<?php
/* --------------------------------------------------------------
  GXModuleCenterModuleController.inc.php 2020-07-08
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
*/

/**
 * Class GXModuleCenterModuleController
 *
 * @extends    AbstractModuleCenterModuleController
 * @category   System
 * @package    Modules
 * @subpackage Controllers
 */
class GXModuleCenterModuleController extends AbstractModuleCenterModuleController
{
    /**
     * Holds json data for all modules
     *
     * @var array $modules
     */
    protected $modules = [];
    
    /**
     * Configuration storage.
     *
     * @var GXModuleConfigurationStorage
     */
    protected $configurationStorage;
    
    /**
     * @var object UserConfiguration $userConfigurationService
     */
    protected $userConfigurationService;
    
    /**
     * @var array
     */
    protected $sectionsGrid = [];
    
    /**
     * @var
     */
    private $languageProvider;
    
    
    /**
     * Initialize the module e.g. set title, description, sort order etc.
     *
     * Function will be called in the constructor
     */
    protected function _init()
    {
        $this->userConfigurationService = StaticGXCoreLoader::getService('UserConfiguration');
    }
    
    
    /**
     * Returns an AdminLayoutHttpControllerResponse with configuration options or returns a
     * RedirectHttpControllerResponse with specified redirect url.
     *
     * @return HttpControllerResponse
     */
    public function actionDefault()
    {
        $config                 = [];
        $sections               = [];
        $userId                 = new IdType(0);
        $module                 = str_replace('ModuleCenterModule', '', $this->_getQueryParameter('do'));
        $configuration          = $this->_getGXModuleJSONConfiguration($module);
        $availableLanguageCodes = $this->getLanguageProvider()->getCodes();
        
        $this->initConfiguration($module);
        
        $template = $this->getTemplateFile('module_center/gx_modules/gx_module_configuration.html');
        $title    = isset($configuration['title']) ? $this->getText($configuration['title']) : $module;
        $title    = new NonEmptyStringType($title);
        
        $contentNavigation = MainFactory::create('ContentNavigationCollection', []);
        
        if (is_array($configuration['configuration'])) {
            foreach ($configuration['configuration'] as $section => $fields) {
                if (isset($fields['tab']) && $this->_getQueryParameter('tab') !== null) {
                    $contentNavigation->add(new StringType($this->getText('module_center.tab_main')),
                                            new StringType(xtc_href_link('admin.php',
                                                                         'do=' . $this->_getQueryParameter('do'))),
                                            new BoolType($this->_getQueryParameter('tab') === null));
                    $tab = $this->_getQueryParameter('tab') == $section;
                } elseif (isset($fields['tab']) && $this->_getQueryParameter('tab') === null) {
                    $contentNavigation->add(new StringType($this->getText('module_center.tab_main')),
                                            new StringType(xtc_href_link('admin.php',
                                                                         'do=' . $this->_getQueryParameter('do'))),
                                            new BoolType($this->_getQueryParameter('tab') === null));
                    $tab = false;
                } elseif (!isset($fields['tab']) && $this->_getQueryParameter('tab') !== null) {
                    $tab = $this->_getQueryParameter('tab') == $section;
                } else {
                    $tab = true;
                }
                foreach ($fields as $key => $value) {
                    if ($key === 'fields' && $tab) {
                        foreach ($value as $field => $fieldvalues) {
                            if (isset($fieldvalues['action']) && isset($fieldvalues['action']['message'])) {
                                $fieldvalues['action']['message'] = $this->getText($fieldvalues['action']['message']);
                            }
                            
                            if (isset($fieldvalues['buttons'])) {
                                foreach ($fieldvalues['buttons'] as &$button) {
                                    $button['text'] = $this->getText($button['text']);
                                    if (isset($button['action']['message'])) {
                                        $button['action']['message'] = $this->getText($button['action']['message']);
                                    }
                                }
                            }
                            
                            $config[$section][$field] = $fieldvalues;
                            
                            $config[$section][$field]['grids_width']       = $this->_getSectionsGridWidth();
                            $config[$section][$field]['label']             = isset($fieldvalues['label']) ? $this->getText($fieldvalues['label']) : '';
                            $config[$section][$field]['title']             = isset($fieldvalues['title']) ? $this->getText($fieldvalues['title']) : '';
                            $config[$section][$field]['description']       = isset($fieldvalues['description']) ? $this->getText($fieldvalues['description']) : '';
                            $config[$section][$field]['text']              = isset($fieldvalues['text']) ? $this->getText($fieldvalues['text']) : '';
                            $config[$section][$field]['tooltip']['text']   = isset($fieldvalues['tooltip']['text']) ? $this->getText($fieldvalues['tooltip']['text']) : '';
                            $config[$section][$field]['languageDependent'] = isset($fieldvalues['languageDependent'])
                                                                             && $fieldvalues['languageDependent']
                                                                                === true;
                            
                            switch ($fieldvalues['type']) {
                                case 'file':
                                    $config[$section][$field]['default_value'] = $fieldvalues['folder'];
                                    break;
                                case 'select':
                                case 'multiselect':
                                    $config[$section][$field]['selected'] = [];
                                    if (isset($fieldvalues['selected'])) {
                                        $config[$section][$field]['selected'] = array_flip($fieldvalues['selected']);
                                    }
                                    
                                    foreach ($fieldvalues['values'] as $index => $option) {
                                        $value      = $option['value'];
                                        $textphrase = $option['text'];
                                        
                                        $config[$section][$field]['values'][$value] = $this->getText($textphrase);
                                        unset($config[$section][$field]['values'][$index]);
                                    }
                                    break;
                                case 'editor':
                                    $config[$section][$field]['editor_type'] = $this->userConfigurationService->getUserConfiguration($userId,
                                                                                                                                     'editor-'
                                                                                                                                     . $module
                                                                                                                                     . '-'
                                                                                                                                     . $field) ? : 'ckeditor';
                                    break;
                                case 'countries':
                                    $config[$section][$field]['values'] = xtc_get_countries();
                                    break;
                                case 'customer_group':
                                    $config[$section][$field]['values'] = xtc_get_customers_statuses();
                                    break;
                                case 'order_status':
                                    $config[$section][$field]['values'] = xtc_get_orders_status();
                                    break;
                                case 'languages':
                                    $config[$section][$field]['values'] = xtc_get_languages();
                                    break;
                            }
                            
                            if ($this->configurationStorage->get($field) !== false) {
                                $config[$section][$field]['default_value'] = $this->configurationStorage->get($field);
                                foreach ($availableLanguageCodes as $code) {
                                    $languageCode = strtolower($code->asString());
                                    // check for default configuration values for specific language, if not, set it to empty.
                                    if (is_array($config[$section][$field]['default_value'])
                                        && !array_key_exists($languageCode,
                                                             $config[$section][$field]['default_value'])) {
                                        $config[$section][$field]['default_value'][$languageCode] = '';
                                    } elseif (is_object($config[$section][$field]['default_value'])
                                              && !property_exists($config[$section][$field]['default_value'],
                                                                  $languageCode)) {
                                        $config[$section][$field]['default_value']->{$languageCode} = '';
                                    }
                                }
                                if ($fieldvalues['type'] === 'multiselect') {
                                    $config[$section][$field]['selected'] = array_flip((array)$this->configurationStorage->get($field));
                                }
                            }
                        }
                    } elseif ($key === 'tab') {
                        $contentNavigation->add(new StringType($this->getText($value)),
                                                new StringType(xtc_href_link('admin.php',
                                                                             'do=' . $this->_getQueryParameter('do')
                                                                             . '&tab=' . $section)),
                                                new BoolType($tab));
                    } else {
                        $sections[$section] = $this->getText($value);
                    }
                }
            }
        }
        
        if ($this->_getQueryParameter('tab') !== null) {
            $tab = '&tab=' . $this->_getQueryParameter('tab');
        } else {
            $tab = '';
        }
        
        $config = [
            'active'                     => $this->configurationStorage->get('active'),
            'form_action'                => xtc_href_link('admin.php',
                                                          'do=' . $this->_getQueryParameter('do') . '/SaveConfiguration'
                                                          . $tab),
            'USE_WYSIWYG'                => USE_WYSIWYG,
            'sections'                   => $sections,
            'configuration'              => $config,
            'module_name'                => $module,
            'tab'                        => $this->_getQueryParameter('tab') !== null,
            'use_responsive_filemanager' => is_dir(DIR_FS_CATALOG . DIRECTORY_SEPARATOR . 'ResponsiveFilemanager')
        ];
        
        $data = MainFactory::create('KeyValueCollection', $config);
        
        if (isset($configuration['config_url'])) {
            return MainFactory::create('RedirectHttpControllerResponse', xtc_href_link($configuration['config_url']));
        }
        
        // suppress direct output
        ob_start();
        MainFactory::create('AdminLayoutHttpControllerResponse',
                            $title,
                            $template,
                            $data,
                            $this->_getAssets(),
                            $contentNavigation);
        $html = ob_get_clean();
        
        return MainFactory::create('HttpControllerResponse', $html);
    }
    
    
    /**
     * @return AssetCollection|bool
     */
    protected function _getAssets()
    {
        $assets = MainFactory::create('AssetCollection');
        $assets->add(MainFactory::create('Asset', 'admin_buttons.lang.inc.php'));
        $assets->add(MainFactory::create('Asset', 'styles/legacy/global-colorpicker.css'));
        $assets->add(MainFactory::create('Asset', 'includes/ckeditor/ckeditor.js'));
        
        return $assets;
    }
    
    
    /**
     * Saves the configuration.
     *
     * @return RedirectHttpControllerResponse Default page.
     */
    public function actionSaveConfiguration()
    {
        $data      = $this->_getPostDataCollection();
        $module    = substr_replace($this->_getQueryParameter('do'), '', strpos($this->_getQueryParameter('do'), '/'));
        $namespace = str_replace('ModuleCenterModule', '', $module);
        
        $this->initConfiguration($namespace);
        
        foreach ($data as $key => $value) {
            
            if ($key == 'active') {
                $moduleStatusChanged = $this->configurationStorage->get('active') !== (int)$value;
            }
            
            if (is_array($value) && $key !== 'editor_identifiers') {
                $this->configurationStorage->set($key, json_encode(xtc_db_prepare_input($value)));
            } elseif ($key !== 'editor_identifiers') {
                $this->configurationStorage->set($key, xtc_db_prepare_input($value));
            }
        }
        
        $this->process_uploads($data);
        
        if ($moduleStatusChanged) {
            $cacheControl = MainFactory::create_object('CacheControl');
            $cacheControl->reset_cache('modules');
            @unlink(DIR_FS_CATALOG . 'cache/__dynamics.css');
        }
        
        if ($this->_getQueryParameter('tab') !== null) {
            $tab = '&tab=' . $this->_getQueryParameter('tab');
        } else {
            $tab = '';
        }
        
        $saveHookData = $this->_getSaveHookData($namespace);
        if ($saveHookData) {
            $this->_callUserMethod($saveHookData);
        }
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   xtc_href_link('admin.php', 'do=' . $module . $tab));
    }
    
    
    /**
     * Get the json configuration from GXModule.json file
     *
     * @param $name
     *
     * @return array|bool
     */
    protected function _getGXModuleJSONConfiguration($name)
    {
        $gxModuleFiles = GXModulesCache::getFiles();
        
        foreach ($gxModuleFiles as $file) {
            if (strpos($file, 'GXModule.json') !== false) {
                preg_match("/GXModules\/(.*)\/GXModule.json/", $file, $matches);
                $moduleData = json_decode(file_get_contents($file), true);
                if (str_replace('/', '', $matches[1]) === $name) {
                    return $moduleData;
                }
            }
        }
        
        return false;
    }
    
    
    /**
     * Loads the configuration from db for specific module
     *
     * @param $module string
     */
    protected function initConfiguration($module)
    {
        $this->configurationStorage = MainFactory::create('GXModuleConfigurationStorage', $module);
    }
    
    
    /**
     * Processing the uploaded files
     *
     * @param $data KayValueCollection
     */
    protected function process_uploads($data)
    {
        foreach ($_FILES as $file => $value) {
            if (!empty($value['tmp_name'])) {
                move_uploaded_file($value['tmp_name'],
                                   DIR_FS_CATALOG . $data->getValue($file . '_folder') . '/' . $value['name']);
                $this->configurationStorage->set($file, $data->getValue($file . '_folder') . '/' . $value['name']);
            }
        }
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
    
    
    /**
     * Returns the after-save hook data from the GXModules config JSON file.
     *
     * @param string $moduleName
     *
     * @return array|null
     */
    protected function _getSaveHookData($moduleName)
    {
        $data = $this->_getGXModuleJSONConfiguration($moduleName);
        if (isset($data['save'])) {
            return $data['save'];
        }
        
        return null;
    }
    
    
    /**
     * Call the method from the controller
     *
     * @param $action
     */
    protected function _callUserMethod($action)
    {
        $method         = $action['method'];
        $controllerName = $action['controller'];
        
        $db           = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $cacheControl = MainFactory::create_object('CacheControl');
    
        $controller = MainFactory::create($controllerName);
        $controller->{$method}($db, $this->configurationStorage, $this->languageTextManager, $cacheControl);
    }
    
    
    /**
     * Calculate total activated languages & return columns width for
     * label & flags grids in the dashboard configurations
     *
     * @param int $breakpoint maximum languages count then it will show label & flags in separate rows
     *
     * @return array [label, flag]
     */
    protected function _getSectionsGridWidth($breakpoint = 8): array
    {
        if (empty($this->sectionsGrid)) {
            $width = [12, 12];
            $count = count($this->getLanguageProvider()->getAdminIds());
            if ($count < $breakpoint) {
                $width = [
                    (12 - $count),
                    $count
                ];
            }
            $this->sectionsGrid = $width;
        }
        
        return $this->sectionsGrid;
    }
    
    
    private function getLanguageProvider()
    {
        if (!$this->languageProvider) {
            $db                     = StaticGXCoreLoader::getDatabaseQueryBuilder();
            $this->languageProvider = MainFactory::create_object('LanguageProvider', [$db]);
        }
        
        return $this->languageProvider;
    }
}