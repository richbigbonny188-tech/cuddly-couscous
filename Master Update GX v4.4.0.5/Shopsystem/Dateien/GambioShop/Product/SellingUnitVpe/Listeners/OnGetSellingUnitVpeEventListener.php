<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitVpeEventListener.php 2021-01-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/
declare(strict_types=1);

namespace Gambio\Shop\Product\SellingUnitVpe\Listeners;

use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitVpeEventInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Vpe;

class OnGetSellingUnitVpeEventListener
{
    /**
     * @param OnGetSellingUnitVpeEventInterface $event
     */
    public function __invoke(OnGetSellingUnitVpeEventInterface $event)
    {
        if ($event->product()->getVpeStatus() && $event->product()->getVpeValue() && $event->product()->getVpeId()) {
            
            $vpe = new Vpe($event->product()->getVpeId(),
                           $event->product()->getVpeName(),
                           $event->product()->getVpeValue());
            
            $event->setVpe($vpe, 1000);
        }
    }
}
