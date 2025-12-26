<?php
/*--------------------------------------------------------------------------------------------------
    OnGetSelectedQuantityEvent.php 2020-11-27
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Database\Unit\Events;

use Gambio\Shop\SellingUnit\Core\Events\SellingUnitEventTrait;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSelectedQuantityEventInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\QuantityGraduation;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SelectedQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitStockInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\ExceptionStacker;
use ProductDataInterface;

class OnGetSelectedQuantityEvent implements OnGetSelectedQuantityEventInterface
{
    use SellingUnitEventTrait;

    /**
     * @var QuantityGraduation
     */
    protected $quantityGraduation;
    /**
     * @var SelectedQuantity | ExceptionStacker
     */
    protected $requestedQuantity;
    /**
     * @var SelectedQuantity
     */
    protected $selectedQuantity;
    /**
     * @var QuantityInterface
     */
    protected $stock;


    /**
     * OnGetSelectedQuantityEvent constructor.
     *
     * @param SellingUnitId $id
     * @param QuantityInterface $requestedQuantity
     * @param QuantityGraduation $quantityGraduation
     * @param ProductDataInterface $product
     * @param SellingUnitStockInterface $stock
     */
    public function __construct(
        SellingUnitId $id,
        QuantityInterface $requestedQuantity,
        QuantityGraduation $quantityGraduation,
        ProductDataInterface $product,
        SellingUnitStockInterface $stock
    ) {

        $this->id = $id;
        $this->product = $product;
        $this->requestedQuantity = $requestedQuantity;
        $this->quantityGraduation = $quantityGraduation;
        $this->stock = $stock;
    }



    /**
     * @inheritDoc
     */
    public function quantityGraduation(): QuantityGraduation
    {
        return $this->quantityGraduation;
    }

    /**
     * @inheritDoc
     */
    public function requestedQuantity(): QuantityInterface
    {
        return $this->requestedQuantity;
    }

    /**
     * @return ExceptionStacker
     */
    public function exceptionStacker() : ExceptionStacker
    {
        return $this->requestedQuantity;
    }

    /**
     * @inheritDoc
     */
    public function selectedQuantity(): QuantityInterface
    {
        return $this->selectedQuantity;
    }

    /**
     * @inheritDoc
     */
    public function setSelectedQuantity(QuantityInterface $quantity): void
    {
        $this->selectedQuantity = $quantity;
    }

    /**
     * @inheritDoc
     */
    public function stock(): SellingUnitStockInterface
    {
        return $this->stock;
    }
}