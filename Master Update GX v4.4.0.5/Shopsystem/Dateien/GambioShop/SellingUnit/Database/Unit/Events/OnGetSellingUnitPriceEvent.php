<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitPriceEvent.php 2020-10-26
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\SellingUnit\Database\Unit\Events;

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\SellingUnit\Core\Events\SellingUnitEventTrait;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitPriceEventInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\PriceBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\Entities\Price;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\AbstractQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SelectedQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use PriceDataInterface;
use ProductDataInterface;
use xtcPrice_ORIGIN;

/**
 * Class OnGetSellingUnitPriceEvent
 * @package Gambio\Shop\SellingUnit\Database\Unit\Events
 */
class OnGetSellingUnitPriceEvent implements OnGetSellingUnitPriceEventInterface
{
    use SellingUnitEventTrait;
    
    /**
     * @var Price
     */
    protected $price;
    
    /**
     * @var PriceDataInterface
     */
    protected $xtcPrice;
    
    /**
     * @var PriceBuilderInterface
     */
    protected $priceBuilder;
    
    /**
     * @var ProductDataInterface
     */
    protected $product;
    
    /**
     * @var SelectedQuantity
     */
    protected $quantity;
    
    /**
     * @var ProductId
     */
    protected $productId;
    
    /**
     * @var LanguageId
     */
    protected $languageId;
    
    /**
     * @var ModifierIdentifierCollectionInterface
     */
    protected $modifiers;
    
    
    /**
     * OnGetSellingUnitPriceEvent constructor.
     *
     * @param SellingUnitId         $id
     * @param ProductDataInterface  $product
     * @param PriceDataInterface    $xtcPrice
     * @param PriceBuilderInterface $priceBuilder
     * @param QuantityInterface     $quantity
     */
    public function __construct( SellingUnitId $id,
        ProductDataInterface $product,
        PriceDataInterface $xtcPrice,
        PriceBuilderInterface $priceBuilder,
        QuantityInterface $quantity
    ) {
        $this->product      = $product;
        $this->id           = $id;
        $this->xtcPrice     = $xtcPrice;
        $this->priceBuilder = $priceBuilder;
        $this->quantity     = $quantity;
    }
    
    
    /**
     * @return ProductDataInterface
     */
    public function product(): ProductDataInterface
    {
        return $this->product;
    }
    
    
    /**
     * @inheritDoc
     */
    public function builder(): PriceBuilderInterface
    {
        return $this->priceBuilder;
    }
    
    
    /**
     * @inheritDoc
     */
    public function productId(): ProductId
    {
        return $this->id->productId();
    }
    
    
    /**
     * @inheritDoc
     */
    public function languageId(): LanguageId
    {
        return $this->id->language();
    }
    
    
    /**
     * @inheritDoc
     */
    public function modifiers(): ModifierIdentifierCollectionInterface
    {
        return $this->id->modifiers();
    }
    
    /**
     * @inheritDoc
     */
    public function id(): SellingUnitId
    {
        return $this->id;
    }


    /**
     * @inheritDoc
     */
    public function quantity(): QuantityInterface
    {
        return $this->quantity;
    }
}