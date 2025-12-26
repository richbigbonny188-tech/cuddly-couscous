<?php
/* --------------------------------------------------------------
   SecurityCheck.inc.php 2020-09-23
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class SecurityCheck
 */
class SecurityCheck
{
	/**
	 * @var array
	 */
	protected static $chmodList = array();
	
	/**
	 * @var array
	 */
	protected static $chmodRecursiveList = array();
	
	/**
	 * @var array
	 */
	protected static $writable = array();
	
	/**
	 * @var array
	 */
	protected static $nonWritable = array();
	
	/**
	 * @var float|null
	 */
	protected static $expectedHtaccessVersion = null;
	
	/**
	 * @var array
	 */
	protected static $ignoredPaths = array(
		'admin/includes/magnalister',
	);
	
	/**
	 * @var bool
	 */
	protected static $chmodRecursiveDirectoriesListCreated = false;
	
	/**
	 * @var bool
	 */
	protected static $ignoredPathsScanned = false;
	
	
	/**
	 * Returns an array which contains wrong permitted file paths as elements.
	 * The array is prepared for the updater logic.
	 *
	 * @return array
	 */
	public static function getWrongPermittedUpdaterFiles()
	{
		$wrongPermittedFiles = self::getWrongPermittedInstallerFiles(true);
		$updaterChmodArray   = array();
		
		foreach($wrongPermittedFiles as $file)
		{
			$updaterChmodArray[] = array('PATH' => $file, 'IS_DIR' => is_dir($file));
		}
		
		return $updaterChmodArray;
	}
	
	
	/**
	 * Returns an array which contains wrong permitted file paths as elements.
	 * The array us prepared for the installer logic.
	 *
	 * @param bool $ignoreConfigureFiles Ignore the includes/configure.php and includes/configure.org.php files.
	 *
	 * @return array
	 */
	public static function getWrongPermittedInstallerFiles($ignoreConfigureFiles = false)
	{
		self::_prepareChmodLists();
		$completeList        = array_merge(self::$chmodList, self::$chmodRecursiveList);
		$wrongPermittedFiles = array();
		
		$configure    = 'includes/configure.php';
		$configureOrg = 'includes/configure.org.php';
        
		foreach($completeList as $pathReference)
		{
			$path = DIR_FS_CATALOG . $pathReference;
			if(!self::_endWith($path, '.gitignore'))
			{
				if($ignoreConfigureFiles
				   && (self::_endWith($pathReference, $configure)
				       || self::_endWith($pathReference, $configureOrg)))
				{
					continue;
				}
				
				if(file_exists($path) && @!is_writable($path) && strpos($path, 'media/secure_token_') === false)
				{
					// set 777 rights only if path is not writable to prevent problems with servers which only need 755 rights to run properly
					@chmod($path, 0777);
					
					if(@!is_writable($path))
					{
						$wrongPermittedFiles[] = $path;
					}
				}
			}
		}
		
		return $wrongPermittedFiles;
	}
	
	
	/**
	 * Checks invalid file/directory permissions. Adds a message to the message stack if
	 * the non writable list contains writable files.
	 *
	 * @param messageStack $messageStack
	 * @param array|null   $nonWritable
	 */
	public static function checkNonWritableList(messageStack $messageStack, $nonWritable = null)
	{
        $dataCache = DataCache::get_instance();
        $lastPermissionsCheck = $dataCache->get_persistent_data('last-permissions-check');
        
        $configurationStorage = MainFactory::create('ConfigurationStorage', 'cronjobs/CheckPermissions');
        $moduleIsActive = (bool)$configurationStorage->get('active');
        $showDate = ($moduleIsActive && file_exists(DIR_FS_CATALOG . 'cache/cronjobs/last_run-check_permissions'))
            || $lastPermissionsCheck !== null;
        
        if ($nonWritable !== null) {
            self::$nonWritable = $nonWritable;
        } else {
            self::_prepareInvalidPermissions(true);
        }
        
        $lastRun = $lastPermissionsCheck !== null ? $lastPermissionsCheck->format(PHP_DATE_TIME_FORMAT) : '';
        if ($moduleIsActive && file_exists(DIR_FS_CATALOG . 'cache/cronjobs/last_run-check_permissions')) {
            $lastRunDate = new DateTime(file_get_contents(DIR_FS_CATALOG . 'cache/cronjobs/last_run-check_permissions'));
            $lastRun = $lastRunDate->format(PHP_DATE_TIME_FORMAT);
        }
        
        if (count(self::$nonWritable) > 0) {
            $message = '<br/>' . implode('<br/>', self::$nonWritable);
            $languageTextManager = MainFactory::create_object('LanguageTextManager', [], true);
            if ($showDate && $lastRun !== '') {
                $messageStack->add($languageTextManager->get_text('TEXT_FILE_WARNING', 'start') . '<b>' . $message
                                   . ' <br/><br/> ' . $languageTextManager->get_text('TEXT_LAST_RUN', 'start')
                                   . $lastRun . ' </b>',
                                   'error');
            } else {
                $messageStack->add($languageTextManager->get_text('TEXT_FILE_WARNING', 'start') . '<b>' . $message
                                   . ' </b>',
                                   'error');
            }
        }
    }
    
    
    /**
     * Checks invalid file/directory permissions. Adds a message to the message stack if
     * the writable list contains non writable files.
     *
     * @param messageStack $messageStack
     * @param array|null $writable
     */
    public static function checkWritableList(messageStack $messageStack, $writable = null)
    {
        $dataCache = DataCache::get_instance();
        $lastPermissionsCheck = $dataCache->get_persistent_data('last-permissions-check');
        
        $configurationStorage = MainFactory::create('ConfigurationStorage', 'cronjobs/CheckPermissions');
        $moduleIsActive = (bool)$configurationStorage->get('active');
        $showDate = ($moduleIsActive && file_exists(DIR_FS_CATALOG . 'cache/cronjobs/last_run-check_permissions'))
            || $lastPermissionsCheck !== null;
        
        if ($writable !== null) {
            self::$writable = $writable;
        } else {
            self::_prepareInvalidPermissions(true);
        }
        
        $lastRun = $lastPermissionsCheck !== null ? $lastPermissionsCheck->format(PHP_DATE_TIME_FORMAT) : '';
        if ($moduleIsActive && file_exists(DIR_FS_CATALOG . 'cache/cronjobs/last_run-check_permissions')) {
            $lastRunDate = new DateTime(file_get_contents(DIR_FS_CATALOG . 'cache/cronjobs/last_run-check_permissions'));
            $lastRun = $lastRunDate->format(PHP_DATE_TIME_FORMAT);
        }
        
        if (count(self::$writable) > 0) {
            $message = '<br/>' . implode('<br/>', self::$writable);
            
            if ($showDate && $lastRun !== '') {
                $messageStack->add(TEXT_FOLDER_WARNING . '<b>' . $message . ' <br/><br/>' . TEXT_LAST_RUN . $lastRun . ' </b>',
                    'error');
            } else {
                $messageStack->add(TEXT_FOLDER_WARNING . '<b>' . $message . '</b>', 'error');
            }
        }
    }
    
    
    /**
     * Checks if the .htaccess file in the shop root directory has the required version. Adds a message to the message
     * stack if the .htaccess file has not the required version.
     *
     * @param messageStack $messageStack
     */
    public static function checkHtaccessVersion(messageStack $messageStack)
    {
        if (self::getHtaccessVersion() < self::getExpectedHtaccessVersion()) {
            $messageStack->add(TEXT_HTACCESS_VERSION_WARNING, 'warning');
        }
    }
    
    
    /**
     * Returns the .htaccess version
     *
     * @return float
     */
    public static function getHtaccessVersion()
    {
        static $htaccessVersion;
        
        if ($htaccessVersion === null) {
            $htaccessVersion = 0.0;
            if (isset($_SERVER['gambio_htaccessVersion'])) {
                $htaccessVersion = (float)$_SERVER['gambio_htaccessVersion'];
            } elseif (isset($_SERVER['REDIRECT_gambio_htaccessVersion'])) {
                $htaccessVersion = (float)$_SERVER['REDIRECT_gambio_htaccessVersion'];
            } elseif (file_exists(DIR_FS_CATALOG . '.htaccess')) {
                $htaccess = file(DIR_FS_CATALOG . '.htaccess');
                
                foreach ($htaccess as $line) {
                    if (preg_match('/gambio_htaccessVersion\s(.?\..?)/', trim($line), $matches)) {
                        $htaccessVersion = (float)$matches[1];
                        break;
                    }
                }
            }
        }
        
        return $htaccessVersion;
    }
    
    
    /**
     * Returns the expected .htaccess version
     *
     * @return float
     */
    public static function getExpectedHtaccessVersion()
    {
        if (isset(self::$expectedHtaccessVersion)) {
            return self::$expectedHtaccessVersion;
        }
        
        $htaccessVersionFilePath = DIR_FS_CATALOG . 'version_info/htaccessVersion.php';
        if (file_exists($htaccessVersionFilePath)) {
            require $htaccessVersionFilePath;
            self::$expectedHtaccessVersion = $expectedHtaccessVersion;
            
            return self::$expectedHtaccessVersion;
        }
        
        require DIR_FS_CATALOG . 'release_info.php';
        $shopVersion = str_replace(array('v', '(', ')', '_'), '', $gx_version);
        if (version_compare(strtolower($shopVersion), '3.5.1.0 beta1', '<') === true) {
            $expectedHtaccessVersion = 1.0;
        } elseif (version_compare(strtolower($shopVersion), '3.5.2.0 beta1', '<') === true) {
            $expectedHtaccessVersion = 2.0;
        } elseif (version_compare(strtolower($shopVersion), '3.5.3.0 beta1', '<') === true) {
            $expectedHtaccessVersion = 2.2;
        } elseif (version_compare(strtolower($shopVersion), '3.5.3.1 beta1', '<') === true) {
            $expectedHtaccessVersion = 2.4;
        } elseif (version_compare(strtolower($shopVersion), '3.9.2.0 beta1', '<') === true) {
            $expectedHtaccessVersion = 2.5;
        } else {
            $expectedHtaccessVersion = 2.6;
        }
        
        @file_put_contents($htaccessVersionFilePath, self::getExpectedHtaccessVersionText($expectedHtaccessVersion));
        
        self::$expectedHtaccessVersion = $expectedHtaccessVersion;
        
        return self::$expectedHtaccessVersion;
    }
    
    
    /**
     * Returns the actual .htaccess version text
     *
     * @return string
     */
    public static function getExpectedHtaccessVersionText($version)
    {
        return '<?php $expectedHtaccessVersion = ' . (float)$version . '; ?>';
    }
    
    
    /**
     * Returns a list of files having wrong permissions. They should not be writable.
     *
     * @return array
     */
    public static function getInvalidPermissionsNonWritableList()
    {
        self::_prepareInvalidPermissions(true);
        
        return self::$nonWritable;
    }
    
    
    /**
     * Returns a list of files having wrong permissions. They should be writable.
     *
     * @return array
     */
    public static function getInvalidPermissionsWritableList()
    {
        self::_prepareInvalidPermissions(true);
        
        return self::$writable;
    }
    
    
    /**
     * Prepares the chmod lists.
     *
     * @param bool $excludeIgnoredPaths If true, paths from the self::$ignoredPaths property will be removed.
     */
    protected static function _prepareChmodLists($excludeIgnoredPaths = false)
    {
        self::_prepareChmodList();
        self::_prepareChmodRecursiveList($excludeIgnoredPaths);
        self::_prepareChmodRecursiveList($excludeIgnoredPaths, true);
    }
    
    
    /**
     * Prepares the chmod list, if not already done.
     */
    protected static function _prepareChmodList()
    {
        if (count(self::$chmodList) === 0) {
            self::$chmodList = array_map(array(__CLASS__, '_trimLeftSlash'),
                file(DIR_FS_CATALOG . 'version_info/lists/chmod.txt'));
        }
    }
    
    
    /**
     * Checks if the passed argument is in the ignored paths property.
     *
     * @param $element
     *
     * @return bool
     */
    protected static function _isPathIgnored($element)
    {
        return !in_array($element, self::$ignoredPaths);
    }
    
    
    /**
     * Prepares the chmod recursive list, if not already done.
     * Scans the directories which are listed recursively.
     *
     * @param bool $excludeIgnoredPaths If true, paths from the self::$ignoredPaths property will be removed.
     * @param bool $ignoreFiles If true, only directories will be added to the list.
     */
    protected static function _prepareChmodRecursiveList($excludeIgnoredPaths = false, $ignoreFiles = false)
    {
        if (($ignoreFiles && !self::$chmodRecursiveDirectoriesListCreated) || count(self::$chmodRecursiveList) === 0) {
            $recursivePath = DIR_FS_CATALOG . 'version_info/lists/chmod_all.txt';
            
            if ($ignoreFiles) {
                $recursivePath = DIR_FS_CATALOG . 'version_info/lists/chmod_all_directories.txt';
            }
            
            $recursiveList = array_map(array(__CLASS__, '_trimLeftSlash'), file($recursivePath));
            
            $recursiveList = array_filter($recursiveList, array(__CLASS__, '_isPathIgnored'));
            foreach ($recursiveList as $listItem) {
                if (is_dir(DIR_FS_CATALOG . $listItem) || (!$ignoreFiles && is_file(DIR_FS_CATALOG . $listItem))) {
                    self::$chmodRecursiveList = array_merge(self::$chmodRecursiveList,
                        self::_getDirContent($listItem, $ignoreFiles));
                }
            }
            
            // handle of excluded paths
            if (!$excludeIgnoredPaths && self::$ignoredPathsScanned === false) {
                foreach (self::$ignoredPaths as $ignoredPath) {
                    self::$chmodRecursiveList = array_merge(self::$chmodRecursiveList,
                        self::_getDirContent($ignoredPath));
                }
                
                self::$ignoredPathsScanned = true;
            }
            
            if ($ignoreFiles) {
                self::$chmodRecursiveDirectoriesListCreated = true;
            }
        }
    }
    
    
    /**
     * Returns an array of relative file/dir paths of a directory scanned recursively.
     *
     * @param string $dir relative directory path
     * @param bool $ignoreFiles only directories in result array
     * @param array $results
     *
     * @return array
     */
    protected static function _getDirContent($dir, $ignoreFiles = false, &$results = array())
    {
        // exclude node_modules folder existing in some dev shop environments
        if (strpos($dir, 'node_modules') !== false) {
            return $results;
        }
        
        if (count($results) === 0) {
            $results[] = $dir;
        }
        
        if ($ignoreFiles) {
            $files = glob(DIR_FS_CATALOG . $dir . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
        } else {
            $files = glob(DIR_FS_CATALOG . $dir . '/*', GLOB_NOSORT);
        }
        
        if (is_array($files)) {
            foreach ($files as $value) {
                $value = basename($value);
                if ($value === '.' || $value === '..') {
                    continue;
                }
                
                $path = $dir . '/' . $value;
                
                if ($ignoreFiles || is_dir(DIR_FS_CATALOG . $path)) {
                    self::_getDirContent($path, $ignoreFiles, $results);
                }
                
                $results[] = $path;
            }
        }
        
        return $results;
    }
    
    
    /**
     * Prepares the lists which contains information about invalid file permissions.
     *
     * @param bool $excludeIgnoredPaths If true, paths from the self::$ignoredPaths property will be removed.
     */
    protected static function _prepareInvalidPermissions($excludeIgnoredPaths = false)
    {
        self::_prepareChmodLists($excludeIgnoredPaths);
        
        if (count(self::$writable) === 0 || count(self::$nonWritable) === 0) {
            self::$writable = array();
            self::$nonWritable = array();
            
            $configure = 'includes/configure.php';
            $configureOrg = 'includes/configure.org.php';
            
            // handle chmod.txt
            foreach (self::$chmodList as $item) {
                $path = DIR_FS_CATALOG . $item;
                
                // configure files files must be non writable
                if ((self::_endWith($item, $configure) || self::_endWith($item, $configureOrg))
                    && is_writable($path)) {
                    self::$nonWritable[] = $path;
                } elseif (!self::_endWith($item, $configure) && !self::_endWith($item, $configureOrg)
                    && !is_writable($path)
                    && (is_dir($path) || is_file($path))) {
                    
                    self::$writable[] = $path;
                }
            }
            
            $adminConfigure = 'admin/includes/configure.php';
            $adminConfigureOrg = 'admin/includes/configure.org.php';
            
            // handle chmod_all.txt
            foreach (self::$chmodRecursiveList as $item) {
                $path = DIR_FS_CATALOG . $item;
                
                if (!self::_endWith($item, $adminConfigure) && !self::_endWith($item, $adminConfigureOrg)
                    && !is_writable($path)) {
                    self::$writable[] = $path;
                }
            }
        }
    }
    
    
    /**
     * Checks if the haystack string ends with needle.
     *
     * @param string $haystack Input string.
     * @param string $needle Expected end of string.
     *
     * @return bool
     */
    protected static function _endWith($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }
    
    
    protected static function _trimLeftSlash($element)
    {
        return ltrim(trim($element), '/');
    }
}
