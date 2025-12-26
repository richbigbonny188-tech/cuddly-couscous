<?php
/* --------------------------------------------------------------
   update.cli.php 2016-12-30
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2016 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

include_once('includes/cli_application.inc.php');

/**
 * @var array $parameters
 */
$parameters = CLIHelper::getUpdateParameters($argv);

/**
 * @var GambioUpdateControl $updateControl
 */
$updateControl = CLIHelper::getGambioUpdateControl();

CLIHelper::proceedOptions(CLIHelper::getOptions());
CLIHelper::authenticateAdmin($parameters['security_token']);
CLIHelper::processUpdates($updateControl);

if(isset($parameters['execute_file_operations']) && $parameters['execute_file_operations'] !== '')
{
    CLIHelper::executeFileOperations($updateControl, $singleChmodFilePath, $recursiveChmodFilePath);
}

CLIHelper::clearCache($updateControl);
CLIHelper::setInstalledVersion($updateControl);
CLIHelper::createPostUpdateFlags();

CLIHelper::doLog('Update process completed.');