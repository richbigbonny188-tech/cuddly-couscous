<?php
/*------------------------------------------------------------------------------
 CombinationBuilder.php 2020-11-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Properties\Builders;

use Gambio\Shop\Properties\Properties\Entities\CheapestCombination;
use Gambio\Shop\Properties\Properties\Entities\Combination;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationEan;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationId;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationModel;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationOrder;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationQuantity;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationSurcharge;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationWeight;
use Gambio\Shop\Properties\Properties\ValueObjects\ShippingStatus;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Vpe;

/**
 * Class CombinationBuilder
 * @package Gambio\Shop\Properties\Properties\Builders
 */
class CombinationBuilder implements CombinationBuilderInterface
{
    
    /**
     * @var CombinationId
     */
    protected $id;
    /**
     * @var CombinationOrder
     */
    protected $order;
    /**
     * @var CombinationModel
     */
    protected $model;
    /**
     * @var CombinationEan
     */
    protected $ean;
    /**
     * @var CombinationQuantity
     */
    protected $quantity;
    /**
     * @var ?CombinationSurcharge
     */
    protected $surcharge;
    /**
     * @var Vpe
     */
    protected $vpe;
    /**
     * @var CombinationWeight
     */
    protected $weight;
    /**
     * @var ShippingStatus|null
     */
    protected $shippingStatus;
    
    
    /**
     * @inheritDoc
     */
    public function withId(CombinationId $id): CombinationBuilderInterface
    {
        $this->id = $id;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withOrder(CombinationOrder $order): CombinationBuilderInterface
    {
        $this->order = $order;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withModel(CombinationModel $model): CombinationBuilderInterface
    {
        $this->model = $model;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withEan(CombinationEan $ean): CombinationBuilderInterface
    {
        $this->ean = $ean;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withQuantity(CombinationQuantity $quantity): CombinationBuilderInterface
    {
        $this->quantity = $quantity;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withSurcharge(?CombinationSurcharge $surcharge): CombinationBuilderInterface
    {
        $this->surcharge = $surcharge;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withVpe(?Vpe $vpe): CombinationBuilderInterface
    {
        $this->vpe = $vpe;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withWeight(?CombinationWeight $weight): CombinationBuilderInterface
    {
        $this->weight = $weight;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withShippingStatus(?ShippingStatus $shippingStatus): CombinationBuilderInterface
    {
        $this->shippingStatus = $shippingStatus;
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function build(): Combination
    {
        $result = new Combination($this->id,
                                  $this->order,
                                  $this->model,
                                  $this->ean,
                                  $this->quantity,
                                  $this->surcharge,
                                  $this->vpe,
                                  $this->weight,
                                  $this->shippingStatus);
        
        $this->id = $this->order = $this->model = $this->ean = $this->quantity = $this->surcharge = $this->vpe = $this->weight = $this->shippingStatus = null;
        
        return $result;
    }
    
    
    public function buildCheapest()
    {
        $result = new CheapestCombination($this->id,
                                  $this->order,
                                  $this->model,
                                  $this->ean,
                                  $this->quantity,
                                  $this->surcharge,
                                  $this->vpe,
                                  $this->weight,
                                  $this->shippingStatus);
    
        $this->id = $this->order = $this->model = $this->ean = $this->quantity = $this->surcharge = $this->vpe = $this->weight = null;
    
        return $result;
    }
}