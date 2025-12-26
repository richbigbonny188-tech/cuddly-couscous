<?php
/*------------------------------------------------------------------------------
 SellingUnit.php 2021-01-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit;

use Exception;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\SellingUnit\Database\Image\Events\OnImageCollectionCreateEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetProductInfoEventInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetQuantityGraduationEventInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitPriceEventInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetProductInfoEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetQuantityGraduationEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSelectedQuantityEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitEanEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitModelEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitPriceEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitStockInfoEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitTaxInfoEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitVpeEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitWeightEvent;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetShippingInfoEvent;
use Gambio\Shop\SellingUnit\Images\Builders\CollectionBuilderInterface;
use Gambio\Shop\SellingUnit\Images\Entities\Interfaces\SellingUnitImageCollectionInterface;
use Gambio\Shop\SellingUnit\Images\ValueObjects\SelectedCollectionType;
use Gambio\Shop\SellingUnit\Presentation\SellingUnitPresenter;
use Gambio\Shop\SellingUnit\Presentation\SellingUnitPresenterInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\PriceBuilder;
use Gambio\Shop\SellingUnit\Unit\Builders\ProductInfoBuilder;
use Gambio\Shop\SellingUnit\Unit\Builders\VpeBuilder;
use Gambio\Shop\SellingUnit\Unit\Entities\Interfaces\ProductInfoInterface;
use Gambio\Shop\SellingUnit\Unit\Entities\Price;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\AbstractQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Ean;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Model;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\QuantityGraduation;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SelectedQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitStockInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ShippingInfo;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\TaxInfo;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\TotalAmount;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Vpe;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Weight;
use LegacyDependencyContainer;
use PriceDataInterface;
use product_ORIGIN;
use ProductDataInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use xtcPrice_ORIGIN;

/**
 * Class SellingUnit
 *
 * @package Gambio\Shop\SellingUnit\Unit
 */
class SellingUnit implements SellingUnitInterface
{

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;
    /**
     * @var Ean
     */
    protected $ean;
    /**
     * @var SellingUnitId
     */
    protected $id;
    /**
     * @var SellingUnitImageCollectionInterface
     */
    protected $images;
    /**
     * @var LanguageId
     */
    protected $languageId;
    /**
     * @var Model
     */
    protected $model;
    /**
     * @var ModifierIdentifierCollectionInterface
     */
    protected $modifiers;
    /**
     * @var Price
     */
    protected $price;
    /**
     * @var product_ORIGIN
     */
    protected $product;
    /**
     * @var ProductId
     */
    protected $productId;
    /**
     * @var ProductInfoInterface
     */
    protected $productInfo;
    /**
     * @var QuantityInterface
     */
    protected $requestedQuantity;
    /**
     * @var ShippingInfo
     */
    protected $shipping;
    /**
     * @var TaxInfo
     */
    protected $taxInfo;
    /**
     * @var Weight
     */
    protected $weight = false;
    /**
     * @var xtcPrice_ORIGIN
     */
    protected $xtcPrice;
    /**
     * @var QuantityGraduation
     */
    protected $quantityGraduation;
    /**
     * @var SelectedQuantity
     */
    protected $selectedQuantity;
    /**
     * @var Vpe
     */
    protected $vpe;

    /**
     * @var SellingUnitPresenterInterface
     */
    protected $presenter;
    /**
     * @var SellingUnitStockInterface
     */
    protected $stock;
    
    /**
     * @var TotalAmount
     */
    protected $totalAmount;
    
    
    /**
     * SellingUnit constructor.
     *
     * @param SellingUnitId             $id
     * @param EventDispatcherInterface  $dispatcher
     * @param QuantityInterface|null    $requestedQuantity
     * @param ProductDataInterface|null $product
     * @param PriceDataInterface|null   $xtcPrice
     */
    public function __construct(
        SellingUnitId $id,
        EventDispatcherInterface $dispatcher,
        ?QuantityInterface $requestedQuantity = null,
        ?ProductDataInterface $product = null,
        ?PriceDataInterface $xtcPrice = null
    ) {

        $this->id                = $id;
        $this->dispatcher        = $dispatcher;
        $this->product           = $product;
        $this->xtcPrice          = $xtcPrice;
        $this->requestedQuantity = $requestedQuantity ?? new SelectedQuantity($this->product->getMinOrder());
    }


    /**
     * @return Price
     * @throws Builders\Exceptions\UnfinishedBuildException
     * @throws Exception
     */
    public function price(): Price
    {
        if (!$this->price) {
            /**
             * @var OnGetSellingUnitPriceEventInterface $event
             */
            $event       = $this->dispatcher->dispatch(new OnGetSellingUnitPriceEvent($this->id, $this->product,
                $this->xtcPrice(),
                PriceBuilder::create(),
                $this->selectedQuantity()));
            $this->price = $event->builder()->build();
        }

        return $this->price;
    }

    /**
     * @inheritDoc
     */
    public function stock(): SellingUnitStockInterface
    {
        if ($this->stock === null) {

            $event       = new OnGetSellingUnitStockInfoEvent($this->id, $this->product, $this->requestedQuantity);
            $event       = $this->dispatcher->dispatch($event);
            $this->stock = $event->stock();
        }


        return $this->stock;

    }


    /**
     * @return xtcPrice_ORIGIN
     */
    public function xtcPrice(): PriceDataInterface
    {
        return $this->xtcPrice;
    }


    /**
     * @return AbstractQuantity
     */
    public function selectedQuantity(): AbstractQuantity
    {
        
        if ($this->stock()->availableQuantity() != null && $this->selectedQuantity === null) {

            $event = new OnGetSelectedQuantityEvent($this->id,
                $this->requestedQuantity,
                $this->quantityGraduation(),
                $this->product,
                $this->stock()
            );

            /** @var OnGetSelectedQuantityEvent $event */
            $event                  = $this->dispatcher->dispatch($event);
            $this->selectedQuantity = $event->selectedQuantity();
        }

        return $this->selectedQuantity;
    }


    /**
     * @inheritDoc
     */
    public function quantityGraduation(): QuantityGraduation
    {
        if ($this->quantityGraduation === null) {

            $event = new OnGetQuantityGraduationEvent($this->id,
                $this->product,
                new QuantityGraduation($this->product->getGranularity()));

            /** @var OnGetQuantityGraduationEventInterface $event */
            $event = $this->dispatcher->dispatch($event);

            $this->quantityGraduation = $event->quantityGraduation();
        }

        return $this->quantityGraduation;
    }


    /**
     * @return ProductInfoInterface
     * @throws Builders\Exceptions\UnfinishedBuildException
     */
    public function productInfo(): ProductInfoInterface
    {
        if (!$this->productInfo) {
            /**
             * @var OnGetProductInfoEventInterface $event
             */
            $event = $this->dispatcher->dispatch(new OnGetProductInfoEvent($this->product,
                $this->id->productId(),
                $this->id->modifiers(),
                $this->id->language(),
                ProductInfoBuilder::create(),
                $this->xtcPrice(),
                $this->dispatcher));

            $this->productInfo = $event->builder()->build();
        }

        return $this->productInfo;
    }
    
    
    /**
     * @return ShippingInfo
     * @throws Builders\Exceptions\UnfinishedBuildException
     */
    public function shipping(): ShippingInfo
    {
        if (!$this->shipping) {
            $event = new OnGetShippingInfoEvent($this->id, $this->product, $this->price());
            $this->dispatcher->dispatch($event);
            $this->shipping = $event->builder()->build();
        }

        return $this->shipping;
    }


    /**
     * @return TaxInfo
     */
    public function taxInfo(): TaxInfo
    {
        if (!$this->taxInfo) {

            $event = new OnGetSellingUnitTaxInfoEvent($this->product, $this->xtcPrice);
            $this->dispatcher->dispatch($event);
            $this->taxInfo = $event->taxInfo();
        }

        return $this->taxInfo;
    }


    /**
     * @return Weight
     */
    public function weight(): ?Weight
    {
        if ($this->weight === false) {
    
            $event = new OnGetSellingUnitWeightEvent($this->id, $this->product);
            $this->dispatcher->dispatch($event);
            $this->weight = $event->builder()->build();
        }

        return $this->weight;
    }


    /**
     * @return SellingUnitId
     */
    public function id(): SellingUnitId
    {
        return $this->id;
    }


    /**
     * @inheritDoc
     */
    public function images(): SellingUnitImageCollectionInterface
    {
        if (!$this->images) {
            /**
             * @var OnImageCollectionCreateEvent $event
             */
            $collectionBuilder = $this->imageCollectionBuilder();
            $event             = $this->dispatcher->dispatch(new OnImageCollectionCreateEvent($this->id,
                $collectionBuilder));
            $this->images      = $event->builder()->build();
        }

        return $this->images;
    }


    /**
     * @inheritDoc
     */
    public function model(): Model
    {
        if ($this->model === null) {
            $event = new OnGetSellingUnitModelEvent($this->product, $this->id());
            $this->dispatcher->dispatch($event);
            $this->model = $event->builder()->build();
        }

        return $this->model;
    }


    /**
     * @inheritDoc
     */
    public function ean(): Ean
    {
        if ($this->ean === null) {
            $event = new OnGetSellingUnitEanEvent($this->product, $this->id());
            $this->dispatcher->dispatch($event);
            $this->ean = $event->builder()->build();
        }

        return $this->ean;
    }

    /**
     * @inheritDoc
     */
    public function vpe(): ?Vpe
    {
        if ($this->vpe === null) {
            $event     = new OnGetSellingUnitVpeEvent($this->id, $this->product);
            $event     = $this->dispatcher->dispatch($event);
            $this->vpe = $event->vpe();
        }

        return $this->vpe;
    }
    
    /**
     * @inheritDoc
     */
    public function totalAmount(): TotalAmount
    {
        if ($this->totalAmount === null) {
            $this->totalAmount = new TotalAmount($this->selectedQuantity()->value() * $this->price()->pricePlain()->value());
        }
    
        return $this->totalAmount;
    }


    /**
     * @return SellingUnitPresenterInterface
     */
    public function presenter(): SellingUnitPresenterInterface
    {
        if ($this->presenter === null) {

            $this->presenter = new SellingUnitPresenter($this, $this->dispatcher);
        }

        return $this->presenter;
    }


    /**
     * @return CollectionBuilderInterface
     */
    protected function imageCollectionBuilder(): CollectionBuilderInterface
    {
        $legacyDependencyContainer = LegacyDependencyContainer::getInstance();
        $selectedCollectionType    = $legacyDependencyContainer->get(SelectedCollectionType::class);

        return $selectedCollectionType->value();
    }


    /**
     * @return string[]
     */
    public function __sleep(): array
    {
        return [
            'id',
            'images',
            'languageId',
            'model',
            'modifiers',
            'price',
            'product',
            'productId',
            'productInfo',
            'shippingInfo',
            'taxInfo',
            'weight',
            'xtcPrice',
            'quantityGraduation',
            'requestedQuantity',
            'selectedQuantity',
            'vpe',
        ];
    }


    public function __wakeup(): void
    {
        $this->presenter     = $this->stock = null;
        $dependencyContainer = LegacyDependencyContainer::getInstance();
        $this->dispatcher    = $dependencyContainer->get(EventDispatcherInterface::class);
    }
}
