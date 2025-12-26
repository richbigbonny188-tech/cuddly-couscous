<?php
/* --------------------------------------------------------------
   define_database.php 2021-01-12
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------*/

/**
 * @deprecated Not used anymore in GX >= 4.3.2 installations. It is still used in shops having the old configure.php.
 * 
 * @param $host
 * @param $user
 * @param $pwd
 * @param $db
 * @param $p_connect
 */
function define_database($host, $user, $pwd, $db, $p_connect) {
    define('DB_SERVER', $host); // eg, localhost - should not be empty for productive servers
    define('DB_SERVER_USERNAME', $user);
    define('DB_SERVER_PASSWORD', $pwd);
    if (file_exists(__DIR__.'/../.dev-environment')
        && isset($_SERVER['HTTP_X_GXDB'])
    ) {
        define('DB_DATABASE', $_SERVER['HTTP_X_GXDB']);
    } else {
        define('DB_DATABASE', $db);
    }
    define('USE_PCONNECT', $p_connect); // use persistent connections?
}
