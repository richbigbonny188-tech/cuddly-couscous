<?php
/*--------------------------------------------------------------------------------------------------
    OnGetProductInfoDiscountAllowedEventListener.php 2021-03-29
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Shop\Product\DiscountAllowed\Listener;

use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetProductInfoEventInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\DiscountAllowed;

/**
 * Class OnGetSellingUnitDiscountAllowedEventListener
 * @package Gambio\Shop\Product\DiscountAllowed\Listener
 */
class OnGetProductInfoEventListener
{
    /**
     * @param OnGetProductInfoEventInterface $event
     */
    public function __invoke(OnGetProductInfoEventInterface $event)
    {
        $discountAllowed = new DiscountAllowed($event->product()->discountAllowed());
    
        $event->builder()->withDiscountAllowed($discountAllowed);
    }
}
