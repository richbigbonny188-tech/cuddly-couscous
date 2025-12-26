<?php
/*--------------------------------------------------------------------------------------------------
    OnGetSellingUnitAvailableQuantityListener.php 2020-12-01
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Listeners;

use Gambio\Shop\Attributes\ProductModifiers\Database\ValueObjects\AttributeModifierIdentifier;
use Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\Services\ReaderServiceInterface;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitAvailableQuantityEventInterface;
use Gambio\Shop\SellingUnit\Unit\Exceptions\InsufficientQuantityException;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\ExceptionStacker;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ModifierQuantityInterface;

class OnGetSellingUnitAvailableQuantityListener
{
    public const PRIORITY = 1000;
    
    /**
     * @var ReaderServiceInterface
     */
    protected $service;
    /**
     * @var string
     */
    private $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT;
    /**
     * @var string
     */
    private $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT;
    /**
     * @var string
     */
    private $GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT;
    /**
     * @var bool
     */
    private $allowCheckout;
    
    
    /**
     * OnGetSellingUnitAvailableQuantityListener constructor.
     *
     * @param ReaderServiceInterface $service
     * @param bool                   $allowCheckout
     * @param string                 $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT
     * @param string                 $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT
     * @param string                 $GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT
     */
    public function __construct(
        ReaderServiceInterface $service,
        bool $allowCheckout,
        string $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT,
        string $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT,
        string $GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT
    ) {
        $this->service                                           = $service;
        $this->GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT  = $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT;
        $this->GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT = $GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT;
        $this->GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT     = $GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT;
        $this->allowCheckout                                     = $allowCheckout;
    }
    
    
    /**
     * @param OnGetSellingUnitAvailableQuantityEventInterface $event
     *
     * @return OnGetSellingUnitAvailableQuantityEventInterface
     */
    public function __invoke(OnGetSellingUnitAvailableQuantityEventInterface $event
    ): OnGetSellingUnitAvailableQuantityEventInterface {
        foreach ($event->id()->modifiers() as $modifierId) {
            if ($modifierId instanceof AttributeModifierIdentifier) {
                $quantity = $this->service->getQuantity($event->id()->productId(),
                                                        $modifierId,
                                                        $event->product(),
                                                        $event->requested());
                if ($quantity) {
                    $this->addExceptionToRequested($event->id()->productId(), $quantity, $event->requested());
                    $event->addQuantity($quantity);
                }
            }
        }
        
        return $event;
    }
    
    
    private function addExceptionToRequested(
        ProductId $productId,
        ModifierQuantityInterface $quantity,
        QuantityInterface $requested
    ) {
        if ($requested instanceof ExceptionStacker && $requested->value() > $quantity->value()) {
            if ($this->allowCheckout) {
                $text = $this->GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CAN_CHECKOUT;
            } elseif ($quantity->value() > 0) {
                $text = $this->GM_ORDER_STOCK_CHECKER_OUT_OF_STOCK_CANT_CHECKOUT;
            } else {
                $text = $this->GM_ORDER_STOCK_CHECKER_NO_STOCK_CANT_CHECKOUT;
            }
            /** @var ExceptionStacker $requested */
            $requested->stackException(new InsufficientQuantityException($productId->value(),
                                                                         $quantity->value(),
                                                                         $text));
        }
    }
}