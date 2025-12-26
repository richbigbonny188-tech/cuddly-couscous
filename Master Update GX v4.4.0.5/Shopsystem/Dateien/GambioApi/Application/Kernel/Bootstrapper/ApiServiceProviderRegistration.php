<?php
/* --------------------------------------------------------------
 ApiServiceProviderRegistration.php 2020-09-01
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Api\Application\Kernel\Bootstrapper;

use Gambio\Admin\Modules\ParcelService\ParcelServiceServiceProvider;
use Gambio\Admin\Modules\TrackingCode\TrackingCodeServiceProvider;
use Gambio\Admin\Modules\Withdrawal\WithdrawalServiceProvider;
use Gambio\Api\Application\ServiceProviders\ApiErrorHandlerServiceProvider;
use Gambio\Api\Application\ServiceProviders\ApiMiddlewareServiceProvider;
use Gambio\Api\Application\ServiceProviders\RequestAuthenticationServiceServiceProvider;
use Gambio\Api\Modules\ParcelService\ParcelServiceApiServiceProvider;
use Gambio\Api\Modules\TrackingCode\TrackingCodeApiServiceProvider;
use Gambio\Api\Modules\Withdrawal\WithdrawalApiServiceProvider;
use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;

/**
 * Class ApiServiceProviderRegistration
 * @package Gambio\Api\Application\Kernel\Bootstrapper
 */
class ApiServiceProviderRegistration implements Bootstrapper
{
    private const API_COMPONENT_SERVICE_PROVIDERS = [
        RequestAuthenticationServiceServiceProvider::class,
        ApiMiddlewareServiceProvider::class,
        ApiErrorHandlerServiceProvider::class,
    ];
    
    private const CONTROLLER_SERVICE_PROVIDERS = [
        WithdrawalApiServiceProvider::class,
        ParcelServiceApiServiceProvider::class,
        TrackingCodeApiServiceProvider::class,
    ];
    
    private const DOMAIN_SERVICE_PROVIDERS = [
        WithdrawalServiceProvider::class,
        ParcelServiceServiceProvider::class,
        TrackingCodeServiceProvider::class,
    ];
    
    
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        foreach (self::API_COMPONENT_SERVICE_PROVIDERS as $componentServiceProvider) {
            $application->registerProvider($componentServiceProvider);
        }
        foreach (self::CONTROLLER_SERVICE_PROVIDERS as $controllerServiceProvider) {
            $application->registerProvider($controllerServiceProvider);
        }
        foreach (self::DOMAIN_SERVICE_PROVIDERS as $domainServiceProvider) {
            $application->registerProvider($domainServiceProvider);
        }
    }
}