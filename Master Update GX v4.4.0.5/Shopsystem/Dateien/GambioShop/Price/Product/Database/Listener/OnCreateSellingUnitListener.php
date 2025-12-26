<?php
/*--------------------------------------------------------------------------------------------------
    OnCreateSellingUnitListener.php 2020-08-20
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\Price\Product\Database\Listener;

use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnCreateSellingUnitEventInterface;

/**
 * Class OnCreateSellingUnitListener
 * @package Gambio\Shop\Price\Product\Database\Listener
 * @codeCoverageIgnore
 * @internal currently untestable due to the usage of the xtcPrice
 */
class OnCreateSellingUnitListener
{
    /**
     * @param OnCreateSellingUnitEventInterface $event
     *
     * @return OnCreateSellingUnitEventInterface
     */
    public function __invoke(OnCreateSellingUnitEventInterface $event): OnCreateSellingUnitEventInterface
    {
        if ($event->xtcPrice() === null) {
            if (isset($GLOBALS['xtPrice'])) {
                $event->setXtcPrice($GLOBALS['xtPrice']);
            } elseif (isset($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id'])) {
                $event->setXtcPrice(new \xtcPrice($_SESSION['currency'],
                                                  $_SESSION['customers_status']['customers_status_id']));
            }
        }
        
        return $event;
    }
    
}