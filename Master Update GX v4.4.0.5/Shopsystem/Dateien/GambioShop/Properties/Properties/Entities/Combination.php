<?php
/*------------------------------------------------------------------------------
 Combination.php 2020-11-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Properties\Entities;

use Gambio\Shop\Properties\Properties\ValueObjects\CombinationEan;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationId;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationModel;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationOrder;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationQuantity;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationSurcharge;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationWeight;
use Gambio\Shop\Properties\Properties\ValueObjects\ShippingStatus;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Vpe;

class Combination
{
    /**
     * @var CombinationSurcharge|null
     */
    protected $surcharge;
    /**
     * @var Vpe|null
     */
    protected $vpe;
    /**
     * @var CombinationEan
     */
    private $ean;
    /**
     * @var CombinationId
     */
    private $id;
    /**
     * @var CombinationModel
     */
    private $model;
    /**
     * @var CombinationOrder
     */
    private $order;
    /**
     * @var CombinationQuantity
     */
    private $quantity;
    /**
     * @var CombinationWeight|null
     */
    private $weight;
    /**
     * @var ShippingStatus|null
     */
    private $shippingStatus;
    
    
    /**
     * Combination constructor.
     *
     * @param CombinationId             $id
     * @param CombinationOrder          $order
     * @param CombinationModel          $model
     * @param CombinationEan            $ean
     * @param CombinationQuantity|null  $quantity
     * @param CombinationSurcharge|null $surcharge
     * @param Vpe|null                  $vpe
     * @param CombinationWeight|null    $weight
     * @param ShippingStatus|null       $shippingStatus
     */
    public function __construct(
        CombinationId $id,
        CombinationOrder $order,
        CombinationModel $model,
        CombinationEan $ean,
        ?CombinationQuantity $quantity,
        ?CombinationSurcharge $surcharge,
        ?Vpe $vpe,
        ?CombinationWeight $weight,
        ?ShippingStatus $shippingStatus
    ) {
    
        $this->id             = $id;
        $this->order          = $order;
        $this->model          = $model;
        $this->ean            = $ean;
        $this->quantity       = $quantity;
        $this->surcharge      = $surcharge;
        $this->vpe            = $vpe;
        $this->weight         = $weight;
        $this->shippingStatus = $shippingStatus;
    }
    
    
    /**
     * @return CombinationEan
     */
    public function ean(): CombinationEan
    {
        return $this->ean;
    }
    
    
    /**
     * @return CombinationId
     */
    public function id(): CombinationId
    {
        return $this->id;
    }
    
    
    /**
     * @return CombinationModel
     */
    public function model(): CombinationModel
    {
        return $this->model;
    }
    
    
    /**
     * @return CombinationOrder
     */
    public function order(): CombinationOrder
    {
        return $this->order;
    }
    
    
    /**
     * @return CombinationQuantity
     */
    public function quantity(): ?CombinationQuantity
    {
        return $this->quantity;
    }
    
    
    /**
     * @return CombinationSurcharge|null
     */
    public function surcharge(): ?CombinationSurcharge
    {
        return $this->surcharge;
    }
    
    
    /**
     * @return Vpe|null
     */
    public function vpe(): ?Vpe
    {
        return $this->vpe;
    }
    
    
    /**
     * @return CombinationWeight|null
     */
    public function weight(): ?CombinationWeight
    {
        return $this->weight;
    }
    
    
    /**
     * @return ShippingStatus|null
     */
    public function shippingStatus(): ?ShippingStatus
    {
        return $this->shippingStatus;
    }
}