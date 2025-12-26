<?php
/* --------------------------------------------------------------
  AbstractModuleCenterModuleController.inc.php 2020-06-09
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
*/

/**
 * Class AbstractModuleCenterModule
 * @extends    AdminHttpViewController
 * @category   System
 * @package    Modules
 * @subpackage Controllers
 */
abstract class AbstractModuleCenterModuleController extends AdminHttpViewController
{
    /**
     * @var string $pageTitle
     */
    protected $pageTitle = '';
    
    /**
     * @var array $buttons
     */
    protected $buttons = [];
    
    /**
     * @var string $redirectUrl
     */
    protected $redirectUrl = '';
    
    /**
     * @var LanguageTextManager $languageTextManager
     */
    protected $languageTextManager;
    
    
    /**
     * @param HttpContextReaderInterface $httpContextReader
     * @param HttpResponseProcessorInterface $httpResponseProcessor
     * @param ContentViewInterface $contentView
     */
    public function __construct(
        HttpContextReaderInterface $httpContextReader,
        HttpResponseProcessorInterface $httpResponseProcessor,
        ContentViewInterface $contentView
    ) {
        parent::__construct($httpContextReader, $httpResponseProcessor, $contentView);
        
        $this->languageTextManager = MainFactory::create('LanguageTextManager', 'module_center_module');
        
        $this->_init();
    }
    
    
    /**
     * Initialize the module e.g. set title, description, sort order etc.
     *
     * Function will be called in the constructor
     */
    abstract protected function _init();
    
    
    /**
     * Returns an AdminLayoutHttpControllerResponse with buttons if buttons are specified or returns a
     * RedirectHttpControllerResponse with specified redirect url.
     *
     * @return AdminLayoutHttpControllerResponse|RedirectHttpControllerResponse
     */
    public function actionDefault()
    {
        if (count($this->buttons)) {
            $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/module_center/');
            $template = 'module_center/module_center_module.html';
            $data     = [
                'buttons' => $this->buttons
            ];
            
            AdminMenuControl::connect_with_page('admin.php?do=ModuleCenter');
            
            return AdminLayoutHttpControllerResponse::createAsLegacyAdminPageResponse($this->pageTitle,
                                                                                      $template,
                                                                                      $data);
        }
        
        return MainFactory::create('RedirectHttpControllerResponse', $this->redirectUrl);
    }
}