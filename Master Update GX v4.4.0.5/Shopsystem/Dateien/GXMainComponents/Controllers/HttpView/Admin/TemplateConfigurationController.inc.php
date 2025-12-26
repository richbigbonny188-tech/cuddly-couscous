<?php
/* --------------------------------------------------------------
   TemplateConfigurationController.inc.php 2020-09-08
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Gambio\Core\AdminAccess\Group\GroupItem;
use Gambio\Core\AdminAccess\PermissionService;
use Gambio\Core\AdminAccess\Role\PermissionAction;
use Gambio\Core\Configuration\ConfigurationService;
use Gambio\Core\Configuration\Models\Write\Configuration;
use Gambio\StyleEdit\DependencyInjector;

MainFactory::load_class('HttpViewController');

/**
 * Class TemplateConfigurationController
 * @extends    HttpViewController
 * @category   System
 * @package    AdminHttpViewControllers
 */
class TemplateConfigurationController extends AdminHttpViewController
{
    /**
     * @var CI_DB_query_builder $db
     */
    protected $db;
    
    /**
     * @var LanguageTextManager $languageTextManager
     */
    protected $languageTextManager;
    
    /**
     * @var string
     */
    protected $shopEnvironment;
    
    /**
     * @var string
     */
    protected $styleEditLink;
    /**
     * @var string
     */
    protected $styleEdit3Link;
    /**
     * @var string
     */
    protected $styleEdit4Link;
    
    /**
     * @var ConfigurationService
     */
    private $configurationService;
    
    
    /**
     * @param HttpContextReaderInterface     $httpContextReader
     * @param HttpResponseProcessorInterface $httpResponseProcessor
     * @param ContentViewInterface           $contentView
     */
    public function __construct(
        HttpContextReaderInterface $httpContextReader,
        HttpResponseProcessorInterface $httpResponseProcessor,
        ContentViewInterface $contentView
    ) {
        parent::__construct($httpContextReader, $httpResponseProcessor, $contentView);
        $gxCoreLoader = MainFactory::create('GXCoreLoader', MainFactory::create('GXCoreLoaderSettings'));
        $this->db     = $gxCoreLoader->getDatabaseQueryBuilder();
        
        $this->languageTextManager = MainFactory::create('LanguageTextManager', 'template_configuration');
        
        $this->configurationService = LegacyDependencyContainer::getInstance()->get(ConfigurationService::class);
    }
    
    
    /**
     * Returns the Template Configuration Page
     *
     * @return HttpControllerResponse|RedirectHttpControllerResponse
     */
    public function actionDefault()
    {
        $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/');
        $this->_checkEnvironment();
        
        $template = 'template_configuration.html';
        $data     = [
            'TEMPLATE_SELECTION'                        => $this->templateSelection(),
            'GM_QUICK_SEARCH'                           => gm_get_conf('GM_QUICK_SEARCH') === 'true',
            'SHOP_ENVIRONMENT'                          => $this->shopEnvironment,
            'STYLE_EDIT_3_LINK'                         => $this->styleEdit3Link,
            'STYLE_EDIT_4_LINK'                         => $this->styleEdit4Link,
            'USER_HAS_ACCESS_TO_STYLE_EDIT'             => $this->userHasAccessToStyleEditControllers(),
            'STYLE_EDIT_SOS_LINK'                       => xtc_href_link('../index.php', 'style_edit_mode=sos'),
            'DISPLAY_OF_PROPERTY_COMBINATION_SELECTION' => gm_get_conf('DISPLAY_OF_PROPERTY_COMBINATION_SELECTION'),
            'STYLE_EDIT_LINK_LAYOUT'                    => $this->determineStyleEditLinkLayout(),
        ];
        
        $pageTitle = $this->languageTextManager->get_text('HEADING_TITLE');
        
        return AdminLayoutHttpControllerResponse::createAsLegacyAdminPageResponse($pageTitle, $template, $data);
    }
    
    
    /**
     * This method was copied from admin/includes/functions/general.php function xtc_cfg_pull_down_template_sets.
     * Instead of returning html, this method returns an array containing all information needed.
     */
    protected function templateSelection()
    {
        $templatesArray = $deactivatedTemplatesArray = [];
        $templatesPath  = DIR_FS_CATALOG . 'templates';
        
        $directoryIterator = new IteratorIterator(new DirectoryIterator($templatesPath));
        
        /** @var \DirectoryIterator $directory */
        foreach ($directoryIterator as $directory) {
            // fetches all templates from the shop and prepare $templatesArray with the fetched data
            if ($directory->isDir() && !$directory->isDot()) {
                $templateName = $directory->getFilename();
                
                if ($templateName === CURRENT_TEMPLATE) {
                    $templatesArray[] = ['id' => 'template-' . $templateName, 'text' => $templateName . ' (Template)'];
                    continue;
                }
                $templateSettings = $directory->getPathname() . '/template_settings.php';
                
                if (file_exists($templateSettings)) {
                    include $templateSettings;
                    
                    if (isset($t_template_settings_array)
                        && is_array($t_template_settings_array)
                        && array_key_exists('TEMPLATE_PRESENTATION_VERSION', $t_template_settings_array)
                        && $t_template_settings_array['TEMPLATE_PRESENTATION_VERSION'] >= 2.0) {
                        $templatesArray[] = [
                            'id'   => 'template-' . $templateName,
                            'text' => $templateName . ' (Template)'
                        ];
                    }
                }
            }
        }
        
        $themesPath = DIR_FS_CATALOG . 'themes';
        if (is_dir($themesPath)) {
            /** @var \ThemeService $themeService */
            $themeService = StaticGXCoreLoader::getService('Theme');
            
            $availableThemes = $themeService->getAvailableThemes(ThemeDirectoryRoot::create(new ExistingDirectory($themesPath)));
            
            DependencyInjector::inject();
            
            /** @var \ThemeName $availableTheme */
            foreach ($availableThemes as $availableTheme) {
                $themeJson = get_theme_json_from_theme_path($themesPath . DIRECTORY_SEPARATOR
                                                            . $availableTheme->getName());
                
                if ($themeJson !== null && !isset($themeJson->preview)) {
                    
                    $templatesArray[] = [
                        'id'   => 'theme-' . $availableTheme->getName(),
                        'text' => $availableTheme->getName() . ' (Theme)'
                    ];
                }
            }
        }
        sort($templatesArray);
        
        $themeControl = StaticGXCoreLoader::getThemeControl();
        if ($themeControl->isThemeSystemActive()) {
            $default = 'theme-' . CURRENT_THEME;
        } else {
            $default = 'template-' . CURRENT_TEMPLATE;
        }
        
        return [
            'templates' => $templatesArray,
            'default'   => $default
        ];
    }
    
    
    /**
     * @return bool
     */
    protected function userHasAccessToStyleEditControllers(): bool
    {
        /** @var PermissionService $adminAccessService */
        $adminAccessService = LegacyDependencyContainer::getInstance()->get(PermissionService::class);
        
        try {
            // StyleEdit 3 & 4 are in the same access group
            $controllerName = new NonEmptyStringType('StyleEdit4Authentication');
            $customerId     = (int)$_SESSION['customer_id'];
            
            return $adminAccessService->checkAdminPermission($customerId,
                                                             PermissionAction::WRITE,
                                                             GroupItem::CONTROLLER_TYPE,
                                                             $controllerName->asString())
                   && $adminAccessService->checkAdminPermission($customerId,
                                                                PermissionAction::READ,
                                                                GroupItem::CONTROLLER_TYPE,
                                                                $controllerName->asString());
        } catch (Exception $exception) {
            return true;
        }
    }
    
    
    /**
     * @return string
     */
    protected function determineStyleEditLinkLayout(): string
    {
        if (StaticGXCoreLoader::getThemeControl()->isThemeSystemActive()) {
            return 'ACTIVE_THEME';
        }
        
        return file_exists(SHOP_ROOT
                           . 'StyleEdit3/bootstrap.inc.php') ? 'ACTIVE_TEMPLATE_WITH_STYLEEDIT3' : 'ACTIVE_TEMPLATE_WITHOUT_STYLEEDIT3';
    }
    
    
    /**
     * Save shop key
     *
     * @return RedirectHttpControllerResponse
     */
    public function actionStore()
    {
        $this->_store(
            'DISPLAY_OF_PROPERTY_COMBINATION_SELECTION',
            $this->_getPostData('DISPLAY_OF_PROPERTY_COMBINATION_SELECTION')
        );
        
        $this->_updateTheme();
        
        $url = xtc_href_link('admin.php', 'do=TemplateConfiguration');
        
        return MainFactory::create('RedirectHttpControllerResponse', $url);
    }
    
    
    protected function _updateTheme()
    {
        $currentTemplate       = $_POST['CURRENT_THEME'];
        $isTheme               = strpos($currentTemplate, 'theme-') === 0;
        $prefix                = $isTheme ? 'theme-' : 'template-';
        $sanitizedTemplateName = str_replace($prefix, '', $currentTemplate);
        
        if ($isTheme) {
            /** @var \ThemeService $themeService */
            $themeService = StaticGXCoreLoader::getService('Theme');
            
            $themeId = ThemeId::create($sanitizedTemplateName);
            
            $source      = ThemeDirectoryRoot::create(new ExistingDirectory(DIR_FS_CATALOG . 'themes'));
            $destination = ThemeDirectoryRoot::create(new ExistingDirectory(DIR_FS_CATALOG
                                                                            . StaticGXCoreLoader::getThemeControl()
                                                                                ->getPublishedThemePath()));
            $settings    = ThemeSettings::create($source, $destination);
            
            $themeService->buildTemporaryTheme($themeId, $settings);
            
            try {
                $themeService->activateTheme($sanitizedTemplateName);
            } catch (Exception $e) {
                $coo_logger = LogControl::get_instance();
                $coo_logger->error($e->getMessage() . '- ' . $e->getFile() . ' - ' . $e->getLine());
                echo $e->getMessage();
            }
        } else {
            $templateQuery = 'UPDATE `gx_configurations` SET `value` = "' . $sanitizedTemplateName
                             . '" WHERE `key` = "configuration/CURRENT_TEMPLATE"';
            $themeQuery    = 'UPDATE `gx_configurations` SET `value` = "" WHERE `key` = "configuration/CURRENT_THEME"';
            xtc_db_query($templateQuery);
            xtc_db_query($themeQuery);
        }
        
        $coo_cache_control = MainFactory::create_object('CacheControl');
        $coo_cache_control->clear_templates_c();
        $coo_cache_control->clear_template_cache();
        $coo_cache_control->clear_css_cache();
        $coo_cache_control->clear_shop_offline_page_cache();
    }
    
    
    /**
     * Update the template configuration values in the database
     *
     * @param string $p_key
     * @param string $p_value
     */
    protected function _store($p_key, $p_value)
    {
        if ($p_value !== null) {
            $key = "gm_configuration/$p_key";
            $this->configurationService->save(Configuration::create($key, $p_value));
        }
    }
    
    
    protected function _checkEnvironment()
    {
        include_once DIR_FS_CATALOG . 'GXModules/Gambio/StyleEdit/Api/Storage/StyleEditWelcomeStorage.php';
        $welcome_storage           = new \Gambio\StyleEdit\Api\Storage\StyleEditWelcomeStorage;
        $userHasSeenWelcomeMessage = $welcome_storage->welcomeStatusSeenForCustomer(new IdType($_SESSION['customer_id']));
        
        if (StyleEditServiceFactory::service() instanceof StyleEdit4Service) {
            
            $this->shopEnvironment = 'StyleEdit4';
        } elseif (is_dir(DIR_FS_CATALOG . 'StyleEdit3/templates/' . StaticGXCoreLoader::getThemeControl()
                             ->getCurrentTheme())
                  || is_dir(DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getPublishedThemePath()
                            . '/styles/styleedit')) {
            $this->shopEnvironment = 'StyleEdit3';
            $this->styleEditLink   = xtc_href_link('admin.php', 'do=StyleEdit3Authentication');
        } elseif (!isset($_GET['force_config']) || $_GET['force_config'] !== 'true') {
            $this->shopEnvironment = 'noStyleEdit';
        } else {
            $this->shopEnvironment = 'forceStyleEdit';
            $this->styleEditLink   = '#';
        }
        
        $this->styleEdit3Link = xtc_href_link('admin.php', 'do=StyleEdit3Authentication');
        $this->styleEdit4Link = $userHasSeenWelcomeMessage ? xtc_href_link('admin.php',
                                                                           'do=StyleEdit4Authentication') : xtc_href_link('admin.php',
                                                                                                                          'do=StyleEdit4Authentication&welcome=1');
    }
}
