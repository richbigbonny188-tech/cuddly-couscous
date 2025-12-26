<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitEanEventListener.php 2020-11-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Product\Ean\Listener;

use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitEanEventInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitEanEvent;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Ean;

/**
 * Class OnGetProductEanEventListener
 * @package Gambio\Shop\Product\Ean\Listener
 */
class OnGetSellingUnitEanEventListener
{
    public const PRIORITY = 1000;
    /**
     * @param OnGetSellingUnitEanEventInterface $event
     */
    public function __invoke(OnGetSellingUnitEanEventInterface $event)
    {
        $productEan          = new Ean($event->product()->getEan());
        $event->builder()->withEanAtPos($productEan, 1000);
    }
    
}