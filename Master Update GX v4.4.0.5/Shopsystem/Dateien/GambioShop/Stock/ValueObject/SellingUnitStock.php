<?php
/*--------------------------------------------------------------------------------------------------
    SellingUnitStock.php 2020-11-25
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\Shop\Stock\ValueObject;

use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierIdentifierInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitAvailableQuantityEventInterface;
use Gambio\Shop\SellingUnit\Database\Unit\Events\OnGetSellingUnitAvailableQuantityEvent;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\AvailableQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ReservedQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SelectedQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitStockInterface;
use ProductDataInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class SellingUnitStock implements SellingUnitStockInterface
{
    
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;
    
    /**
     * @var SellingUnitId
     */
    protected $id;
    
    /**
     * @var ProductDataInterface
     */
    protected $product;
    
    /**
     * @var AvailableQuantity
     */
    protected $availableQuantity;
    
    /**
     * @var ReservedQuantity
     */
    protected $reservedQuantity;
    /**
     * @var QuantityInterface
     */
    protected $requested;
    
    
    /**
     * SellingUnitStock constructor.
     *
     * @param SellingUnitId            $id
     * @param ProductDataInterface     $product
     * @param QuantityInterface        $requested
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        SellingUnitId $id,
        ProductDataInterface $product,
        QuantityInterface $requested,
        EventDispatcherInterface $dispatcher
    ) {
        $this->dispatcher = $dispatcher;
        $this->id         = $id;
        $this->product    = $product;
        $this->requested  = $requested;
    }
    
    
    /**
     * @inheritDoc
     */
    public function availableQuantity(): AvailableQuantity
    {
        if (!$this->availableQuantity) {
            /**
             * @var OnGetSellingUnitAvailableQuantityEventInterface $event
             */
            $event                   = $this->dispatcher->dispatch(new OnGetSellingUnitAvailableQuantityEvent($this->id,
                                                                                                              $this->product,
                                                                                                              $this->requested));
            $this->availableQuantity = $event->buildQuantity();
        }
        
        return $this->availableQuantity;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getQuantityByModifier(ModifierIdentifierInterface $id): QuantityInterface
    {
        $quantity      = $this->availableQuantity()->byModifier($id);
        $quantityValue = ($quantity === null ? 0 : $quantity->value());
        
        return new SelectedQuantity($quantityValue, '');
    }
}