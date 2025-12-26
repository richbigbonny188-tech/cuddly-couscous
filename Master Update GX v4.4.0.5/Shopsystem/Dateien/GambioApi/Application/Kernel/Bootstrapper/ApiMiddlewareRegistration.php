<?php
/* --------------------------------------------------------------
   ApiMiddlewareRegistration.php 2020-10-05
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Application\Kernel\Bootstrapper;

use Gambio\Api\Application\Middleware\AuthenticationMiddleware;
use Gambio\Api\Application\Middleware\RateLimitMiddleware;
use Gambio\Api\Application\Middleware\VersionsMiddleware;
use Gambio\Api\Application\ServiceProviders\ApiMiddlewareServiceProvider;
use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;
use Slim\App as SlimApp;

/**
 * Class ApiMiddlewareRegistration
 *
 * @package Gambio\Api\Application\Kernel\Bootstrapper
 */
class ApiMiddlewareRegistration implements Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        $application->registerProvider(ApiMiddlewareServiceProvider::class);
        
        /** @var SlimApp $slim */
        $slim = $application->get(SlimApp::class);
        $slim->add(RateLimitMiddleware::class);
        $slim->add(AuthenticationMiddleware::class);
        $slim->add(VersionsMiddleware::class);
    }
}