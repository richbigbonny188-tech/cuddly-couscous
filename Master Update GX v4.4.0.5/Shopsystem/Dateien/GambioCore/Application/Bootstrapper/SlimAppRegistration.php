<?php
/* --------------------------------------------------------------
 SlimAppRegistration.php 2020-03-10
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Bootstrapper;

use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App as SlimApp;
use Slim\Factory\AppFactory;
use Slim\Interfaces\CallableResolverInterface;

/**
 * Class SlimAppRegistration
 * @package Gambio\Core\Application\Bootstrapper
 */
class SlimAppRegistration implements Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        $application->registerShared(
            ResponseFactoryInterface::class,
            // @codeCoverageIgnoreStart
            static function () {
                return AppFactory::determineResponseFactory();
            }
        // @codeCoverageIgnoreEnd
        );
        
        $slim = AppFactory::createFromContainer($application);
        $slim->addRoutingMiddleware();
        
        $application->registerShared(SlimApp::class, $slim);
        $application->registerShared(CallableResolverInterface::class, $slim->getCallableResolver());
    }
}