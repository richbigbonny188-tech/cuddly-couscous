<?php
/*--------------------------------------------------------------------
 OnGetShippingInfoEventListener.php 2020-11-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Product\ShippingLink\Listener;

use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetShippingInfoEventInterface;

/**
 * Class OnGetShippingInfoEventListener
 * @package Gambio\Shop\Product\ShippingLink\Listener
 */
class OnGetShippingInfoEventListener
{
    /**
     * @param OnGetShippingInfoEventInterface $event
     */
    public function __invoke(OnGetShippingInfoEventInterface $event)
    {
        $event->builder()->withLanguage($event->id()->language());
        $event->builder()->withProductId($event->id()->productId());
        if ($event->product()->shippingTime()) {
            $event->builder()->withStatus($event->product()->shippingTime());
        }
    }
}