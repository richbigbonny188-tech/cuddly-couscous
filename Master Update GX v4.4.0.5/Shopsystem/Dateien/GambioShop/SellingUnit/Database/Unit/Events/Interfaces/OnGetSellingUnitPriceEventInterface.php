<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitPriceEventInterface.php 2020-10-26
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces;

use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\PriceBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\AbstractQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SelectedQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use PriceDataInterface;
use ProductDataInterface;

/**
 * Interface OnGetSellingUnitPriceEventInterface
 * @package Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces
 */
interface OnGetSellingUnitPriceEventInterface
{
    /**
     * @return ProductId
     */
    public function productId(): ProductId;
    
    /**
     * @return LanguageId
     */
    public function languageId(): LanguageId;
    
    /**
     * @return ModifierIdentifierCollectionInterface
     */
    public function modifiers(): ModifierIdentifierCollectionInterface;
    
    
    /**
     * @return SellingUnitId
     */
    public function id(): SellingUnitId;
    
    /**
     * @return PriceBuilderInterface
     */
    public function builder(): PriceBuilderInterface;
    
    /**
     * @return PriceDataInterface
     */
    public function xtcPrice(): ?PriceDataInterface;
    
    /**
     * @return ProductDataInterface
     */
    public function product(): ProductDataInterface;
    
    
    /**
     * @return QuantityInterface
     */
    public function quantity(): QuantityInterface;
}