<?php
/* --------------------------------------------------------------
 ApiBootstrapper.php 2021-01-08
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Api\Application\Kernel;

use Gambio\Api\Application\Kernel\Bootstrapper\ApiErrorHandlerRegistration;
use Gambio\Api\Application\Kernel\Bootstrapper\ApiMiddlewareRegistration;
use Gambio\Api\Application\Kernel\Bootstrapper\ApiRoutesRegistration;
use Gambio\Api\Application\Kernel\Bootstrapper\ApiServiceProviderRegistration;
use Gambio\Api\Application\Kernel\Bootstrapper\LoadAdminShopConfiguration;
use Gambio\Core\Application\Bootstrapper\CoreBootstrapper;
use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;

/**
 * Class ApiBootstrapper
 * @package Gambio\Api\Application\Kernel
 */
class ApiBootstrapper extends CoreBootstrapper implements Bootstrapper
{
    /**
     * Defines the current API version.
     */
    public const VERSION = '3.0.0';
    
    
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        (new LoadAdminShopConfiguration())->boot($application);
        $this->registerEventDispatcher($application);
        $this->registerCommandDispatcher($application);
        
        $this->registerCoreServiceProvider($application);
        $this->initDefaultServerConfiguration($application);
        (new ApiServiceProviderRegistration())->boot($application);
        
        $this->registerSlimFramework($application);
        (new ApiErrorHandlerRegistration())->boot($application);
        (new ApiMiddlewareRegistration())->boot($application);
        (new ApiRoutesRegistration())->boot($application);
    }
}