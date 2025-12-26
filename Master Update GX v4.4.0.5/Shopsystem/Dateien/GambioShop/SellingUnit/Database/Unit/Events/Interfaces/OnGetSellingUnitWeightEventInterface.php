<?php
/*--------------------------------------------------------------------
 OnGetSellingUnitWeightEventInterface.php 2021-01-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

namespace Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces;

use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\WeightBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Weight;
use ProductDataInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Interface OnGetSellingUnitWeightEventInterface
 * @package Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces
 */
interface OnGetSellingUnitWeightEventInterface extends StoppableEventInterface
{
    /**
     * @return SellingUnitId
     */
    public function id(): SellingUnitId;
    
    
    /**
     * @return ProductDataInterface
     */
    public function product(): ProductDataInterface;
    
    
    /**
     * @return WeightBuilderInterface
     */
    public function builder(): WeightBuilderInterface;
    
    /**
     * @return void
     */
    public function stopPropagation(): void ;
}