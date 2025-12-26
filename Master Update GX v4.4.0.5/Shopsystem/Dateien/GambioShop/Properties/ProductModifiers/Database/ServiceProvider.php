<?php
/*--------------------------------------------------------------------------------------------------
    ServiceProvider.php 2020-11-27
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\Properties\ProductModifiers\Database;

use Doctrine\DBAL\Connection;
use Gambio\Core\Event\EventListenerProvider;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups\GroupDTOBuilder;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups\GroupDTOBuilderInterface;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOBuilder;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOBuilderInterface;
use Gambio\Shop\ProductModifiers\Database\Core\Events\OnCreateGroupsForSellingUnitEvent;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Interfaces\PresentationMapperInterface;
use Gambio\Shop\ProductModifiers\Modifiers\Events\OnModifierIdCreateEvent;
use Gambio\Shop\Properties\ProductModifiers\Database\Builders\PropertyGroupBuilder;
use Gambio\Shop\Properties\ProductModifiers\Database\Builders\PropertyModifierBuilder;
use Gambio\Shop\Properties\ProductModifiers\Database\Listeners\OnCreateGroupsForSellingUnitListener;
use Gambio\Shop\Properties\ProductModifiers\Database\Listeners\OnModifierIdCreateListener;
use Gambio\Shop\Properties\ProductModifiers\Database\Readers\CachedPropertyModifierReader;
use Gambio\Shop\Properties\ProductModifiers\Database\Readers\PropertyGroupReader;
use Gambio\Shop\Properties\ProductModifiers\Database\Readers\PropertyGroupReaderInterface;
use Gambio\Shop\Properties\ProductModifiers\Database\Readers\PropertyModifierReader;
use Gambio\Shop\Properties\ProductModifiers\Database\Readers\PropertyModifierReaderInterface;
use Gambio\Shop\Properties\ProductModifiers\Database\Repository\PropertyGroupRepository;
use Gambio\Shop\Properties\ProductModifiers\Database\Repository\PropertyGroupRepositoryInterface;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 *
 * @package Gambio\Shop\Properties\ProductModifiers\Database
 * @property-read Container
 * @codeCoverageIgnore service providers don't need to be tested
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var array
     */
    protected $provides = [
        OnModifierIdCreateListener::class,
        OnCreateGroupsForSellingUnitListener::class
    ];
    
    
    /**
     * @inheritDoc
     */
    public function boot()
    {
        /** @var EventListenerProvider $listenerProvider */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnModifierIdCreateEvent::class, OnModifierIdCreateListener::class);
        $listenerProvider->attachListener(OnCreateGroupsForSellingUnitEvent::class,
                                          OnCreateGroupsForSellingUnitListener::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function register()
    {
        
        $stockCheck              = defined('STOCK_CHECK') ? STOCK_CHECK === 'true' : false;
        $attributeStockCheck     = defined('ATTRIBUTE_STOCK_CHECK') ? ATTRIBUTE_STOCK_CHECK === 'true' : false;
        $stockAllowCheckout      = defined('STOCK_ALLOW_CHECKOUT') ? STOCK_ALLOW_CHECKOUT === 'true' : false;
        $checkStockBeforeShoppingCart = defined('CHECK_STOCK_BEFORE_SHOPPING_CART') ? CHECK_STOCK_BEFORE_SHOPPING_CART
                                                                                      === 'true' : false;
        $this->container->share(PropertyModifierReader::class)
            ->addArgument(Connection::class)
            ->addArgument(ModifierDTOBuilderInterface::class)
            ->addArgument($stockCheck)
            ->addArgument($attributeStockCheck)
            ->addArgument($stockAllowCheckout)
            ->addArgument($checkStockBeforeShoppingCart);
        
        $this->container->share(OnModifierIdCreateListener::class);
        
        $this->container->share(PropertyModifierReaderInterface::class, CachedPropertyModifierReader::class)
            ->addArgument(PropertyModifierReader::class);
        
        $this->container->share(ModifierDTOBuilderInterface::class, ModifierDTOBuilder::class);
        $this->container->share(PropertyGroupBuilder::class);
        $this->container->share(PropertyModifierBuilder::class);
        
        $this->container->share(OnCreateGroupsForSellingUnitListener::class)
            ->addArgument(PropertyGroupRepositoryInterface::class);
        
        $this->container->share(GroupDTOBuilderInterface::class, GroupDTOBuilder::class);
        
        $this->container->share(PropertyGroupReaderInterface::class, PropertyGroupReader::class)
            ->addArgument(Connection::class)
            ->addArgument(GroupDTOBuilderInterface::class);
        
        $this->container->share(PropertyGroupRepositoryInterface::class, PropertyGroupRepository::class)
            ->addArgument(PropertyGroupReaderInterface::class)
            ->addArgument(PropertyModifierReaderInterface::class)
            ->addArgument(PropertyGroupBuilder::class)
            ->addArgument(PropertyModifierBuilder::class)
            ->addArgument(PresentationMapperInterface::class);
    }
}