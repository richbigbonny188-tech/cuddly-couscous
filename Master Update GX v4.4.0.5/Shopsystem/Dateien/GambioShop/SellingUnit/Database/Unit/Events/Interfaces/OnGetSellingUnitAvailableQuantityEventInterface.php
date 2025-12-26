<?php
/*--------------------------------------------------------------------------------------------------
    OnGetSellingUnitAvailableQuantityEventInterface.php 2020-12-01
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces;

use Gambio\Shop\SellingUnit\Unit\Builders\SellingUnitBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\AvailableQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ModifierQuantityInterface;
use Psr\EventDispatcher\StoppableEventInterface;

interface OnGetSellingUnitAvailableQuantityEventInterface extends BasicSellingUnitEventInterface, StoppableEventInterface
{
    /**
     * @return AvailableQuantity
     */
    public function buildQuantity(): ?AvailableQuantity;


    /**
     * @param ModifierQuantityInterface $quantity
     *
     * @return void
     */
    public function addQuantity(ModifierQuantityInterface $quantity): void;

    /**
     * @return void
     */
    public function invalidateQuantity(): void;


    /**
     * @param ModifierQuantityInterface|null $quantity
     *
     * @param int                            $priority
     *
     * @return void
     */
    public function setMainQuantity(?ModifierQuantityInterface $quantity, int $priority = 0): void;

    /**
     *
     */
    public function stopPropagation(): void;

    /**
     * @return QuantityInterface
     */
    public function requested(): QuantityInterface;
}