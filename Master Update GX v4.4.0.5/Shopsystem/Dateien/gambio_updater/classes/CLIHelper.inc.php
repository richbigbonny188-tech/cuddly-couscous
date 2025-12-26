<?php
/* --------------------------------------------------------------
   CLIHelper.inc.php 2016-12-30
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2016 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class CLIHelper
 */
class CLIHelper
{
    protected static $updateParameterMapping = [
        1 => 'security_token',
        2 => 'execute_file_operations'
    ];
    
    protected static $updateFilesParameterMapping = [
        1 => 'security_token'
    ];
    
    protected static $upgradeParameterMapping = [
        1 => 'security_token',
        2 => 'price_model'
    ];
    
    
    /**
     * Echoes a string and adds a Timestamp and a linebreak
     *
     * @param string $string
     */
    public static function doLog($string = '')
    {
        echo '[' . date('Y-m-d H:i:s') . '] ' . $string . "\n";
    }
    
    
    /**
     * Echoes a string and adds a Timestamp and a linebreak and executes it via system()
     *
     * @param string $string
     */
    public static function doSystem($string = '')
    {
        self::doLog($string);
        system($string);
    }
    
    
    /**
     * Reports SQL errors and terminates the script, if any SQL errors occurred during the update process
     *
     * @param DatabaseModel $update
     */
    public static function reportUpdateDBErrors(DatabaseModel $update)
    {
        $sqlErrors = $update->get_sql_errors();
        if (count($sqlErrors) !== 0) {
            foreach ($sqlErrors as $error) {
                self::doLog($error['error']);
                self::doLog('Query:');
                self::doLog($error['query']);
            }
            
            exit(0);
        }
    }
    
    
    /**
     * Transforms parameters (commandline or GET parameters) into a normalized array
     *
     * @param array|null $commandlineArguments
     *
     * @return array
     */
    public static function getUpdateParameters(array $commandlineArguments = null)
    {
        return $_GET ? : self::_mapArguments($commandlineArguments, self::$updateParameterMapping) ? : [];
    }
    
    
    /**
     * Transforms parameters (commandline or GET parameters) into a normalized array
     *
     * @param array|null $commandlineArguments
     *
     * @return array
     */
    public static function getUpdateFilesParameters(array $commandlineArguments = null)
    {
        return $_GET ? : self::_mapArguments($commandlineArguments, self::$updateFilesParameterMapping) ? : [];
    }
    
    
    /**
     * Transforms parameters (commandline or GET parameters) into a normalized array
     *
     * @param array|null $commandlineArguments
     *
     * @return array
     */
    public static function getUpgradeParameters(array $commandlineArguments = null)
    {
        return $_GET ? : self::_mapArguments($commandlineArguments, self::$upgradeParameterMapping) ? : [];
    }
    
    
    /**
     * Maps all expected commandline arguments
     *
     * @param array $arguments       Given commandline arguments
     * @param array $argumentMapping Mapping array
     *
     * @return array All expected parameters
     */
    protected static function _mapArguments(array $arguments, array $argumentMapping)
    {
        self::doLog('Mapping arguments...');
        $parameters = [];
        
        foreach ($argumentMapping as $index => $parameterKey) {
            $parameters[$parameterKey] = array_key_exists($index, $arguments) ? $arguments[$index] : '';
        }
        
        return $parameters;
    }
    
    
    /**
     * Returns a GambioUpdateControl instance
     *
     * @return \GambioUpdateControl
     */
    public static function getGambioUpdateControl()
    {
        self::doLog('Loading available updates...');
        $updateControl = new GambioUpdateControl(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
        
        return $updateControl;
    }
    
    
    /**
     * Gets and returns an array of all given commandline options
     *
     * @return array Commandline options
     */
    public static function getOptions()
    {
        self::doLog('Gathering commandline options...');
        $shortOptions = '';
        $longOptions  = [
            'help'
        ];
        
        return getopt($shortOptions, $longOptions) ? : [];
    }
    
    
    /**
     * Proceeds options
     *
     * @param array $options
     */
    public static function proceedOptions(array $options)
    {
        self::doLog('Proceeding commandline options...');
        if (array_key_exists('help', $options)) {
            self::doLog('HELP!');
        }
    }
    
    
    /**
     * Authenticates an admin by a given security token.
     *
     * @param string $token The security token
     *
     * @return bool Indicates if authentication failed or succeeded
     */
    public static function authenticateAdmin($token = '')
    {
        self::doLog('Authenticating admin...');
        if (UpdaterLogin::auth($token) !== true) {
            self::doLog('Permission denied.');
            exit(0);
        }
    }
    
    
    /**
     * Processes the available, compatible updates
     *
     * @param \GambioUpdateControl $updateControl
     */
    public static function processUpdates(GambioUpdateControl $updateControl)
    {
        self::doLog('Processing updates...');
        foreach ($updateControl->gambio_update_array as $update) {
            self::doLog($update->get_name() . ':');
            self::_executeUpdateDependent($update);
            self::_executeUpdateIndependent($update);
            self::_executeUpdateCSS($update);
            self::_executeUpdateVersionHistory($update);
        }
    }
    
    
    /**
     * Executes all dependent DB operations of a given update
     *
     * @param \GambioUpdateModel $update
     */
    protected static function _executeUpdateDependent(GambioUpdateModel $update)
    {
        self::doLog('...dependent');
        $update->update_dependent_data();
        self::reportUpdateDBErrors($update);
    }
    
    
    /**
     * Executes all independent DB operations of a given update
     *
     * @param \GambioUpdateModel $update
     */
    protected static function _executeUpdateIndependent(GambioUpdateModel $update)
    {
        self::doLog('...independent');
        $update->update_independent_data();
        self::reportUpdateDBErrors($update);
    }
    
    
    /**
     * Updates CSS changes (EyeCandy only) of a given update
     *
     * @param \GambioUpdateModel $update
     */
    protected static function _executeUpdateCSS(GambioUpdateModel $update)
    {
        self::doLog('...CSS');
        $update->update_css();
        self::reportUpdateDBErrors($update);
    }
    
    
    /**
     * Sets the current shop version to the version of the given update
     *
     * @param \GambioUpdateModel $update
     */
    protected static function _executeUpdateVersionHistory(GambioUpdateModel $update)
    {
        self::doLog('...version history');
        $update->update_version_history();
        self::reportUpdateDBErrors($update);
    }
    
    
    /**
     * Executes the file movements for all available, compatible updates
     *
     * @param \GambioUpdateControl $updateControl
     */
    public static function moveFiles(GambioUpdateControl $updateControl)
    {
        self::doLog('Moving files...');
        FilesystemManager::move($updateControl->get_move_array());
    }
    
    
    /**
     * Executes the file deletions for all available, compatible updates
     *
     * @param \GambioUpdateControl $updateControl
     */
    public static function deleteFiles(GambioUpdateControl $updateControl)
    {
        self::doLog('Deleting files...');
        FilesystemManager::delete(array_filter($updateControl->get_delete_list(), function ($value) {
            return trim($value) !== '';
        }));
    }
    
    
    /**
     * Checks and sets full file permissions according to the files that are pointed at by the given file paths
     *
     * @param string $singleChmodFilePath    File path to a list of single files and directories
     * @param string $recursiveChmodFilePath File path to a list of directories that should be processed recursively
     */
    public static function changeFilePermissions($singleChmodFilePath = '', $recursiveChmodFilePath = '')
    {
        self::doLog('Changing file permissions...');
        if ($singleChmodFilePath !== '' && file_exists($singleChmodFilePath)) {
            FilesystemManager::singleChmod($singleChmodFilePath);
        }
        
        self::doLog('Changing file permissions recursively...');
        if ($recursiveChmodFilePath !== '' && file_exists($recursiveChmodFilePath)) {
            FilesystemManager::recursiveChmod($recursiveChmodFilePath);
        }
    }
    
    
    /**
     * Clears all caches and rebuilds index tables
     *
     * @param \GambioUpdateControl $updateControl
     */
    public static function clearCache(GambioUpdateControl $updateControl)
    {
        self::doLog('Clearing caches...');
        xtc_db_connect() or die('Unable to connect to database server!');
        $updateControl->clear_cache();
        
        self::doLog('Rebuilding caches...');
        xtc_db_connect() or die('Unable to connect to database server!');
        chdir(DIR_FS_CATALOG);
        $updateControl->rebuild_cache();
    }
    
    
    /**
     * Sets the configuration value 'INSTALLED_VERSION' to the value contained in the release_info.php
     *
     * @param \GambioUpdateControl $updateControl
     */
    public static function setInstalledVersion(GambioUpdateControl $updateControl)
    {
        self::doLog('Setting INSTALLED_VERSION configuration...');
        $updateControl->set_installed_version();
    }
    
    
    /**
     * Creates flag files so the PostUpdateShop- and PostUpdateAdminExtenders will be processed
     */
    public static function createPostUpdateFlags()
    {
        touch(DIR_FS_CATALOG . 'cache/execute_post_update_shop_extenders');
        touch(DIR_FS_CATALOG . 'cache/execute_post_update_admin_extenders');
        self::doSystem('touch "' . DIR_FS_CATALOG . 'cache/execute_post_update_shop_extenders"');
        self::doSystem('touch "' . DIR_FS_CATALOG . 'cache/execute_post_update_admin_extenders"');
    }
    
    
    /**
     * Creates flag files so the PostUpgradeShop- and PostUpgradeAdminExtenders will be processed
     */
    public static function createPostUpgradeFlags()
    {
        self::doSystem('touch "' . DIR_FS_CATALOG . 'cache/execute_post_upgrade_shop_extenders"');
        self::doSystem('touch "' . DIR_FS_CATALOG . 'cache/execute_post_upgrade_admin_extenders"');
    }
    
    
    /**
     * Creates a new price model flag and deletes all other the flags
     *
     * @param $newPriceModel string The new price model flag
     */
    public static function createPriceModelFlag($newPriceModel)
    {
        $priceModels = ['startup', 'small_business', 'professional'];
        if (!in_array($newPriceModel, $priceModels, true)) {
            self::doLog('Error: Unknown price model.');
            exit(0);
        }
        
        foreach ($priceModels as $priceModel) {
            @unlink(DIR_FS_CATALOG . 'debug/.' . $priceModel);
        }
        
        self::doSystem('touch "' . DIR_FS_CATALOG . 'debug/.' . $newPriceModel . '"');
    }
    
    
    /**
     * Executes all file operations for an update (move files, delete files, change file permissions)
     * 
     * @param \GambioUpdateControl $updateControl
     * @param string               $singleChmodFilePath
     * @param string               $recursiveChmodFilePath
     */
    public static function executeFileOperations(GambioUpdateControl $updateControl, $singleChmodFilePath, $recursiveChmodFilePath)
    {
        self::doLog('Executing file operations...');
        $updateControl = self::loadFileOperationVersion($updateControl);
        self::moveFiles($updateControl);
        self::deleteFiles($updateControl);
        self::changeFilePermissions($singleChmodFilePath, $recursiveChmodFilePath);
        self::writeFileOperationVersion($updateControl);
    }
    
    
    /**
     * Sets the version for the UpdateControl by a possibly existing file /gambio_updater/file_operation_version and
     * reloads the list of relevant updates
     *
     * @param \GambioUpdateControl $updateControl
     *
     * @return \GambioUpdateControl
     */
    protected static function loadFileOperationVersion(GambioUpdateControl $updateControl)
    {
        self::doLog('Loading file operation version...');
        if ($fileOperationVersionConfiguration = @file_get_contents(DIR_FS_CATALOG
                                                                   . 'gambio_updater/file_operation_version')
        ) {
            $updateControl->current_db_version = $fileOperationVersionConfiguration;
            $updateControl->gambio_update_array = [];
            $updateControl->load_updates($fileOperationVersionConfiguration);
            $updateControl->sort_updates();
        }
        foreach($updateControl->gambio_update_array as $update)
        {
            echo $update . "\n";
        }
        
        return $updateControl;
    }
    
    
    /**
     * Writes the current version from UpdateControl to a file /gambio_updater/file_operation_version
     * 
     * @param \GambioUpdateControl $updateControl
     */
    protected static function writeFileOperationVersion(GambioUpdateControl $updateControl)
    {
        self::doLog('Setting new file operation version...');
        $lastUpdate = array_pop($updateControl->gambio_update_array);
        // Version 3.2.2.0 was the first cloud release version. All file operations should be executed for every update.
        //file_put_contents(DIR_FS_CATALOG . 'gambio_updater/file_operation_version', '3.2.2.0');
        if(!empty($lastUpdate)) {
            file_put_contents(DIR_FS_CATALOG . 'gambio_updater/file_operation_version', $lastUpdate->get_update_version());
        }
    }
}