<?php
/*--------------------------------------------------------------------------------------------------
    ThemeContentView.inc.php 2021-03-31
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

/**
 * Class ThemeContentView
 */
class ThemeContentView extends BaseClass implements ContentViewInterface
{
    var       $v_env_get_array           = [];
    var       $v_env_post_array          = [];
    var       $v_content_template        = '';
    var       $content_array             = [];
    var       $deprecated_array          = [];
    var       $v_min_deprecation_level   = 2; // TODO: rename in max_deprecation_level
    var       $v_flat_assigns            = false;
    /**
     * @var bool|GXSmarty
     */
    var       $v_coo_smarty              = false;
    var       $v_cache_id_elements_array = [];
    var       $v_template_dir            = '';
    var       $v_caching_enabled         = false; # set in init_smarty()
    var       $v_compile_check_enabled   = false; # set in init_smarty()
    var       $v_session_id_placeholder  = '[#%_SESSION_ID_PLACEHOLDER_%#]';
    protected $escape_html               = false;
    protected $build_html                = true;
    
    #	var $v_caching_enabled = false;
    #	var $v_compile_check_enabled = true;
    public function __construct($p_get_array = false, $p_post_array = false)
    {
        if ($p_get_array) {
            $this->v_env_get_array = $p_get_array;
        }
        if ($p_post_array) {
            $this->v_env_post_array = $p_post_array;
        }
        
        $this->deprecated_array[0] = [];
        $this->deprecated_array[1] = [];
        $this->deprecated_array[2] = [];
        
        $this->set_validation_rules();
        $this->set_deprecated_array();
    }
    
    
    function init_smarty()
    {
        if ($this->v_coo_smarty === false) {
            if (isset($GLOBALS['coo_debugger']) && is_object($GLOBALS['coo_debugger']) == true
                && $GLOBALS['coo_debugger']->is_enabled('smarty_compile_check') == true) {
                # overwrite only, if compile_check is enabled in debug_config
                $this->set_compile_check_enabled(true);
                $this->set_caching_enabled(false);
            }
            
            # create smarty
            $this->v_coo_smarty = MainFactory::create('GXSmarty');
            
            # cache settings
            $this->v_coo_smarty->caching        = $this->is_caching_enabled();
            $this->v_coo_smarty->cache_lifetime = -1;
            $this->v_coo_smarty->use_sub_dirs   = false;
            
            # compile settings
            $this->v_coo_smarty->compile_check = $this->is_compile_check_enabled();
            $this->v_coo_smarty->compile_dir   = $this->get_shop_path() . StaticGXCoreLoader::getThemeControl()
                    ->getCompiledTemplatesFolder();
            $this->v_coo_smarty->cache_dir     = $this->get_shop_path() . 'cache/';
            $this->v_coo_smarty->escape_html   = $this->escape_html;
            
            if ($this->v_template_dir === '' && strpos($this->v_content_template, ':') === false) {
                # set only, if it's empty and template source is a file
                $this->v_template_dir = $this->get_shop_path() . StaticGXCoreLoader::getThemeControl()->getThemeHtmlPath();
            }
            
            $dataCache = DataCache::get_instance();
            
            if ($dataCache->key_exists('smarty_plugin_paths_' . StaticGXCoreLoader::getThemeControl()
                                           ->getCurrentTheme(),
                                       true)) {
                $pluginPaths = $dataCache->get_data('smarty_plugin_paths_' . StaticGXCoreLoader::getThemeControl()
                                                        ->getCurrentTheme(),
                                                    true);
            } else {
                $pluginPaths = [];
                
                $gxModuleFiles = GXModulesCache::getInstalledModuleFiles();
                
                foreach ($gxModuleFiles as $file) {
                    
                    $strpos = stripos($file, '/SmartyPlugins/');
                    
                    if ($strpos !== false) {
                        $pluginPaths[] = substr($file, 0, $strpos + strlen('/SmartyPlugins'));
                    }
                }
                
                if (is_dir($this->get_shop_path() . 'GXMainComponents/SmartyPlugins')) {
                    $pluginPaths[] = $this->get_shop_path() . 'GXMainComponents/SmartyPlugins';
                }
                
                if (is_dir($this->get_shop_path() . StaticGXCoreLoader::getThemeControl()->getThemeSmartyPath())) {
                    $pluginPaths[] = $this->get_shop_path() . StaticGXCoreLoader::getThemeControl()->getThemeSmartyPath();
                }
                
                $pluginPaths = array_unique($pluginPaths);
                
                $dataCache->set_data('smarty_plugin_paths_' . StaticGXCoreLoader::getThemeControl()->getCurrentTheme(),
                                     $pluginPaths,
                                     true);
            }
            
            $this->v_coo_smarty->addPluginsDir($pluginPaths);
            
            # default elements for cache_id building
            $t_cache_id_parameter_array = [
                $this->get_content_template(),
                $_SESSION['language'],
                $_SESSION['currency'],
                $_SESSION['customers_status']['customers_status_id']
            ];
            $this->add_cache_id_elements($t_cache_id_parameter_array);
            
            # output settings
            if (HTML_COMPRESSION == 'true') {
                $this->v_coo_smarty->loadFilter('output', 'trimwhitespace');
            }
        }
    }
    
    
    public function prepare_data()
    {
        // called at the beginning of get_html()
        // e.g. can be used in overload to set additional content data for html-template
    }
    
    
    # should be overwritten by sub-classes
    public function get_html()
    {
        $t_html_output = '';
        $this->prepare_data();
        if ($this->build_html == true) {
            $t_html_output = $this->build_html();
        }
        
        return $t_html_output;
    }
    
    
    public function set_template_dir($p_dir_path)
    {
        if (strpos($p_dir_path, $this->get_shop_path() . 'admin/templates/') !== false) {
            throw new Exception('Use of admin/templates is deprecated, use admin/html/content instead');
        }
        $templateDirPath = realpath($p_dir_path) . '/';
        
        if (is_dir($templateDirPath) == false) {
            trigger_error('dir not found: ' . $templateDirPath, E_USER_WARNING);
            
            return false;
        }
        
        $this->v_template_dir = $templateDirPath;
        
        return true;
    }
    
    
    public function set_flat_assigns($p_status)
    {
        $this->v_flat_assigns = $p_status;
    }
    
    
    public function get_flat_assigns()
    {
        return $this->v_flat_assigns;
    }
    
    
    public function set_content_data($p_content_name, $p_content_item, $p_deprecation_level = 0)
    {
        $t_deprecation_level = (int)$p_deprecation_level;
        if ($t_deprecation_level != 0 && array_key_exists($t_deprecation_level, $this->deprecated_array) == false) {
            trigger_error('invalid p_deprecation_level: ' . $p_deprecation_level, E_USER_WARNING);
        }
        
        $this->content_array[$p_content_name] = $p_content_item;
        
        $this->deprecated_array[$t_deprecation_level][] = $p_content_name;
    }
    
    
    public function get_content_array($p_max_deprecation_level = false)
    {
        $t_deprecated_array = $this->get_merged_deprecated_array();
        
        foreach ($t_deprecated_array as $t_value) {
            if (trim($t_value) == '') {
                continue;
            }
            $t_keys_array = explode('|', $t_value);
            
            $this->search_deprecated_keys($t_keys_array, $this->content_array);
        }
        
        return $this->content_array;
    }
    
    
    protected function set_deprecated_array()
    {
    }
    
    
    function search_deprecated_keys($p_key_array, &$p_array)
    {
        if (array_key_exists($p_key_array[0], $p_array)) {
            $t_key = array_shift($p_key_array);
            if (count($p_key_array) > 0) {
                $this->search_deprecated_keys($p_key_array, $p_array[$t_key]);
            } else {
                unset($p_array[$t_key]);
            }
        }
    }
    
    
    function get_merged_deprecated_array($p_max_deprecation_level = false)
    {
        $t_deprecated_array = [];
        if ($p_max_deprecation_level === false) {
            $t_max_level = $this->v_min_deprecation_level;
        } else {
            $t_max_level = $p_max_deprecation_level;
        }
        
        # merge all levels
        foreach ($this->deprecated_array as $t_key => $t_array) {
            if ($t_key == 0 || $t_key > $t_max_level || is_array($t_array) == false) {
                continue;
            }
            $t_deprecated_array = array_merge($t_deprecated_array, $t_array);
        }
        
        return $t_deprecated_array;
    }
    
    
    public function set_content_template($p_filepath)
    {
        $this->v_content_template = $p_filepath;
    }
    
    
    /**
     * Smarty can render templates from a string by using the string: or eval: resource.
     *
     * @param string $p_template
     * @param bool   $storeCompiledTemplate If set on "true", each unique template string will create a new compiled
     *                                      template file. If your template strings are accessed frequently, this is a
     *                                      good choice. If you have frequently changing template strings (or strings
     *                                      with low reuse value), "false" may be a better choice, as it doesn't save
     *                                      compiled templates to disk.
     */
    public function set_content_template_from_string($p_template, $storeCompiledTemplate = true)
    {
        if ($storeCompiledTemplate) {
            $this->v_content_template = 'string:' . $p_template;
        } else {
            $this->v_content_template = 'eval:' . $p_template;
        }
    }
    
    
    public function get_content_template()
    {
        return $this->v_content_template;
    }
    
    
    function add_cache_id_elements($p_elements_array)
    {
        $this->v_cache_id_elements_array = array_merge($this->v_cache_id_elements_array, $p_elements_array);
    }
    
    
    function clear_cache_id_elements()
    {
        $this->v_cache_id_elements_array = [];
    }
    
    
    function get_cache_id()
    {
        $t_cache_id_parameter_array = $this->v_cache_id_elements_array;
        
        # build cache_id
        $t_cache_id = implode('_', $t_cache_id_parameter_array);
        $t_cache_id = 'view_' . md5($t_cache_id);
        
        return $t_cache_id;
    }
    
    
    function is_cached()
    {
        $this->init_smarty();
        
        $t_template = $this->v_template_dir . $this->get_content_template();
        $t_cache_id = $this->get_cache_id();
        
        $t_cache_status = $this->v_coo_smarty->isCached($t_template, $t_cache_id);
        
        if ($t_cache_status == true) {
            $t_cache_status_log = 'TRUE';
        } else {
            $t_cache_status_log = 'FALSE';
        }
        if (isset($GLOBALS['coo_debugger']) && is_object($GLOBALS['coo_debugger'])) {
            $GLOBALS['coo_debugger']->log('cache_id:' . $t_cache_id . ' is_cached=' . $t_cache_status_log,
                                          'SmartyCache');
        }
        
        return $t_cache_status;
    }
    
    
    public function build_html($p_content_data_array = false, $p_template_file = false)
    {
        $t_html_output = '';
        
        $this->before_build_html();
        
        $this->init_smarty();
        
        # set using array and template
        $t_content_data_array = $p_content_data_array;
        $t_template_file      = $p_template_file;
        
        if ($t_content_data_array === false) {
            $t_content_data_array = $this->get_content_array();
        }
        if ($t_template_file === false) {
            $t_template_file = $this->get_content_template();
        }
        if ($t_template_file == '') {
            trigger_error('t_template_file empty', E_USER_WARNING);
        }
        
        if ($this->is_caching_enabled() == false || $this->is_cached() == false) {
            # assign module content
            if ($this->get_flat_assigns() == false) {
                $this->v_coo_smarty->assign('content_data', $t_content_data_array);
            } else {
                foreach ($t_content_data_array as $t_data_key => $t_data_value) {
                    $this->v_coo_smarty->assign($t_data_key, $t_data_value);
                }
            }
            
            # assign global content
            $this->v_coo_smarty->assign('session_id_placeholder', $this->get_session_id_placeholder());
            $this->v_coo_smarty->assign('tpl_path',
                                        $this->get_shop_path() . StaticGXCoreLoader::getThemeControl()->getThemeHtmlPath());
            $this->v_coo_smarty->assign('theme_path', StaticGXCoreLoader::getThemeControl()->getThemePath());
            $this->v_coo_smarty->assign('language', $_SESSION['language']);
            $this->v_coo_smarty->assign('languages_id', $_SESSION['languages_id']);
            $this->v_coo_smarty->assign('language_code', $_SESSION['language_code']);
            $this->v_coo_smarty->assign('language_id', $_SESSION['languages_id']);
            $this->v_coo_smarty->assign('page_url', htmlspecialchars_wrapper(gm_get_env_info('REQUEST_URI')));
            if (StyleEditServiceFactory::service()->isEditing()) {
                $this->v_coo_smarty->assign('style_edit_active', true);
            }
        }
        
        # get html content
        $t_full_template_path = $this->v_template_dir . $t_template_file;
        
        if (strpos(':', $t_template_file) === false) // exclude templates from string
        {
            // LEGACY ABORT
            // Throws an exception if the template file is supposed to be in the deprecated admin/templates directory
            if (strpos($t_full_template_path, $this->get_shop_path() . 'admin/templates/') !== false) {
                throw new Exception('Use of admin/templates is deprecated, use admin/html/content instead');
            }
        }
        
        $dataCache             = DataCache::get_instance();
        $extenderTemplateFiles = [];
        $smartyFetchFiles      = [];
        
        // load extender templates from cache
        if ($dataCache->key_exists('smarty_fetch_files', true)) {
            $smartyFetchFiles = $dataCache->get_data('smarty_fetch_files', true);
            
            if (isset($smartyFetchFiles[$t_full_template_path])) {
                $extenderTemplateFiles = $smartyFetchFiles[$t_full_template_path];
            }
        }
        
        // check for extender templates, if cache is not available
        if (!isset($smartyFetchFiles[$t_full_template_path]) && $this->is_admin_file($t_full_template_path)) {
            $gxModulesFiles = GXModulesCache::getInstalledModuleFiles();
            foreach ($gxModulesFiles as $file) {
                $relativeFilePath = preg_replace('/.*\/(Admin\/Html\/.*)/i', '$1', $file);
                if ($t_full_template_path !== $file && stripos($relativeFilePath, 'admin/html/layout/') !== false) {
                    $extenderTemplateFiles[] = $file;
                    continue;
                }
                if ($t_full_template_path !== $file && stripos($t_full_template_path, $relativeFilePath) !== false) {
                    $extenderTemplateFiles[] = $file;
                }
            }
        }
        
        $fetchTemplatePath = $t_full_template_path;
        
        if (strpos($t_template_file, ':') === false) {
            $smartyFetchFiles[$t_full_template_path] = $extenderTemplateFiles;
            $dataCache->set_data('smarty_fetch_files', $smartyFetchFiles, true);
            
            if (file_exists($t_full_template_path)) {
                array_unshift($extenderTemplateFiles, $t_full_template_path);
            }
            
            $fetchTemplatePath = '';
            
            if (count($extenderTemplateFiles) > 1) {
                $fetchTemplatePath = 'extends:';
            } elseif (!count($extenderTemplateFiles)) {
                trigger_error('t_template_file does not exist: ' . $t_full_template_path, E_USER_ERROR);
            }
            
            $fetchTemplatePath .= implode('|', $extenderTemplateFiles);
        }
        $t_html_output = $this->v_coo_smarty->fetch($fetchTemplatePath, $this->get_cache_id());
        
        # insert session_ids
        $t_html_output = $this->replace_session_id_placeholder($t_html_output);
        
        return $t_html_output;
    }
    
    protected function is_admin_file($p_full_template_path) : bool
    {
        return stripos($p_full_template_path,$this->get_shop_path() . StaticGXCoreLoader::getThemeControl()->getPublishedThemePath()) === false;
        
    }
    
    
    function set_caching_enabled($p_status)
    {
        $this->v_caching_enabled = $p_status;
    }
    
    
    function is_caching_enabled()
    {
        return $this->v_caching_enabled;
    }
    
    
    function set_compile_check_enabled($p_status)
    {
        $this->v_compile_check_enabled = $p_status;
    }
    
    
    function is_compile_check_enabled()
    {
        return $this->v_compile_check_enabled;
    }
    
    
    function replace_session_id_placeholder($p_content)
    {
        $t_placeholder = $this->get_session_id_placeholder();
        $t_session_id  = xtc_session_id();
        
        $t_output = str_replace($t_placeholder, $t_session_id, $p_content);
        
        return $t_output;
    }
    
    
    function get_session_id_placeholder()
    {
        return $this->v_session_id_placeholder;
    }
    
    
    /**
     * get the first template from folder
     *
     * this function gets the first template from the folder,
     * if the given filepath not an file
     *
     * @param string $filePath Path to the templates
     * @param string $prefix   prefix of the type of template
     * @param string $template Name of the template
     *
     * @return string Template basename
     */
    function get_default_template($filePath, $prefix, $template = 'default')
    {
        $template = $prefix . $template;
        // get default template if given template not exists
        if (!is_file($filePath . $template)) {
            // get all html templates and select the first
            $files    = glob($filePath . $prefix . '*.html');
            $i = 0;
            while($this->is_extension_file($files[$i])){
                $i++;
            }
            $template = basename($files[$i]);
        }
        
        return $template;
    }
    
    
    function before_build_html()
    {
        // called at the beginning of build_html()
        // e.g. can be used in overload to set additional content data for html-template
    }
    
    
    /**
     * Set whether HTML must be escaped automatically.
     *
     * @link http://www.smarty.net/docs/en/variable.escape.html.tpl
     *
     * @param bool $value
     */
    public function set_escape_html($value)
    {
        $this->escape_html = $value;
    }

    /**
     * @param string $filename
     * @return bool
     */
    protected function is_extension_file(string $filename){
        //check if is not an extended file
        $result = false;
        $file_info = pathinfo($filename);
        $templatePath = dirname($filename);
        $filename_parts = explode('.',$file_info['filename']);
        if(is_numeric(end($filename_parts))){
            array_pop($filename_parts);
            $original_filename = implode('.', $filename_parts).'.'.$file_info['extension'];
            $result = file_exists($templatePath.DIRECTORY_SEPARATOR.$original_filename);
        }
        return $result;
    }
}
