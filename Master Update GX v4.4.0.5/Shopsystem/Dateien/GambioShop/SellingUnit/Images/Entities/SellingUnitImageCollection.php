<?php
/**
 * SellingUnitImageCollection.php 2020-4-6
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

namespace Gambio\Shop\SellingUnit\Images\Entities;

use Gambio\Shop\SellingUnit\Images\Entities\Interfaces\SellingUnitImageCollectionInterface;
use Gambio\Shop\SellingUnit\Images\Entities\Interfaces\SellingUnitImageInterface;
use InvalidArgumentException;

/**
 * Class SellingUnitImagesCollection
 *
 * @package Gambio\Shop\SellingUnit\Images\Entities
 */
class SellingUnitImageCollection implements SellingUnitImageCollectionInterface
{
    /**
     * @var SellingUnitImageInterface[]
     */
    protected $values = [];

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * SellingUnitImagesCollection constructor.
     *
     * @param SellingUnitImageInterface[] $images
     */
    public function __construct(array $images = [])
    {
        if (count($images)) {

            foreach ($images as $image) {

                $this[] = $image;
            }
        }
    }


    /**
     * @inheritDoc
     */
    public function current(): SellingUnitImageInterface
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
        if (!$value instanceof SellingUnitImageInterface) {

            throw new InvalidArgumentException(static::class . ' only accepts ' . SellingUnitImageInterface::class);
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


    protected function sort(): void
    {
        $compare = function (SellingUnitImageInterface $x, SellingUnitImageInterface $y) {
            if ($x->source()->sortOrder() == $y->source()->sortOrder()) {
                return 0;
            } else {
                if ($x->source()->sortOrder() > $y->source()->sortOrder()) {
                    return 1;
                } else {
                    return -1;
                }
            }
        };
        usort($this->values, $compare);
    }


    /**
     * @param string $type
     *
     * @return array
     */
    protected function getImagesBySourceType(string $type): array
    {
        $result = [];

        foreach ($this as $image) {

            if ($image->source() instanceof $type) {

                $result[] = $image;
            }
        }

        return $result;
    }
}