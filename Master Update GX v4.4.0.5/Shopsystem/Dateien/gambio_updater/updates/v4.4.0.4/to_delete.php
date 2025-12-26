<?php
/* --------------------------------------------------------------
   to_delete.php 2022-02-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2022 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

clearstatcache(true);

$toDelete = ['GXModules/Gambio/SecurityUpdate202201/*'];

// check if Google Services 3 exists
if (file_exists(DIR_FS_CATALOG . 'GXModules/Gambio/Google/ECommerce')
    && file_exists(DIR_FS_CATALOG . 'GXModules/Gambio/GoogleECommerce')) {
    
    $toDelete = [
        'GXModules/Gambio/Google/Admin/Classes/*',
        'GXModules/Gambio/Google/Admin/Html/*',
        'GXModules/Gambio/Google/Admin/Module/*',
        'GXModules/Gambio/Google/Admin/TextPhrases/*',
        'GXModules/Gambio/GoogleAdWords/*',
        'GXModules/Gambio/GoogleECommerce/*',
        'GXModules/Gambio/GoogleOAuth/*',
        'GXModules/Gambio/GoogleShopping/*',
        'GXModules/Gambio/GoogleTracking/*',
        'GXModules/Gambio/SecurityUpdate202201/*',
    ];
}

return $toDelete;