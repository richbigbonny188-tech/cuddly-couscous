<?php
/* --------------------------------------------------------------
   GXSmarty.inc.php 2020-06-23
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

class GXSmarty extends SmartyBC
{
    /**
     * @var bool|mixed|null
     */
    protected static $devEnvironmentEnabled;

    /**
     * GXSmarty constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->registerPlugin('block', 'php', 'smarty_php_tag');
        $this->setConfigDir('.' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR);
        $this->setupDevEnvironment();
    }

    /**
     * Configure Smarty in order to show more detailed information about the Smarty Blocks
     */
    protected function setupDevEnvironment()
    {
        if($this->isDevEnvironmentEnabled())
        {
            $this->template_class = GXSmartyTemplate::class;
        }
    }
    
    
    /**
     * creates a template object
     *
     * @param string  $template   the resource handle of the template file
     * @param mixed   $cache_id   cache id to be used with this template
     * @param mixed   $compile_id compile id to be used with this template
     * @param object  $parent     next higher level of Smarty variables
     * @param boolean $do_clone   flag is Smarty object shall be cloned
     *
     * @return object  template object
     */
    public function createTemplate($template, $cache_id = null, $compile_id = null, $parent = null, $do_clone = true)
    {
        $template = $this->_getGambioUsermodFilePath($template);
        
        return parent::createTemplate($template, $cache_id, $compile_id, $parent, $do_clone);
    }
    
    
    /**
     * Safely assigns the key and value to smarty.
     *
     * Safely means that if the key is already used, the value will be appended
     * instead of replaced.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function safeAssign(string $key, $value): void
    {
        $templateValue = $this->getTemplateVars($key);
        
        if (!$templateValue) {
            $this->assign($key, $value);
            
            return;
        }
        
        if (is_string($templateValue)) {
            $newValue = $templateValue . PHP_EOL . $value;
            $this->assign($key, $newValue);
            
            return;
        }
        
        if (is_array($templateValue)) {
            $templateValue[] = $value;
            $this->assign($key, $templateValue);
        }
    }
    
    
    /**
     * trigger Smarty error
     *
     * @param string  $error_msg
     * @param integer $error_type
     */
    public function trigger_error($error_msg, $error_type = E_USER_WARNING)
    {
        $msg = htmlentities_wrapper($error_msg);
        trigger_error("Smarty error: $msg", $error_type);
    }
    
    
    /**
     * This method checks if a USERMOD of the given template exists and replaces it with the USERMOD
     *
     * @param $p_template
     *
     * @return string
     */
    protected function _getGambioUsermodFilePath($p_template)
    {
        static $shopPath;

        if (defined('DIR_FS_CATALOG')) {
            if ($shopPath === null) {
                $shopPath = str_replace('\\',
                                        '/',
                                        realpath(DIR_FS_CATALOG)) . '/';
            }

            if (strlen(DIR_FS_CATALOG) > 1) {
                $strpos = strpos($p_template, DIR_FS_CATALOG);
                if ($strpos !== false) {
                    $p_template = substr($p_template, 0, $strpos) . $shopPath . substr($p_template,
                                                                                       $strpos
                                                                                       + strlen(DIR_FS_CATALOG));
                }
            }
        }
        
        $t_template = str_replace('\\', '/', $p_template);
        
        // try absolute path
        $usermodFilePath = get_usermod($t_template);
        if ($usermodFilePath != $t_template) {
            return $usermodFilePath;
        }
        
        if ((defined('DIR_FS_CATALOG') && strpos($t_template, $shopPath) !== 0)
            || defined('DIR_FS_CATALOG') === false) {
            // try relative path
            $templateDir = (is_array($this->template_dir) ? $this->template_dir[0] : $this->template_dir) . '/';
            if (strpos($t_template, $templateDir) === 0) {
                $templateDir = '';
            }
            
            $usermodFilePath = get_usermod($templateDir . $t_template);
            if ($usermodFilePath != $templateDir . $t_template) {
                return $usermodFilePath;
            }
        }
        
        // no usermod-resource found. return original
        return $t_template;
    }

    /**
     *
     */
    protected function isDevEnvironmentEnabled()
    {
        if(static::$devEnvironmentEnabled === null) {
            static::$devEnvironmentEnabled = defined('DIR_FS_CATALOG')
                && file_exists(DIR_FS_CATALOG . '.dev-environment');
        }
        return static::$devEnvironmentEnabled;
    }
}

/**
 * Smarty {php}{/php} block function
 *
 * @param array    $params   parameter list
 * @param string   $content  contents of the block
 * @param object   $template template object
 * @param boolean &$repeat   repeat flag
 *
 * @return string content re-formatted
 */
function smarty_php_tag($params, $content, $template, &$repeat)
{
    eval($content);
    
    return '';
}
