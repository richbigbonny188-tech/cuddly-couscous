<?php
/**
 * SellingUnitServiceProvider.php 2021-01-25
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2021 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

namespace Gambio\Shop\SellingUnit\Database\Unit;

use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnCreateSellingUnitEventInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnCreateSellingUnitEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnSet404HeaderEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Listener\OnCreateSellingUnitListener;
use Gambio\Shop\SellingUnit\Database\Unit\Listener\OnSet404HeaderEventListener;
use Gambio\Shop\SellingUnit\Unit\SellingUnitRepositoryInterface;
use Gambio\Shop\SellingUnit\Unit\Services\Interfaces\SellingUnitReadServiceInterface;
use Gambio\Shop\SellingUnit\Unit\Services\SellingUnitReadService;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class SellingUnitProvider
 * @package Gambio\Shop\SellingUnit\Database\Unit
 * @property-read Container $container
 * @codeCoverageIgnore
 */
class SellingUnitServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    
    /**
     * @var array
     */
    protected $provides = [
        SellingUnitReadServiceInterface::class,
        OnCreateSellingUnitListener::class,
        OnSet404HeaderEventListener::class
    ];
    
    
    /**
     * @inheritDoc
     */
    public function boot()
    {
        /**
         * @var EventListenerProvider $listener
         */
        $listener = $this->container->get(EventListenerProvider::class);
        $listener->attachListener(OnCreateSellingUnitEvent::class, OnCreateSellingUnitListener::class);
        $listener->attachListener(OnSet404HeaderEvent::class, OnSet404HeaderEventListener::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->container->share(SellingUnitReadServiceInterface::class, SellingUnitReadService::class)
                        ->addArgument(SellingUnitRepositoryInterface::class);
        $this->container->share(SellingUnitRepositoryInterface::class, SellingUnitRepository::class)
                        ->addArgument(EventDispatcherInterface::class);
        $this->container->share(OnCreateSellingUnitListener::class)
                        ->addArgument(EventDispatcherInterface::class);
        $this->container->share(OnSet404HeaderEventListener::class);
    }
}