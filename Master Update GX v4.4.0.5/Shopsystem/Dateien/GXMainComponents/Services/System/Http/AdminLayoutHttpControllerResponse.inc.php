<?php
/* --------------------------------------------------------------
   AdminLayoutHttpControllerResponse.inc.php 2020-06-09
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Gambio\Admin\Application\GambioAdminBootstrapper;
use Gambio\Admin\Layout\Menu\Commands\ConnectPage;
use Gambio\Admin\Layout\Renderer\GambioAdminLoader;
use Gambio\Core\Command\Interfaces\CommandDispatcher;
use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\DependencyInjection\LeagueContainer;
use Gambio\Core\TemplateEngine\Collection\LayoutDataCollection;

MainFactory::load_class('HttpControllerResponse');

/**
 * Class AdminLayoutHttpControllerResponse
 *
 * This class will be used for rendering the new Admin pages which must be explicitly written in
 * templates. These templates can extend any of the existing admin layouts by themselves.
 *
 * Child controllers can you the "init" method to initialize their dependencies
 *
 * @category System
 * @package  Http
 * @extends  HttpControllerResponse
 */
class AdminLayoutHttpControllerResponse extends HttpControllerResponse
{
    /**
     * Page Title
     *
     * @var string
     */
    protected $title;
    
    /**
     * Template Path
     *
     * @var string
     */
    protected $template;
    
    /**
     * Template data.
     *
     * @var KeyValueCollection
     */
    protected $data;
    
    /**
     * Page Assets
     *
     * Provide paths or filenames to JavaScript, CSS or PHP Translation files.
     *
     * @var AssetCollectionInterface
     */
    protected $assets;
    
    /**
     * Content Sub Navigation
     *
     * The sub navigation will be displayed under the header and can redirect to similar pages.
     *
     * @var ContentNavigationCollectionInterface
     */
    protected $contentNavigation;
    
    /**
     * ContentView instance.
     *
     * Used for parsing the Smarty templates.
     *
     * @var ContentView
     */
    protected $contentView;
    
    
    /**
     * AdminLayoutHttpViewController constructor.
     *
     * @param NonEmptyStringType                        $title             Page title.
     * @param ExistingFile                              $template          Template absolute path.
     * @param KeyValueCollection|null                   $data              A key-value collection containing the data
     *                                                                     to be used by the template.
     * @param AssetCollectionInterface|null             $assets            Page assets (js, css, translations etc).
     * @param ContentNavigationCollectionInterface|null $contentNavigation Sub content navigation (key-value
     *                                                                     collection).
     * @param ContentView|null                          $contentView       Provide a custom content view class if
     *                                                                     needed.
     */
    public function __construct(
        NonEmptyStringType $title,
        ExistingFile $template,
        KeyValueCollection $data = null,
        AssetCollectionInterface $assets = null,
        ContentNavigationCollectionInterface $contentNavigation = null,
        ContentView $contentView = null
    ) {
        $this->title             = $title->asString();
        $this->template          = $template->getFilePath();
        $this->data              = $data;
        $this->assets            = $assets;
        $this->contentNavigation = $contentNavigation;
        $this->contentView       = (!empty($contentView)) ? $contentView : MainFactory::create('ContentView');
        $this->_render();
    }
    
    
    /**
     * @param string     $title
     * @param string     $template
     * @param mixed[]    $templateData
     * @param string[]   $assets
     * @param array|null $subNavItems
     *
     * @return AdminLayoutHttpControllerResponse
     */
    public static function createAsLegacyAdminPageResponse(
        string $title,
        string $template,
        array $templateData = [],
        array $assets = [],
        array $subNavItems = null
    ): AdminLayoutHttpControllerResponse {
        $template = DIR_FS_CATALOG . 'admin/html/content/' . $template;
        
        $assetSuffix = file_exists(__DIR__ . '/../../../../.dev-environment') ? '' : '.min';
        $assets[]    = DIR_WS_ADMIN . "html/assets/styles/compatibility{$assetSuffix}.css";
        $assets[]    = DIR_WS_ADMIN . "html/assets/javascript/compatibility-vendor{$assetSuffix}.js";
        $assets[]    = DIR_WS_ADMIN . 'includes/ckeditor/ckeditor.js';
        $assets[]    = 'buttons.lang.inc.php';
        $assets[]    = 'messages.lang.inc.php';
        $assets[]    = 'labels.lang.inc.php';
        $assets[]    = 'admin_buttons.lang.inc.php';
        $assets[]    = 'admin_labels.lang.inc.php';
        $assets[]    = 'admin_general.lang.inc.php';
        $assets[]    = 'general.lang.inc.php';
        $assets[]    = 'admin_info_boxes.lang.inc.php';
        $assets[]    = 'product_image_lists.lang.inc.php';
        $assets      = array_map(
            static function (string $asset): AssetInterface {
                return MainFactory::create('Asset', $asset);
            },
            $assets
        );
        
        if ($subNavItems !== null) {
            $subNavigation = MainFactory::create('ContentNavigationCollection', []);
            foreach ($subNavItems as $naviItem) {
                $subNavigation->add(
                    new StringType($naviItem['text']),
                    new StringType($naviItem['link']),
                    new BoolType($naviItem['active'])
                );
            }
        }
        
        return MainFactory::create(
            'AdminLayoutHttpControllerResponse',
            new NonEmptyStringType($title),
            new ExistingFile(new NonEmptyStringType($template)),
            new KeyValueCollection($templateData),
            new AssetCollection($assets),
            $subNavigation ?? null
        );
    }
    
    
    /**
     * Render the provided template.
     *
     * Hint: Override this method to change the rendering algorithm.
     */
    protected function _render(): void
    {
        $this->configureContentView();
        $this->setInitialMessages();
        
        /** @var GXSmarty $smarty */
        $this->contentView->init_smarty();
        $smarty = $this->contentView->v_coo_smarty;
        $this->assignData($smarty);
        
        // Todo: Remove workaround
        $smarty->assign('engine', 'JSE');
        $smarty->assign('_SESSION', $_SESSION);
        
        $application = $this->createApplication();
        $this->connectPage($application);
        $this->loadGambioAdmin($application, $smarty);
        
        $this->fixTranslations($smarty);
        
        echo $this->contentView->get_html();
    }
    
    
    /**
     * Fix translations, so all translations from internal assets variable will be assigned too.
     *
     * @param Smarty $smarty
     */
    private function fixTranslations(Smarty $smarty): void
    {
        if ($this->assets) {
            $translations = json_decode($smarty->getTemplateVars('jseTranslations'), true);
            $translations = $translations ? : [];
            
            $translations = array_merge($translations, $this->assets->getTranslations());
            $smarty->assign('translations', json_encode($translations));
            $smarty->assign('jseTranslations', json_encode($translations));
        }
    }
    
    
    /**
     * Content view configuration.
     */
    private function configureContentView(): void
    {
        $this->contentView->set_flat_assigns(true);
        $this->contentView->set_escape_html(true);
        $this->contentView->set_content_template(basename($this->template));
        try {
            $this->contentView->set_template_dir(dirname($this->template));
        } catch (Exception $e) {
        }
    }


    /**
     * Assigns content data from internal variables.
     *
     * @param GXSmarty $smarty
     */
    private function assignData(GXSmarty $smarty): void
    {
        $smarty->safeAssign('page_title', $this->title);
        $smarty->safeAssign('pageTitle', $this->title);
        
        // Set message stack data.
        $smarty->safeAssign('message_stack', $GLOBALS['messageStack']->get_messages());
        $smarty->safeAssign('messageStack', $GLOBALS['messageStack']->get_messages());
        
        // Set Page Assets
        if ($this->assets !== null) {
            $scripts = $this->assets->getHtml(new StringType(Asset::JAVASCRIPT));
            $smarty->safeAssign('dynamic_script_assets', $scripts);
            $styles = $this->assets->getHtml(new StringType(Asset::CSS));
            $smarty->safeAssign('dynamic_style_assets', $styles);
        }
        
        if ($this->data !== null) {
            $content = []; // Content array
            foreach ($this->data->getArray() as $key => $value) {
                $content[$key] = $value;
            }
            $smarty->safeAssign('content', $content);
        }
        if ($this->contentNavigation !== null) {
            $smarty->safeAssign('content_navigation', $this->contentNavigation->getArray());
            $smarty->safeAssign('contentNavigation', $this->contentNavigation->getArray());
        }
    }
    
    
    /**
     * Connects a page to the menu.
     *
     * @param Application $application
     */
    private function connectPage(Application $application): void
    {
        $currentPage = str_replace(DIR_WS_ADMIN, '', AdminMenuControl::get_connected_page());
        if ($currentPage !== '') {
            $dispatcher = $application->get(CommandDispatcher::class);
            $dispatcher->dispatch(new ConnectPage($currentPage));
        }
    }
    
    
    /**
     * Loads gambio admin layout data.
     *
     * @param Application $application
     * @param GXSmarty      $smarty
     */
    private function loadGambioAdmin(Application $application, GXSmarty $smarty): void
    {
        /** @var GambioAdminLoader $loader */
        $loader = $application->get(GambioAdminLoader::class);
        $data   = new LayoutDataCollection();
        $loader->load($data);
        
        foreach ($data->toArray() as $key => $value) {
            $smarty->safeAssign($key, $value);
        }
    }
    
    
    /**
     * Set initial messages for new admin layout.
     */
    protected function setInitialMessages(): void
    {
        $languageTextManager = MainFactory::create('LanguageTextManager', 'admin_general', $_SESSION['languages_id']);
        $contentArray        = $this->contentView->get_content_array();
        
        if ($contentArray['environment'] === 'development') {
            $GLOBALS['messageStack']->add(
                $languageTextManager->get_text(
                    'TEXT_DEV_ENVIRONMENT_WARNING',
                    'admin_general'
                ),
                'warning'
            );
        } elseif (file_exists(DIR_FS_CATALOG . 'gambio_installer/request_port.php')) {
            // Installer still exists error message.
            $installerMessage = sprintf(
                $languageTextManager->get_text(
                    'WARNING_INSTALL_DIRECTORY_EXISTS',
                    'general'
                ),
                substr(DIR_WS_CATALOG, 0, -1)
            );
            
            $GLOBALS['messageStack']->add($installerMessage, 'error');
        }
    }
    
    
    /**
     * Creates and boots an application instance.
     *
     * @return Application
     */
    private function createApplication(): Application
    {
        $application = new \Gambio\Core\Application\Application(LeagueContainer::create());
        
        $adminBootstrapper = new GambioAdminBootstrapper();
        $adminBootstrapper->boot($application);
        
        return $application;
    }
}
