<?php
/* --------------------------------------------------------------
 ApiRoutesRegistration.php 2020-03-10
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Api\Application\Kernel\Bootstrapper;

use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;
use Slim\App as SlimApp;

/**
 * Class ApiRoutesRegistration
 * @package Gambio\Api\Application\Kernel\Bootstrapper
 */
class ApiRoutesRegistration implements Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        $routes = include __DIR__ . '/../../routes.php';
        $routes($application->get(SlimApp::class));
    }
}