<?php
/*--------------------------------------------------------------------------------------------------
    OnGetSellingUnitQuantityListener.php 2020-12-01
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\Product\SellingUnitQuantitiy\Database\Listeners;

use Gambio\Shop\Product\SellingUnitQuantitiy\Criteria\ProductCheckStockCriteria;
use Gambio\Shop\Product\SellingUnitQuantitiy\Quantitiy\Entities\ProductQuantity;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollection;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitAvailableQuantityEventInterface;
use Gambio\Shop\SellingUnit\Unit\Exceptions\InsufficientQuantityException;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\ScopedQuantityInterface;

/**
 * Class OnGetSellingUnitAvailableQuantityListener
 * @package Gambio\Shop\Product\SellingUnitQuantitiy\Database\Listeners
 */
class OnGetSellingUnitAvailableQuantityListener
{
    
    /**
     * @var ProductCheckStockCriteria
     */
    protected $criteria;
    /**
     * @var string
     */
    protected $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT;
    /**
     * @var string
     */
    protected $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT;
    /**
     * @var string
     */
    protected $GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT;
    
    
    /**
     * OnGetSellingUnitAvailableQuantityListener constructor.
     *
     * @param ProductCheckStockCriteria $criteria
     * @param string                    $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT
     * @param string                    $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT
     * @param string                    $GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT
     */
    public function __construct(
        ProductCheckStockCriteria $criteria,
        string $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT,
        string $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT,
        string $GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT
    ) {
        $this->criteria                                          = $criteria;
        $this->GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT  = $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT;
        $this->GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT = $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT;
        $this->GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT     = $GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT;
    }
    
    
    /**
     * @param OnGetSellingUnitAvailableQuantityEventInterface $event
     *
     * @return OnGetSellingUnitAvailableQuantityEventInterface
     */
    public function __invoke(OnGetSellingUnitAvailableQuantityEventInterface $event
    ): OnGetSellingUnitAvailableQuantityEventInterface {
        
        $qty       = $event->product()->getProductQuantity();
        $requested = $event->requested();
        if ($requested instanceof ScopedQuantityInterface) {
            /** @var ScopedQuantityInterface $requested */
            $qty -= $requested->scope()->quantityFor($event->id()->productId(), new ModifierIdentifierCollection([]));
        }
        
        $result = new ProductQuantity($qty, $event->product()->measureUnit(), $event->id()->modifiers());
        if ($event->requested()->value() > $qty && $this->criteria->checkStock()) {
            
            if ($this->criteria->allowCheckout()) {
                $text = $this->GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT;
            } elseif ($qty > 0) {
                $text = $this->GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT;
            } else {
                $text = $this->GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT;
            }
            
            $result->stackException(new InsufficientQuantityException($event->id()->productId()->value(), $qty, $text));
        }
        
        $event->setMainQuantity($result, 100);
        
        return $event;
    }
}