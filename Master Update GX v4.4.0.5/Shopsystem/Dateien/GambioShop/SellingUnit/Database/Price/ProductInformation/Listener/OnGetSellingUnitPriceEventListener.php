<?php
/*--------------------------------------------------------------------
 OnGetSellingUnitPriceEventListener.php 2020-2-17
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Database\Price\ProductInformation\Listener;

use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitPriceEventInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\PriceStatus;

/**
 * Class OnGetSellingUnitPriceEventListener
 * @package Gambio\Shop\SellingUnit\Database\Price\ProductInformation\Listener
 */
class OnGetSellingUnitPriceEventListener
{
    /**
     * @param OnGetSellingUnitPriceEventInterface $event
     */
    public function __invoke(OnGetSellingUnitPriceEventInterface $event)
    {
        $event->builder()
            ->withQuantity($event->quantity()->value())
            ->withTaxClassId($event->product()->getTaxClassId())
            ->withProductId($event->productId())
            ->withStatus($this->priceStatus($event->product()->priceStatus()));
    }
    
    /**
     * @param int $priceStatus
     * @return PriceStatus
     */
    protected function priceStatus(int $priceStatus) : PriceStatus
    {
        return new PriceStatus($priceStatus);

    }
}