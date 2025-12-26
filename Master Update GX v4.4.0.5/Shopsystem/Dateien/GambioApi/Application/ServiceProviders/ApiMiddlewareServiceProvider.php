<?php
/* --------------------------------------------------------------
 ApiMiddlewareServiceProvider.php 2020-04-30
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Api\Application\ServiceProviders;

use Gambio\Api\Application\Auth\Interfaces\WebRequestAuthenticationService;
use Gambio\Api\Application\Middleware\AuthenticationMiddleware;
use Gambio\Api\Application\Middleware\RateLimitMiddleware;
use Gambio\Api\Application\Middleware\VersionsMiddleware;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;
use Gambio\Core\Cache\CacheFactory;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * Class ApiMiddlewareServiceProvider
 * @package Gambio\Api\Application\ServiceProvider
 */
class ApiMiddlewareServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            VersionsMiddleware::class,
            AuthenticationMiddleware::class,
            RateLimitMiddleware::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->registerShared(VersionsMiddleware::class);
        
        $this->application->registerShared(RateLimitMiddleware::class,
            function () {
                /** @var CacheFactory $cacheFactory */
                $cacheFactory = $this->application->get(CacheFactory::class);
                
                return new RateLimitMiddleware($this->application->get(ResponseFactoryInterface::class),
                                               $cacheFactory->createCacheFor('gxapi_v3_sessions'));
            });
        
        $this->application->registerShared(AuthenticationMiddleware::class)
            ->addArgument(ResponseFactoryInterface::class)
            ->addArgument(WebRequestAuthenticationService::class);
    }
}