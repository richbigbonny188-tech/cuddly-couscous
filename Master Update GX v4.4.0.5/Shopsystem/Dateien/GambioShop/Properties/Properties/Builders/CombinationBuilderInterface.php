<?php
/*------------------------------------------------------------------------------
 CombinationBuilderInterface.php 2020-11-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Properties\Builders;

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

interface CombinationBuilderInterface
{
    /**
     * @param CombinationId $id
     *
     * @return CombinationBuilderInterface
     */
    public function withId(CombinationId $id): CombinationBuilderInterface;
    
    
    /**
     * @param CombinationOrder $order
     *
     * @return CombinationBuilderInterface
     */
    public function withOrder(CombinationOrder $order): CombinationBuilderInterface;
    
    
    /**
     * @param CombinationModel $model
     *
     * @return CombinationBuilderInterface
     */
    public function withModel(CombinationModel $model): CombinationBuilderInterface;
    
    
    /**
     * @param CombinationEan $ean
     *
     * @return CombinationBuilderInterface
     */
    public function withEan(CombinationEan $ean): CombinationBuilderInterface;
    
    
    /**
     * @param CombinationQuantity $quantity
     *
     * @return CombinationBuilderInterface
     */
    public function withQuantity(CombinationQuantity $quantity): CombinationBuilderInterface;
    
    
    /**
     * @param CombinationSurcharge|null $surcharge
     *
     * @return CombinationBuilderInterface
     */
    public function withSurcharge(?CombinationSurcharge $surcharge): CombinationBuilderInterface;
    
    
    /**
     * @param Vpe|null $vpe
     *
     * @return CombinationBuilderInterface
     */
    public function withVpe(?Vpe $vpe): CombinationBuilderInterface;
    
    
    /**
     * @param CombinationWeight|null $weight
     *
     * @return CombinationBuilderInterface
     */
    public function withWeight(?CombinationWeight $weight): CombinationBuilderInterface;
    
    
    /**
     * @param ShippingStatus|null $shippingStatus
     *
     * @return CombinationBuilderInterface
     */
    public function withShippingStatus(?ShippingStatus $shippingStatus): CombinationBuilderInterface;
    
    
    /**
     * @return Combination
     */
    public function build(): Combination;
    
    /**
     * @return Combination
     */
    public function buildCheapest();
}
