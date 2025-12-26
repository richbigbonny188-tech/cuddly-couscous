<?php
/* --------------------------------------------------------------
   api_v3.php 2021-01-08
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

use Gambio\Api\Application\Kernel\ApiBootstrapper;
use Gambio\Core\Application\Application;
use Gambio\Core\Application\Kernel\HttpKernel;

ini_set('display_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
@date_default_timezone_set('Europe/Berlin');

require_once __DIR__ . '/vendor/autoload.php';

Application::main(new HttpKernel(), new ApiBootstrapper());