<?php
/* --------------------------------------------------------------
 AuthenticationMiddlewareServiceProvider.php 2020-10-05
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Api\Application\ServiceProviders;

use Gambio\Api\Application\Auth\BasicRequestAuthenticator;
use Gambio\Api\Application\Auth\BearerRequestAuthenticator;
use Gambio\Api\Application\Auth\Interfaces\WebRequestAuthenticationService;
use Gambio\Api\Application\Auth\RequestAuthenticationService;
use Gambio\Core\AdminAccess\PermissionService;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;
use Gambio\Core\Application\ValueObjects\Url;
use Gambio\Core\Auth\JsonWebTokenAuthenticator;
use Gambio\Core\Auth\UserAuthenticator;

/**
 * Class AuthenticationMiddlewareServiceProvider
 * @package Gambio\Api\Application\ServiceProvider
 */
class RequestAuthenticationServiceServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            WebRequestAuthenticationService::class
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->register(BasicRequestAuthenticator::class)->addArgument(UserAuthenticator::class);
        $this->application->register(BearerRequestAuthenticator::class)->addArgument(JsonWebTokenAuthenticator::class);
        
        $this->application->register(WebRequestAuthenticationService::class, RequestAuthenticationService::class)
            ->addArgument(PermissionService::class)
            ->addArgument(Url::class)
            ->addArgument(BasicRequestAuthenticator::class)
            ->addArgument(BearerRequestAuthenticator::class);
    }
}