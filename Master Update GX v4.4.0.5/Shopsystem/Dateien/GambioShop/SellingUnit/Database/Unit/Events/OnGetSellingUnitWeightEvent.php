<?php
/*--------------------------------------------------------------------
 OnGetSellingUnitWeightEvent.php 2021-01-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

namespace Gambio\Shop\SellingUnit\Database\Unit\Events;

use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitWeightEventInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\WeightBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\WeightBuilder;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use ProductDataInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class OnGetSellingUnitWeightEvent
 * @package Gambio\Shop\SellingUnit\Database\Unit\Events
 */
class OnGetSellingUnitWeightEvent implements OnGetSellingUnitWeightEventInterface
{
    /**
     * @var ProductId
     */
    protected $id;
    
    /**
     * @var ProductDataInterface
     */
    protected $product;
    
    /**
     * @var WeightBuilderInterface
     */
    protected $builder;

    /**
     * @var bool
     */
    protected $isPropagationStopped = false;
    
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;
    
    
    /**
     * OnGetSellingUnitWeightEvent constructor.
     *
     * @param SellingUnitId            $id
     * @param ProductDataInterface     $product
     */
    public function __construct(
        SellingUnitId $id,
        ProductDataInterface $product
    ) {
        $this->id         = $id;
        $this->product    = $product;
        $this->builder    = new WeightBuilder();
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
    public function product(): ProductDataInterface
    {
        return $this->product;
    }
    
    
    /**
     * @inheritDoc
     */
    public function builder(): WeightBuilderInterface
    {
        return $this->builder;
    }
    
    
    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }
    
    
    public function stopPropagation(): void
    {
        $this->isPropagationStopped = true;
    }
}