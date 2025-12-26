<?php
/*--------------------------------------------------------------------------------------------------
    ProductModifiersServiceProvider.php 2020-08-04
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\Shop\ProductModifiers;

use Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups\GroupDTOBuilder;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups\GroupDTOBuilderInterface;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOBuilder;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOBuilderInterface;
use Gambio\Shop\ProductModifiers\Database\Core\Factories\Interfaces\PresentationMapperFactoryInterface;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Interfaces\PresentationMapperInterface;
use Gambio\Shop\ProductModifiers\Database\Presentation\PresentationMapperFactory;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class ServiceProvider
 *
 * @package Gambio\Shop\Properties\ProductModifiers\Database
 * @property-read Container $container
 */
class ProductModifiersServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var array
     */
    protected $provides = [
        PresentationMapperInterface::class,
        GroupDTOBuilderInterface::class,
        ModifierDTOBuilderInterface::class
    ];
    
    
    public function boot()
    {
        // TODO: Implement boot() method.
    }
    
    
    /**
     *
     */
    public function register()
    {
        $factory = new PresentationMapperFactory();
        $mappers = $factory->createMapperChain();
        $this->container->share(PresentationMapperInterface::class, $mappers);
        $this->container->share(GroupDTOBuilderInterface::class, GroupDTOBuilder::class);
        $this->container->share(ModifierDTOBuilderInterface::class, ModifierDTOBuilder::class);
    }
}