<?php
/*------------------------------------------------------------------------------
 ServiceProvider.php 2020-12-21
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Database;

use Doctrine\DBAL\Connection;
use Gambio\Core\Event\EventListenerProvider;
use Gambio\Core\Language\TextManager;
use Gambio\Shop\Properties\Database\Criterias\CheckStockBeforeShoppingCartCriteria;
use Gambio\Shop\Properties\Database\Criterias\CheckStockCriteria;
use Gambio\Shop\Properties\Database\Listeners\OnGetSellingUnitAvailableQuantityListener;
use Gambio\Shop\Properties\Database\Listeners\OnGetSellingUnitEanEventListener;
use Gambio\Shop\Properties\Database\Listeners\OnGetSellingUnitModelEventListener;
use Gambio\Shop\Properties\Database\Listeners\OnGetSellingUnitPriceEventListener;
use Gambio\Shop\Properties\Database\Listeners\OnGetSellingUnitVpeEventListener;
use Gambio\Shop\Properties\Database\Listeners\OnGetSellingUnitWeightEventListener;
use Gambio\Shop\Properties\Database\Listeners\OnGetShippingInfoEventListener;
use Gambio\Shop\Properties\Database\Readers\CachedPropertyReader;
use Gambio\Shop\Properties\Database\Readers\Interfaces\PropertyReaderInterface;
use Gambio\Shop\Properties\Database\Repositories\Interfaces\PropertyReadRepositoryInterface;
use Gambio\Shop\Properties\Database\Repositories\PropertyReadRepository;
use Gambio\Shop\Properties\Database\Services\Interfaces\PropertiesReaderServiceInterface;
use Gambio\Shop\Properties\Database\Services\Interfaces\PropertyQuantityReadServiceInterface;
use Gambio\Shop\Properties\Database\Services\PropertiesReaderService;
use Gambio\Shop\Properties\Database\Services\PropertyQuantityReadService;
use Gambio\Shop\Properties\Properties\Builders\CombinationBuilder;
use Gambio\Shop\Properties\Properties\Builders\CombinationBuilderInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitAvailableQuantityEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitEanEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitModelEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitPriceEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitVpeEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitWeightEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetShippingInfoEvent;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package            Gambio\Shop\Properties\Database
 * @codeCoverageIgnore providers dont need to be tested
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var array
     */
    protected $provides = [
        PropertiesReaderServiceInterface::class,
        CheckStockBeforeShoppingCartCriteria::class,
        OnGetSellingUnitAvailableQuantityListener::class,
        OnGetSellingUnitWeightEventListener::class,
        OnGetSellingUnitEanEventListener::class,
        OnGetSellingUnitPriceEventListener::class,
        OnGetSellingUnitModelEventListener::class,
        OnGetSellingUnitVpeEventListener::class,
        OnGetShippingInfoEventListener::class,
        CheckStockCriteria::class,
        PropertyReadRepositoryInterface::class
    ];
    
    
    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->container->share(CheckStockBeforeShoppingCartCriteria::class)
            ->addArgument(defined('STOCK_ALLOW_CHECKOUT') ? STOCK_ALLOW_CHECKOUT === 'true' : false)
            ->addArgument(defined('STOCK_CHECK') ? STOCK_CHECK === 'true' : false)
            ->addArgument(defined('CHECK_STOCK_BEFORE_SHOPPING_CART') ? CHECK_STOCK_BEFORE_SHOPPING_CART
                                                                        === 'true' : false)
            ->addArgument(defined('ATTRIBUTE_STOCK_CHECK') ? ATTRIBUTE_STOCK_CHECK === 'true' : false);
        
        $this->container->share(CheckStockCriteria::class)
            ->addArgument(defined('STOCK_CHECK') ? STOCK_CHECK === 'true' : false)
            ->addArgument(defined('ATTRIBUTE_STOCK_CHECK') ? ATTRIBUTE_STOCK_CHECK === 'true' : false)
            ->addArgument(defined('STOCK_ALLOW_CHECKOUT') ? STOCK_ALLOW_CHECKOUT === 'true' : false);
        
        $this->container->share(PropertyQuantityReadServiceInterface::class, PropertyQuantityReadService::class)
            ->addArgument(PropertyReadRepositoryInterface::class)
            ->addArgument(CheckStockCriteria::class)
            ->addArgument(TextManager::class);
        
        $this->container->share(OnGetSellingUnitAvailableQuantityListener::class)
            ->addArgument(PropertyQuantityReadServiceInterface::class, PropertyQuantityReadService::class);
        
        $this->container->share(CombinationBuilderInterface::class, CombinationBuilder::class);
        
        $this->container->share(PropertiesReaderServiceInterface::class, PropertiesReaderService::class)
            ->addArgument(PropertyReadRepositoryInterface::class);
        
        $this->container->share(OnGetSellingUnitEanEventListener::class)
            ->addArgument(PropertiesReaderServiceInterface::class);
        
        $this->container->share(OnGetShippingInfoEventListener::class)
            ->addArgument(PropertiesReaderServiceInterface::class);
        
        $this->container->share(PropertyReadRepositoryInterface::class, PropertyReadRepository::class)
            ->addArgument(PropertyReaderInterface::class);
        
        $this->container->share(PropertyReaderInterface::class, CachedPropertyReader::class)
            ->addArgument(Connection::class)
            ->addArgument(CombinationBuilderInterface::class)
            ->addArgument(CheckStockBeforeShoppingCartCriteria::class)
            ->addArgument(CheckStockCriteria::class);
        
        $this->container->share(PropertiesReaderServiceInterface::class, PropertiesReaderService::class);
        $this->container->share(PropertiesReaderService::class);
        $this->container->share(OnGetSellingUnitWeightEventListener::class)
            ->addArgument(PropertiesReaderServiceInterface::class);
        
        $this->container->share(OnGetSellingUnitPriceEventListener::class)
            ->addArgument(PropertiesReaderServiceInterface::class);
        
        $this->container->share(OnGetSellingUnitModelEventListener::class)
            ->addArgument(PropertiesReaderServiceInterface::class)
            ->addArgument($this->appendPropertiesModel());
        
        $this->container->share(OnGetSellingUnitVpeEventListener::class)
            ->addArgument(PropertiesReaderServiceInterface::class);
        
    }
    
    
    protected function appendPropertiesModel(): bool
    {
        return defined('APPEND_PROPERTIES_MODEL') ? APPEND_PROPERTIES_MODEL === 'true' : false;
    }
    
    
    public function boot()
    {
        /** @var EventListenerProvider $listenerProvider */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnGetSellingUnitAvailableQuantityEvent::class,
                                          OnGetSellingUnitAvailableQuantityListener::class);
        $listenerProvider->attachListener(OnGetSellingUnitWeightEvent::class,
                                          OnGetSellingUnitWeightEventListener::class);
        $listenerProvider->attachListener(OnGetSellingUnitEanEvent::class, OnGetSellingUnitEanEventListener::class);
        $listenerProvider->attachListener(OnGetSellingUnitPriceEvent::class, OnGetSellingUnitPriceEventListener::class);
        $listenerProvider->attachListener(OnGetSellingUnitModelEvent::class, OnGetSellingUnitModelEventListener::class);
        $listenerProvider->attachListener(OnGetSellingUnitVpeEvent::class, OnGetSellingUnitVpeEventListener::class);
        $listenerProvider->attachListener(OnGetShippingInfoEvent::class, OnGetShippingInfoEventListener::class);
    }
}