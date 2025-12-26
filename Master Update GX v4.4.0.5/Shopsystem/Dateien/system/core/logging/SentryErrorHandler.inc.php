<?php
/* --------------------------------------------------------------
   SentryErrorHandler.inc.php 2020-05-19
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/


$configPath = DIR_FS_CATALOG . 'GXModules/Gambio/ErrorReporting/configuration.json';

if(!file_exists($configPath)) {
	return;
}

$sentryConfig = json_decode(file_get_contents($configPath), true);

if (!$sentryConfig['active']) {
	return;
}

include(DIR_FS_CATALOG . 'release_info.php');

$environment = file_exists(DIR_FS_CATALOG . '.dev-environment')
               || strpos($gx_version, 'develop') !== false ? 'development' : 'production';

Sentry\init([
                'dsn'           => $sentryConfig['dsn'],
                'environment'   => $environment,
                'release'       => $gx_version,
                'prefixes'      => [DIR_FS_CATALOG, DIR_FS_DOCUMENT_ROOT],
                'error_types'   => E_ALL & ~E_NOTICE & ~E_USER_NOTICE & ~E_CORE_ERROR & ~E_CORE_WARNING & ~E_STRICT
                                   & ~E_DEPRECATED,
                'send_attempts' => 1,
            ]);

