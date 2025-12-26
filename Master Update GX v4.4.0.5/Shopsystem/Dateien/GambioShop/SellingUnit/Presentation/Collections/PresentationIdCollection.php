<?php
/**
 * PresentationIdCollection.php 2020-05-11
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Presentation\Collections;

use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Presentation\Entities\AbstractPresentationId;
use InvalidArgumentException;

/**
 * Class PresentationIdCollection
 *
 * @package Gambio\Shop\SellingUnit\Presentation\Collections
 */
class PresentationIdCollection implements PresentationIdCollectionInterface
{

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var AbstractPresentationId[]
     */
    protected $values = [];

    /**
     * @var ProductId
     */
    protected $productId;


    /**
     * SellingUnitImagesCollection constructor.
     *
     * @param ProductId $productId
     * @param AbstractPresentationId[] $identifiers
     */
    public function __construct(ProductId $productId, array $identifiers = [])
    {
        if (count($identifiers)) {

            foreach ($identifiers as $identifier) {

                $this[] = $identifier;
            }
        }

        $this->productId = $productId;
    }


    /**
     * @inheritDoc
     */
    public function current(): AbstractPresentationId
    {
        return $this->values[$this->position];
    }


    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->position++;
    }


    /**
     * @inheritDoc
     */
    public function key(): int
    {
        return $this->position;
    }


    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->values[$this->position]);
    }


    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->position = 0;
    }


    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->values[$offset]);
    }


    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->values[$offset];
    }


    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof AbstractPresentationId) {

            throw new InvalidArgumentException(static::class . ' only accepts ' . AbstractPresentationId::class);
        }

        if (empty($offset)) {
            $this->values[] = $value;
        } else {
            $this->values[$offset] = $value;
        }

        $this->sort();
    }


    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->values[$offset]);
    }


    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->values);
    }


    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->values;
    }


    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        $result = (string)$this->productId->value();

        if (count($this)) {

            foreach ($this as $presentationId) {

                $result .= $presentationId;
            }
        }

        return $result;
    }


    /**
     * sort the values array
     */
    protected function sort(): void
    {
        $compare = function (AbstractPresentationId $x, AbstractPresentationId $y) {
            if ($x->sortOrder() == $y->sortOrder()) {
                return 0;
            } else {
                if ($x->sortOrder() > $y->sortOrder()) {
                    return 1;
                } else {
                    return -1;
                }
            }
        };
        usort($this->values, $compare);
    }
}