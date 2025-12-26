<?php
/* --------------------------------------------------------------
   update.cli.php 2019-02-22
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2017 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_CORE_ERROR & ~E_CORE_WARNING & ~E_DEPRECATED);

if(file_exists(__DIR__ . '/../../includes/local/configure.php'))
{
    require_once(__DIR__ . '/../../includes/local/configure.php');
}
else
{
    require_once(__DIR__ . '/../../includes/configure.php');
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../GXMainComponents/Shared/LegacyDependencyContainer.php';

define('APPLICATION_RUN_MODE', 'frontend');

require_once(DIR_FS_INC . 'htmlentities_wrapper.inc.php');
require_once(DIR_FS_INC . 'htmlspecialchars_wrapper.inc.php');
require_once(DIR_FS_INC . 'html_entity_decode_wrapper.inc.php');
require_once(DIR_FS_INC . 'strlen_wrapper.inc.php');
require_once(DIR_FS_INC . 'substr_wrapper.inc.php');
require_once(DIR_FS_INC . 'strpos_wrapper.inc.php');
require_once(DIR_FS_INC . 'strrpos_wrapper.inc.php');
require_once(DIR_FS_INC . 'strtolower_wrapper.inc.php');
require_once(DIR_FS_INC . 'strtoupper_wrapper.inc.php');
require_once(DIR_FS_INC . 'substr_count_wrapper.inc.php');
require_once(DIR_FS_INC . 'utf8_encode_wrapper.inc.php');

require_once(DIR_FS_CATALOG . 'gambio_updater/classes/UpdaterLogin.inc.php');
require_once(DIR_FS_CATALOG . 'gambio_updater/classes/CLIHelper.inc.php');
require_once(DIR_FS_CATALOG . 'gambio_updater/classes/FilesystemManager.inc.php');
require_once(DIR_FS_CATALOG . 'gambio_updater/classes/GambioUpdateControl.inc.php');
require_once(DIR_FS_CATALOG . 'system/core/logging/LogControl.inc.php');

// include the list of project filenames
require(DIR_WS_INCLUDES . 'filenames.php');

// include the list of project database tables
require(DIR_WS_INCLUDES . 'database_tables.php');

// SQL caching dir
define('SQL_CACHEDIR', DIR_FS_CATALOG . 'cache/');

// DEPRECATED: Please use gm_get_conf('GRADUATED_ASSIGN') instead
// graduated prices model or products assigned ?
define('GRADUATED_ASSIGN', 'true');

/**
 * @var string $singleChmodFilePath
 */
$singleChmodFilePath = DIR_FS_CATALOG . 'version_info/lists/chmod.txt';

/**
 * @var string $recursiveChmodFilePath
 */
$recursiveChmodFilePath = DIR_FS_CATALOG . 'version_info/lists/chmod_all.txt';

// Database
require_once(DIR_FS_INC . 'xtc_db_connect.inc.php');
require_once(DIR_FS_INC . 'xtc_db_close.inc.php');
require_once(DIR_FS_INC . 'xtc_db_error.inc.php');
require_once(DIR_FS_INC . 'xtc_db_perform.inc.php');
require_once(DIR_FS_INC . 'xtc_db_query.inc.php');
require_once(DIR_FS_INC . 'xtc_db_fetch_array.inc.php');
require_once(DIR_FS_INC . 'xtc_db_num_rows.inc.php');
require_once(DIR_FS_INC . 'xtc_db_data_seek.inc.php');
require_once(DIR_FS_INC . 'xtc_db_insert_id.inc.php');
require_once(DIR_FS_INC . 'xtc_db_free_result.inc.php');
require_once(DIR_FS_INC . 'xtc_db_fetch_fields.inc.php');
require_once(DIR_FS_INC . 'xtc_db_output.inc.php');
require_once(DIR_FS_INC . 'xtc_db_input.inc.php');
require_once(DIR_FS_INC . 'xtc_db_prepare_input.inc.php');

function debug_notice($notice)
{
    CLIHelper::doLog($notice);
}
