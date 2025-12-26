<?php
/*--------------------------------------------------------------------------------------------------
    ServiceProvider.php 2021-01-25
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\Attributes\SellingUnit\Database;

use Doctrine\DBAL\Connection;
use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\Attributes\SellingUnit\Database\Listener\OnGetSellingUnitVpeEventListener;
use Gambio\Shop\Attributes\SellingUnit\Database\Listener\OnSellingUnitIdCreateListener;
use Gambio\Shop\Attributes\SellingUnit\Database\Repository\Readers\Reader;
use Gambio\Shop\Attributes\SellingUnit\Database\Repository\Readers\ReaderInterface;
use Gambio\Shop\Attributes\SellingUnit\Database\Repository\Repository;
use Gambio\Shop\Attributes\SellingUnit\Database\Repository\RepositoryInterface;
use Gambio\Shop\Attributes\SellingUnit\Database\Service\ReadService;
use Gambio\Shop\Attributes\SellingUnit\Database\Service\ReadServiceInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitVpeEvent;
use Gambio\Shop\SellingUnit\Unit\Events\OnSellingUnitIdCreateEvent;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class ServiceProvider
 * @package Gambio\Shop\Attributes\SellingUnit\Database
 * @property-read Container $container
 * @codeCoverageIgnore
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    
    /**
     * @var array
     */
    protected $provides = [
        OnSellingUnitIdCreateListener::class,
        OnGetSellingUnitVpeEventListener::class,
        ReadServiceInterface::class
    ];
    
    
    /**
     * @inheritDoc
     */
    public function boot()
    {
        /** @var EventListenerProvider $listenerProvider */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnSellingUnitIdCreateEvent::class, OnSellingUnitIdCreateListener::class);
        $listenerProvider->attachListener(OnGetSellingUnitVpeEvent::class, OnGetSellingUnitVpeEventListener::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->container->share(OnSellingUnitIdCreateListener::class)
            ->addArgument(ReadServiceInterface::class)
            ->addArgument(EventDispatcherInterface::class);
        
        $this->container->share(OnGetSellingUnitVpeEventListener::class)->addArgument(ReadServiceInterface::class);
        
        $this->container->share(ReadServiceInterface::class, ReadService::class)
            ->addArgument(RepositoryInterface::class);
        
        $this->container->share(RepositoryInterface::class, Repository::class)->addArgument(ReaderInterface::class);
        
        $this->container->share(ReaderInterface::class, Reader::class)->addArgument(Connection::class);
    }
}