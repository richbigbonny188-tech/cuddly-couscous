<?php
/*--------------------------------------------------------------------
 ServiceProvider.php 2020-08-05
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitPrice;

use Doctrine\DBAL\Connection;
use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\Attributes\SellingUnitPrice\Listener\OnGetSellingUnitPriceEventListener;
use Gambio\Shop\Attributes\SellingUnitPrice\Repository\Readers\Reader;
use Gambio\Shop\Attributes\SellingUnitPrice\Repository\Readers\ReaderInterface;
use Gambio\Shop\Attributes\SellingUnitPrice\Repository\Repository;
use Gambio\Shop\Attributes\SellingUnitPrice\Repository\RepositoryInterface;
use Gambio\Shop\Attributes\SellingUnitPrice\Service\ReadService;
use Gambio\Shop\Attributes\SellingUnitPrice\Service\ReadServiceInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitPriceEventInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitPriceEvent;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package Gambio\Shop\Attributes\SellingUnitPrice
 * @property-read Container $container
 * @codeCoverageIgnore
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    protected $provides = [
        OnGetSellingUnitPriceEventListener::class
    ];
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->container->share(OnGetSellingUnitPriceEventListener::class)
            ->addArgument(ReadServiceInterface::class);
        
        $this->container->share(ReadServiceInterface::class, ReadService::class)
            ->addArgument(RepositoryInterface::class);
        
        $this->container->share(RepositoryInterface::class, Repository::class)
            ->addArgument(ReaderInterface::class);
        
        $this->container->share(ReaderInterface::class, Reader::class)
            ->addArgument(Connection::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function boot()
    {
        /**
         * @var EventListenerProvider $listenerProvider
         */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnGetSellingUnitPriceEvent::class, OnGetSellingUnitPriceEventListener::class);
    }
}