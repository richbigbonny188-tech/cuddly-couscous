<?php
/*--------------------------------------------------------------------
 ServiceProvider.php 2020-11-27
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\Representation\Id;

use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\Properties\Database\Services\Interfaces\PropertiesReaderServiceInterface;
use Gambio\Shop\Properties\Representation\Id\Listener\OnPresentSellingUnitIdEventListener;
use Gambio\Shop\SellingUnit\Presentation\Events\OnPresentSellingUnitIdEvent;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package Gambio\Shop\SellingUnit\Properties\Representation\Id
 * @property-read Container $container
 * @codeCoverageIgnore service providers don't need to be tested
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    protected $provides = [
        OnPresentSellingUnitIdEventListener::class
    ];
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->container->share(OnPresentSellingUnitIdEventListener::class)->addArgument(PropertiesReaderServiceInterface::class);
        
        //$this->container->share(PropertiesReaderServiceInterface::class, PropertiesReaderService::class);
        //$this->container->share()
    }
    
    
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        /** @var EventListenerProvider $listenerProvider */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnPresentSellingUnitIdEvent::class, OnPresentSellingUnitIdEventListener::class);
    }
}