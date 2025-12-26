<?php
/* --------------------------------------------------------------
   ModuleCenterController.inc.php 2020-06-09
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

MainFactory::load_class('AdminHttpViewController');

/**
 * Class ModuleCenterController
 *
 * @extends    AdminHttpViewController
 * @category   System
 * @package    AdminHttpViewControllers
 */
class ModuleCenterController extends AdminHttpViewController
{
    /**
     * @var LanguageTextManager $languageTextManager
     */
    protected $languageTextManager;
    
    
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
        
        $this->languageTextManager = MainFactory::create('LanguageTextManager', 'module_center');
    }
    
    
    /**
     * Returns the Module Center Page
     *
     * @return HttpControllerResponse|RedirectHttpControllerResponse
     */
    public function actionDefault()
    {
        $pageTitle = $this->languageTextManager->get_text('page_title');
        
        $template = 'module_center/module_center.html';
        $data     = [
            'modules' => $this->_getModulesCollection(),
        ];
        $assets   = [
            'module_center.lang.inc.php',
        ];
        
        return AdminLayoutHttpControllerResponse::createAsLegacyAdminPageResponse($pageTitle,
                                                                                  $template,
                                                                                  $data,
                                                                                  $assets);
    }
    
    
    /**
     * @return JsonHttpControllerResponse
     */
    public function actionGetData()
    {
        $module = $this->_findModule($this->_getQueryParameter('module'));
        
        if ($module !== null) {
            $payload = [
                'title'       => $module->getTitle(),
                'name'        => $module->getName(),
                'description' => $module->getDescription(),
                'isInstalled' => $module->isInstalled(),
                'isEditable'  => method_exists($module, 'isEditable') ? $module->isEditable() : true
            ];
            
            $response = ['success' => true, 'payload' => $payload];
        } else {
            $response = ['success' => false];
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Install module
     *
     * @return RedirectHttpControllerResponse
     */
    public function actionStore()
    {
        $module = $this->_findModule($this->_getPostData('module'));
        $url    = xtc_href_link('admin.php', 'do=ModuleCenter');
        
        if ($module !== null) {
            $customModuleUrl = $module->install();
            if (xtc_not_null($customModuleUrl)) {
                $url = $customModuleUrl;
            } else {
                $url = xtc_href_link('admin.php', 'do=ModuleCenter&module=' . $module->getName());
            }
        }
        
        return MainFactory::create('RedirectHttpControllerResponse', $url);
    }
    
    
    /**
     * Uninstall module
     *
     * @return RedirectHttpControllerResponse
     */
    public function actionDestroy()
    {
        $module = $this->_findModule($this->_getPostData('module'));
        $url    = xtc_href_link('admin.php', 'do=ModuleCenter');
        
        if ($module !== null) {
            $module->uninstall();
            $url = xtc_href_link('admin.php', 'do=ModuleCenter&module=' . $module->getName());
        }
        
        return MainFactory::create('RedirectHttpControllerResponse', $url);
    }
    
    
    /**
     * @param string $p_moduleName
     *
     * @return ModuleCenterModuleInterface|null
     */
    protected function _findModule($p_moduleName)
    {
        $module = null;
        
        if (!empty($p_moduleName)) {
            $moduleName = basename($p_moduleName);
            
            $languageTextManager = MainFactory::create('LanguageTextManager', 'module_center_module');
            $gxCoreLoader        = MainFactory::create('GXCoreLoader', MainFactory::create('GXCoreLoaderSettings'));
            $db                  = $gxCoreLoader->getDatabaseQueryBuilder();
            $cacheControl        = MainFactory::create_object('CacheControl');
            
            if (!class_exists($moduleName . 'ModuleCenterModule')) {
                $moduleClass = 'GX';
            } else {
                $moduleClass = $moduleName;
            }
            
            /**
             * @var ModuleCenterModuleInterface $module
             */
            $module = MainFactory::create($moduleClass . 'ModuleCenterModule',
                                          $languageTextManager,
                                          $db,
                                          $cacheControl);
            
            if ($moduleClass === 'GX') {
                $gxModules   = $this->_getGXModules();
                $title       = empty($gxModules[$p_moduleName]['title']) ? $moduleName : $this->getText($gxModules[$p_moduleName]['title']);
                $description = $this->getText($gxModules[$p_moduleName]['description']);
                $module->setName($p_moduleName);
                $module->setTitle($title);
                $module->setDescription($description);
            }
        }
        
        return $module;
    }
    
    
    /**
     * @return ModuleCenterModuleCollection
     */
    protected function _getModulesCollection()
    {
        $modules      = [];
        $modulesIndex = [];
        $collection   = MainFactory::create('ModuleCenterModuleCollection');
        
        $moduleFiles = glob(DIR_FS_CATALOG . 'GXMainComponents/Modules/*ModuleCenterModule.inc.php');
        
        $gxCoreLoader        = MainFactory::create('GXCoreLoader', MainFactory::create('GXCoreLoaderSettings'));
        $db                  = $gxCoreLoader->getDatabaseQueryBuilder();
        $languageTextManager = MainFactory::create('LanguageTextManager', 'module_center_module');
        $cacheControl        = MainFactory::create_object('CacheControl');
        
        $gxModules = $this->_getGXModules();
        foreach ($gxModules as $gxModuleName => $value) {
            $title  = $this->getText($gxModules[$gxModuleName]['title']);
            $module = MainFactory::create('GXModuleCenterModule', $languageTextManager, $db, $cacheControl);
            $module->setTitle($title ? : $gxModuleName);
            $module->setName($gxModuleName);
            
            if (!empty($gxModules[$gxModuleName]['sortOrder'])) {
                $module->setSortOrder($gxModules[$gxModuleName]['sortOrder']);
            }
            
            $modules[$gxModuleName]      = $module;
            $modulesIndex[$gxModuleName] = $module->getSortOrder();
        }
        
        if (is_array($moduleFiles)) {
            foreach ($moduleFiles as $file) {
                $moduleName = strtok(basename($file), '.');
                
                /** @var ModuleCenterModuleInterface $module */
                $module = MainFactory::create($moduleName, $languageTextManager, $db, $cacheControl);
                if (!$module->isVisible()) {
                    continue;
                }
                $modules[$moduleName]      = $module;
                $modulesIndex[$moduleName] = $module->getSortOrder();
            }
        }
        
        $gxModuleFiles = GXModulesCache::getFiles();
        
        foreach ($gxModuleFiles as $file) {
            if (strpos($file, 'ModuleCenterModule.inc.php') !== false) {
                $moduleName = strtok(basename($file), '.');
                
                $module                    = MainFactory::create($moduleName, $languageTextManager, $db, $cacheControl);
                $modules[$moduleName]      = $module;
                $modulesIndex[$moduleName] = $module->getSortOrder();
            }
        }
        
        asort($modulesIndex, SORT_NUMERIC);
        
        foreach ($modulesIndex as $moduleName => $module) {
            $collection->add($modules[$moduleName]);
        }
        
        return $collection;
    }
    
    
    /**
     * Scans the GXModules directory for GXModule.json files and returns them as an array
     *
     * @return array
     */
    protected function _getGXModules()
    {
        $modules       = [];
        $gxModuleFiles = GXModulesCache::getFiles();
        
        foreach ($gxModuleFiles as $file) {
            if (strpos($file, 'GXModule.json') !== false) {
                $module_data = json_decode(file_get_contents($file), true);
                
                preg_match('/GXModules\/(.*)\/GXModule.json/', $file, $matches);
                $modules[str_replace('/', '', $matches[1])] = $module_data;
            }
        }
        
        return $modules;
    }
    
    
    /**
     * Returns the translated text for the given section phrase selector (i.e. "buttons.ok" results in "Ok")
     *
     * @param $sectionPhraseSelector
     *
     * @return string
     */
    protected function getText($sectionPhraseSelector)
    {
        if (is_string($sectionPhraseSelector) && substr_count($sectionPhraseSelector, '.') === 1) {
            $sectionPhrase = explode('.', $sectionPhraseSelector);
            
            return $this->languageTextManager->get_text($sectionPhrase[1],
                                                        $sectionPhrase[0],
                                                        $_SESSION['languages_id']);
        }
        
        return $sectionPhraseSelector;
    }
}