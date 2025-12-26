<?php
/*--------------------------------------------------------------------
 OnGetProductModelEventListener.php 2020-06-24
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Product\Model\Listener;

use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitModelEventInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Model;

/**
 * Class OnGetProductModelEventListener
 *
 * @package Gambio\Shop\Product\Model\Listener
 */
class OnGetSellingUnitModelEventListener
{
    /**
     * @param OnGetSellingUnitModelEventInterface $event
     *
     * @return OnGetSellingUnitModelEventInterface
     */
    public function __invoke(OnGetSellingUnitModelEventInterface $event)
    {
        if ($event->product()->getModel()) {
            $productModel = new Model($event->product()->getModel());
            $event->builder()->withModelAtPos($productModel, 1000);
        }
        return $event;
    }
}