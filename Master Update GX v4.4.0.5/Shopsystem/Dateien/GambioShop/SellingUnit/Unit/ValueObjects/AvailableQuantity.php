<?php
/*--------------------------------------------------------------------------------------------------
    AvailableQuantity.php 2020-12-01
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\SellingUnit\Unit\ValueObjects;

use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierIdentifierInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;

class AvailableQuantity implements QuantityInterface
{
    /**
     * @var ModifierQuantityInterface[]
     */
    protected $quantities = [];
    /**
     * @var ModifierQuantityInterface
     */
    protected $mainQuantity;

    /**
     * @var bool
     */
    protected $valid = true;


    /**
     * @param ModifierQuantityInterface $quantity
     */
    public function addQuantity(ModifierQuantityInterface $quantity): void
    {
        if (count($this->quantities) && $this->quantities[0]->measureUnit() !== $quantity->measureUnit()) {
            throw new \InvalidArgumentException('Invalid measure unit: ' . $quantity->measureUnit());
        }
        $this->quantities[] = $quantity;
    }


    /**
     * @param ModifierQuantityInterface $quantity
     */
    public function setMainQuantity(ModifierQuantityInterface $quantity): void
    {
        $this->mainQuantity = $quantity;
        $this->addQuantity($this->mainQuantity);
    }


    /**
     * @param ModifierIdentifierInterface $id
     *
     * @return ModifierQuantityInterface|null
     */
    public function byModifier(ModifierIdentifierInterface $id): ?ModifierQuantityInterface
    {
        $result = null;
        foreach ($this->quantities as $quantity) {
            foreach ($quantity->linkedModifiers() as $linkedModifier) {
                if ($id->equals($linkedModifier)) {
                    return $quantity;
                }
            }
        }

        return null;
    }


    /**
     * @inheritDoc
     */
    public function measureUnit(): string
    {
        return count($this->quantities) ? $this->quantities[0]->measureUnit() : '';
    }


    /**
     * @inheritDoc
     */
    public function value(): float
    {
        if (empty($this->quantities)) {

            return 0;
        }

        $values = array_map(static function (ModifierQuantityInterface $quantity) {
            return $quantity->value();
        },
            $this->quantities);

        return min($values);
    }


    /**
     * @return ModifierQuantityInterface
     */
    public function mainQuantity(): ?QuantityInterface
    {
        return $this->mainQuantity;
    }

    /**
     * @return void
     */
    public function invalidate(): void
    {
        $this->valid = false;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }
}