<?php
/* --------------------------------------------------------------
  LoginAdminHelper.inc.php 2021-01-05
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

declare(strict_types=1);

/**
 * Class LoginAdminHelper
 * can redirect to login_admin.php or return url of login_admin.php
 */
class LoginAdminHelper
{
    /**
     * Redirects to the login_admin.php
     */
    public static function redirect(): void
    {
        static::load_constants();

        header('Cache-Control: no-store, max-age=0');
        header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
        header('Pragma: no-cache');
        header('Location: ' . static::loginAdminUrl());
        die;
    }
    
    
    /**
     * @return string
     */
    protected static function loginAdminUrl(): string
    {
       return (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . 'login_admin.php';
    }
    
    
    /**
     *
     */
    protected static function load_constants(): void
    {
        require_once dirname(__DIR__, 3) . '/includes/configure.php';
    }
}