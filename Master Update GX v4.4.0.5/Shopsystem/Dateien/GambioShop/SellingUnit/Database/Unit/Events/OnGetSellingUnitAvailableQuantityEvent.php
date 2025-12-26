<?php
/*--------------------------------------------------------------------------------------------------
    OnGetSellingUnitAvailableQuantityEvent.php 2021-05-03
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Database\Unit\Events;

use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitAvailableQuantityEventInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\AvailableQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\ExceptionStacker;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ModifierQuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use ProductDataInterface;

class OnGetSellingUnitAvailableQuantityEvent implements OnGetSellingUnitAvailableQuantityEventInterface
{
    /**
     * @var array
     */
    protected $mainQuantity = [];
    /**
     * @var array
     */
    protected $additionalQuantity = [];
    
    /**
     * @var ?AvailableQuantity
     */
    protected $quantity;
    /**
     * @var bool
     */
    protected $isPropagationStopped = false;
    /**
     * @var SellingUnitId
     */
    protected $id;
    /**
     * @var ProductDataInterface
     */
    protected $product;
    /**
     * @var QuantityInterface
     */
    protected $requested;
    /**
     * @var AvailableQuantity|null
     */
    protected $result;
    
    /**
     * @var boolean
     */
    protected $isValid = true;
    
    
    /**
     * OnGetSellingUnitAvailableQuantityEvent constructor.
     *
     * @param SellingUnitId        $id
     * @param ProductDataInterface $product
     * @param QuantityInterface    $requested
     */
    public function __construct(SellingUnitId $id, ProductDataInterface $product, QuantityInterface $requested)
    {
        $this->id        = $id;
        $this->product   = $product;
        $this->quantity  = new AvailableQuantity();
        $this->requested = $requested;
    }
    
    
    /**
     * @inheritDoc
     */
    public function id(): SellingUnitId
    {
        return $this->id;
    }
    
    
    /**
     * @inheritDoc
     */
    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }
    
    
    /**
     * @return ProductDataInterface
     */
    public function product(): ProductDataInterface
    {
        return $this->product;
    }
    
    
    /**
     * @inheritDoc
     */
    public function buildQuantity(): ?AvailableQuantity
    {
        if ($this->result === null) {
            if (!empty($this->mainQuantity) && $main = $this->getExceptionStackerWithHighestPriority($this->mainQuantity)) {
                if ($main instanceof ExceptionStacker && $this->requested instanceof ExceptionStacker) {
                    /** @var ExceptionStacker|ModifierQuantityInterface $main */
                    foreach ($main->exceptions() as $exception) {
                        $this->requested->stackException($exception);
                    }
                }
                $this->quantity->setMainQuantity($main);
            } else {
                $this->invalidateQuantity();
            }
            $this->result = $this->quantity;
        }
        if (!$this->isValid) {
            $this->result->invalidate();
        }
        
        return $this->result;
    }
    
    
    /**
     * @inheritDoc
     */
    public function addQuantity(ModifierQuantityInterface $quantity): void
    {
        $this->quantity->addQuantity($quantity);
    }
    
    
    /**
     * @return QuantityInterface
     */
    public function requested(): QuantityInterface
    {
        return $this->requested;
    }
    
    
    /**
     * @inheritDoc
     */
    public function stopPropagation(): void
    {
        $this->isPropagationStopped = true;
    }
    
    
    /**
     * @inheritDoc
     */
    public function setMainQuantity(?ModifierQuantityInterface $quantity, int $priority = 0): void
    {
        $this->mainQuantity[$priority] = $quantity;
        ksort($this->mainQuantity);
    }
    
    
    /**
     * @inheritDoc
     */
    public function invalidateQuantity(): void
    {
        $this->isValid = false;
    }
    
    
    /**
     * @param array $prioritisedArray
     *
     * @return ExceptionStacker|null
     */
    private function getExceptionStackerWithHighestPriority(array $prioritisedArray): ?ExceptionStacker
    {
        $priorities = array_keys($prioritisedArray);
        $index      = count($priorities);
        
        while ($index--) {
    
            $priority = $priorities[$index];
            $value    = $prioritisedArray[$priority];
            
            if ($value instanceof ExceptionStacker) {
                
                return $value;
            }
        }
        
        return null;
    }
}