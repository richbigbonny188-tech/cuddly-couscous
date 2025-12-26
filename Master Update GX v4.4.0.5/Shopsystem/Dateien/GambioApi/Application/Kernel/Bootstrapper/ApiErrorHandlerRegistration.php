<?php
/* --------------------------------------------------------------
 ApiErrorHandlerRegistration.php 2020-03-20
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Api\Application\Kernel\Bootstrapper;

use Gambio\Api\Application\ApiErrorHandler;
use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;
use Slim\App as SlimApp;

/**
 * Class ApiErrorHandlerRegistration
 * @package Gambio\Api\Application\Kernel\Bootstrapper
 */
class ApiErrorHandlerRegistration implements Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        /** @var SlimApp $slim */
        $slim = $application->get(SlimApp::class);
        
        $debugMode       = file_exists(__DIR__ . '/../../../../.dev-environment');
        $errorMiddleware = $slim->addErrorMiddleware($debugMode, false, false);
        $errorMiddleware->setDefaultErrorHandler(ApiErrorHandler::class);
    }
}