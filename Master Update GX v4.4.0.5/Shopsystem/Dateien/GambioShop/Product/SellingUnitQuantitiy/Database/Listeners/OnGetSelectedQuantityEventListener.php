<?php
/*--------------------------------------------------------------------------------------------------
    OnGetSelectedQuantityEventListener.php 2021-02-17
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\Product\SellingUnitQuantitiy\Database\Listeners;

use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSelectedQuantityEventInterface;
use Gambio\Shop\SellingUnit\Unit\Exceptions\InvalidQuantityGranularityException;
use Gambio\Shop\SellingUnit\Unit\Exceptions\QuantitySurpassMaximumAllowedQuantityException;
use Gambio\Shop\SellingUnit\Unit\Exceptions\RequestedQuantityBelowMinimumException;

/**
 * Class OnGetSelectedQuantityListener
 */
class OnGetSelectedQuantityEventListener
{
    /**
     * @var float
     */
    private $maxProductQuantity;
    
    
    /**
     * OnGetSelectedQuantityListener constructor.
     *
     * @param float $maxProductQuantity
     */
    public function __construct(float $maxProductQuantity)
    {
        
        $this->maxProductQuantity = $maxProductQuantity;
    }
    
    
    /**
     * @param OnGetSelectedQuantityEventInterface $event
     *
     * @return OnGetSelectedQuantityEventInterface
     */
    public function __invoke(OnGetSelectedQuantityEventInterface $event): OnGetSelectedQuantityEventInterface
    {
        $quantity = $event->requestedQuantity()->value();
        
        if ($quantity > $this->maxProductQuantity) {
            $event->requestedQuantity()->stackException(new QuantitySurpassMaximumAllowedQuantityException($event->id()
                                                                                                               ->productId()
                                                                                                               ->value(),
                                                                                                           $event->requestedQuantity()
                                                                                                               ->value(),
                                                                                                           $this->maxProductQuantity,
                                                                                                           $event->requestedQuantity()
                                                                                                               ->exception()));
        }
        
        if ($quantity < $event->product()->getMinOrder()) {
            $event->requestedQuantity()->stackException(new RequestedQuantityBelowMinimumException($event->id()
                                                                                                       ->productId()
                                                                                                       ->value(),
                                                                                                   $quantity,
                                                                                                   $event->product()
                                                                                                       ->getMinOrder(),
                                                                                                   $event->requestedQuantity()
                                                                                                       ->exception()));
        }
    
        $quantity    = (int)(10000 * $quantity); // 1000 since the precision for the granularity is 4
        $granularity = (int)($event->product()->getGranularity() * 10000);
        $mod         = $quantity % $granularity;
        
        if ($mod > 0) {
            $event->requestedQuantity()->stackException(new InvalidQuantityGranularityException($event->id()
                                                                                                    ->productId()
                                                                                                    ->value(),
                                                                                                $event->quantityGraduation()
                                                                                                    ->value(),
                                                                                                $event->requestedQuantity()
                                                                                                    ->exception()));
        }
        
        $event->setSelectedQuantity($event->requestedQuantity());
        
        return $event;
    }
}