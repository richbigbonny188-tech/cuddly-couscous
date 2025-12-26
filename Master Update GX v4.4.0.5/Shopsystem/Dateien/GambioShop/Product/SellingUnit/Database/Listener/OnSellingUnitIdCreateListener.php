<?php
/*------------------------------------------------------------------------------
 OnSellingUnitIdCreateListener.php 2020-11-04
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Product\SellingUnit\Database\Listener;

use Exception;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Unit\Events\Interfaces\OnSellingUnitIdCreateEventInterface;

class OnSellingUnitIdCreateListener
{
    /**
     * @param OnSellingUnitIdCreateEventInterface $event
     *
     * @return OnSellingUnitIdCreateEventInterface
     * @throws Exception
     */
    public function __invoke(OnSellingUnitIdCreateEventInterface $event): OnSellingUnitIdCreateEventInterface
    {
        foreach ($event->sets() as $type => $value) {
            if ($type === 'product' || $type === 'product_id') {
                $event->builder()->withProductId(new ProductId((int)$value));
            } elseif ($type === 'info') {
                if(strpos($value,'_') === false) {
                    $value.='_';
                }
                preg_match("/p([\d]+)(?:\{[\d]+\}[\d]+)*(?:x[\d]+){0,1}_/", $value, $t_extract);
                if (isset($t_extract[1])) {
                    $event->builder()->withProductId(new ProductId((int)$t_extract[1]));
                }
            }
        }
        
        return $event;
    }
    
}