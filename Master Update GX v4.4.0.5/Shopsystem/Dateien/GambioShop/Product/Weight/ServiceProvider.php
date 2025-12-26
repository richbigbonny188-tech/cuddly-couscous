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

namespace Gambio\Shop\Product\Weight;

use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\Product\Weight\Listener\OnGetProductInfoEventListener;
use Gambio\Shop\Product\Weight\Listener\OnGetSellingUnitWeightEventListener;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitWeightEventInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetProductInfoEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetProductWeightEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitWeightEvent;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package Gambio\Shop\Product\Weight
 * @property-read Container $container
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        OnGetSellingUnitWeightEventListener::class,
        OnGetProductInfoEventListener::class
    ];
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->container->share(OnGetSellingUnitWeightEventListener::class);
        $this->container->share(OnGetProductInfoEventListener::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        /**
         * @var EventListenerProvider $listenerProvider
         */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnGetSellingUnitWeightEvent::class, OnGetSellingUnitWeightEventListener::class);
        $listenerProvider->attachListener(OnGetProductInfoEvent::class, OnGetProductInfoEventListener::class);
    }
}