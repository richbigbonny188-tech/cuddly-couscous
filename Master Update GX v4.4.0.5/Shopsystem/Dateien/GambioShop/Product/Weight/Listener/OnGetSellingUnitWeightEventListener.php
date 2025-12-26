<?php
/*--------------------------------------------------------------------
 OnGetSellingUnitWeightEventListener.php 2020-11-27
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Product\Weight\Listener;

use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitWeightEventInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Weight;
use Throwable;

/**
 * Class OnGetSellingUnitWeightEventListener
 * @package Gambio\Shop\Product\Weight\Listener
 */
class OnGetSellingUnitWeightEventListener
{
    /**
     * @param OnGetSellingUnitWeightEventInterface $event
     *
     * @throws Throwable
     */
    public function __invoke(OnGetSellingUnitWeightEventInterface $event)
    {
        if(!$event->product()->showWeight()) {
            $event->builder()->hideWeight();
        }
        $weightFloat = $event->product()->getWeight();
        
        $weight      = new Weight($weightFloat, $event->product()->showWeight());
        $event->builder()->setMainWeight($weight, 1000);
    }
}