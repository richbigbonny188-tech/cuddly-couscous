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

declare(strict_types=1);

namespace Gambio\Shop\Properties\SellingUnitImages\Database;

use Doctrine\DBAL\Connection;
use Gambio\Core\Event\EventListenerProvider;
use Gambio\Core\Filesystem\Interfaces\Filesystem;
use Gambio\Core\Images\ValueObjects\ProductGalleryImages;
use Gambio\Core\Images\ValueObjects\ProductInfoImages;
use Gambio\Core\Images\ValueObjects\ProductOriginalImages;
use Gambio\Core\Images\ValueObjects\ProductPopUpImages;
use Gambio\Core\Images\ValueObjects\ProductThumbnailImages;
use Gambio\Shop\Properties\SellingUnitImages\Database\Listener\OnImageCollectionCreateEventListener;
use Gambio\Shop\Properties\SellingUnitImages\Database\Listener\OnMainImageCreateEventListener;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\Factories\ImageFactory;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\Factories\ImageFactoryInterface;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\Helpers\CombisIdIdentifier;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\Helpers\CombisIdIdentifierInterface;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\Readers\Reader;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\Readers\ReaderInterface;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\Repository;
use Gambio\Shop\Properties\SellingUnitImages\Database\Repository\RepositoryInterface;
use Gambio\Shop\Properties\SellingUnitImages\Database\Service\ReadService;
use Gambio\Shop\Properties\SellingUnitImages\Database\Service\ReadServiceInterface;
use Gambio\Shop\SellingUnit\Database\Configurations\ShopPaths;
use Gambio\Shop\SellingUnit\Database\Image\Events\OnImageCollectionCreateEvent;
use Gambio\Shop\SellingUnit\Database\Images\Events\OnMainImageCreateEvent;
use GmConfigurationServiceInterface;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use PropertiesDataAgent;
use StaticGXCoreLoader;

/**
 * Class ServiceProvider
 * @package Gambio\Shop\Properties\SellingUnitImages\Database
 *
 * @property-read Container $container
 * @codeCoverageIgnore service providers don't need to be tested
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var array
     */
    protected $provides = [
        OnMainImageCreateEventListener::class,
        OnImageCollectionCreateEventListener::class
    ];
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        /** @var GmConfigurationServiceInterface $gxConfigurationService */
        $gxConfigurationService = StaticGXCoreLoader::getService('GmConfiguration');
        $configuration          = $gxConfigurationService->getConfigurationByKey('DISPLAY_OF_PROPERTY_COMBINATION_SELECTION');

        $this->container->share(OnMainImageCreateEventListener::class)->addArgument(ReadServiceInterface::class);
        $this->container->share(OnImageCollectionCreateEventListener::class)
                        ->addArgument(ReadServiceInterface::class)
                        ->addArgument($configuration->value());
        
        $this->container->share(ReadServiceInterface::class, ReadService::class)
                        ->addArgument(RepositoryInterface::class);
        
        $this->container->share(RepositoryInterface::class, Repository::class)
                        ->addArgument(ReaderInterface::class)
                        ->addArgument(CombisIdIdentifierInterface::class)
                        ->addArgument(ImageFactoryInterface::class);
        
        $this->container->share(ReaderInterface::class, Reader::class)
                         ->addArgument(Connection::class)
                         ->addArgument(Filesystem::class)
                         ->addArgument(ProductOriginalImages::class)
                         ->addArgument(ProductInfoImages::class)
                         ->addArgument(ProductPopUpImages::class)
                         ->addArgument(ProductThumbnailImages::class)
                         ->addArgument(ProductGalleryImages::class);
        
        $this->container->share(CombisIdIdentifierInterface::class, CombisIdIdentifier::class)
                        ->addArgument(PropertiesDataAgent::class);
        $this->container->share(PropertiesDataAgent::class);
        
        $this->container->share(ImageFactoryInterface::class, ImageFactory::class)->addArgument(ShopPaths::class);
        
        $pathBase = '';
        $webBase  = '';
        try {
            
            $pathBase = $this->container->get('path.base');
            $webBase  = $this->container->get('web.base');
        } catch (\Exception $e) {
            /**
             * TODO: This try/catch must be removed as soon the literals are exported
             */
        }
        
        $this->container->share(ShopPaths::class)->addArgument($pathBase)->addArgument($webBase);
    }
    
    
    /**
     * @inheritDoc
     */
    public function boot()
    {
        /** @var EventListenerProvider $listenerProvider */
        $listenerProvider = $this->container->get(EventListenerProvider::class);
        $listenerProvider->attachListener(OnMainImageCreateEvent::class, OnMainImageCreateEventListener::class);
        $listenerProvider->attachListener(OnImageCollectionCreateEvent::class, OnImageCollectionCreateEventListener::class);
    }
}