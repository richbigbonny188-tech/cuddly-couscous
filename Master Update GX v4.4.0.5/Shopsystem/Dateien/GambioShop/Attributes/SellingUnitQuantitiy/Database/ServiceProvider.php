<?php
/*--------------------------------------------------------------------------------------------------
    ServiceProvider.php 2021-01-07
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\Attributes\SellingUnitQuantitiy\Database;

use Doctrine\DBAL\Connection;
use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Listeners\OnGetSellingUnitAvailableQuantityListener;
use Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository\Reader\Reader;
use Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository\Reader\ReaderInterface;
use Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository\Repository;
use Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Repository\RepositoryInterface;
use Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Services\ReaderService;
use Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Services\ReaderServiceInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitAvailableQuantityEvent;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * @property  Container container
 * @codeCoverageIgnore
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var array
     */
    protected $provides = [
        OnGetSellingUnitAvailableQuantityListener::class
    ];
    
    
    /**
     * @inheritDoc
     */
    public function boot()
    {
        /** @var EventListenerProvider $listenerProvider */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnGetSellingUnitAvailableQuantityEvent::class,
                                          OnGetSellingUnitAvailableQuantityListener::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->container->share(OnGetSellingUnitAvailableQuantityListener::class)
            ->addArgument(ReaderServiceInterface::class)
            ->addArgument(defined('STOCK_ALLOW_CHECKOUT') ? STOCK_ALLOW_CHECKOUT === 'true' : false)
            ->addArgument(defined('GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT') ? GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT : '')
            ->addArgument(defined('GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT') ? GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT : '')
            ->addArgument(defined('GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT') ? GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT : '');
        
        $this->container->share(ReaderServiceInterface::class, ReaderService::class)
            ->addArgument(RepositoryInterface::class)
            ->addArgument(defined('STOCK_CHECK') ? STOCK_CHECK === 'true' : false)
            ->addArgument(defined('ATTRIBUTE_STOCK_CHECK') ? ATTRIBUTE_STOCK_CHECK === 'true' : false)
            ->addArgument(defined('DOWNLOAD_STOCK_CHECK') ? DOWNLOAD_STOCK_CHECK === 'true' : false);
        $this->container->share(RepositoryInterface::class, Repository::class)->addArgument(ReaderInterface::class);
        $this->container->share(ReaderInterface::class, Reader::class)->addArgument(Connection::class);
    }
}