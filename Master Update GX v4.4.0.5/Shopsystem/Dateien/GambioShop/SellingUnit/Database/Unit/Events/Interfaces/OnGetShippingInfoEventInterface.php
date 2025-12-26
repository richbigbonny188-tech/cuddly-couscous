<?php
/*--------------------------------------------------------------------
 OnGetShippingInfoEventInterface.php 2020-11-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/
declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces;

use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\ShippingBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\Entities\Price;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use ProductDataInterface;

/**
 * Interface OnGetShippingInfoEventInterface
 * @package Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces
 */
interface OnGetShippingInfoEventInterface
{
    /**
     * @return SellingUnitId
     */
    public function id(): SellingUnitId;
    
    
    /**
     * @return ShippingBuilderInterface
     */
    public function builder(): ShippingBuilderInterface;
    
    
    /**
     * @return Price
     */
    public function price(): Price;
    
    
    /**
     * @return ProductDataInterface
     */
    public function product(): ProductDataInterface;
    
}