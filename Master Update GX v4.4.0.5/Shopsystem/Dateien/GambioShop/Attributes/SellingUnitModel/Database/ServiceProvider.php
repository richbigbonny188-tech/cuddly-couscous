<?php
/*--------------------------------------------------------------------
 ServiceProvider.php 2020-07-29
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitModel\Database;

use Doctrine\DBAL\Connection;
use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Listener\OnGetSellingUnitModelEventListener;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Repository\Readers\Reader;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Repository\Readers\ReaderInterface;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Repository\Repository;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Repository\RepositoryInterface;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Service\ReadService;
use Gambio\Shop\Attributes\SellingUnitModel\Database\Service\ReadServiceInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitModelEvent;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package Gambio\Shop\Attributes\SellingUnitModel\Database
 * @property-read Container $container
 * @codeCoverageIgnore
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    protected $provides = [
        OnGetSellingUnitModelEventListener::class
    ];
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->container->share(OnGetSellingUnitModelEventListener::class)
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
    public function boot(): void
    {
        /** @var EventListenerProvider $listenerProvider */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnGetSellingUnitModelEvent::class, OnGetSellingUnitModelEventListener::class);
    }
}