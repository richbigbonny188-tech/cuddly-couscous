<?php
/*------------------------------------------------------------------------------
 OptionIdOptionValuesIdDtoCollection.php 2020-10-28
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitPrice\Repository\Dto;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;
use JsonSerializable;

/**
 * Class OptionIdOptionValuesIdDtoCollection
 * @package Gambio\Shop\Attributes\SellingUnitPrice\Repository\Dto
 */
class OptionIdOptionValuesIdDtoCollection implements  Countable, Iterator, ArrayAccess, JsonSerializable
{
    /**
     * @var OptionIdOptionValuesIdDto[]
     */
    protected $values = [];
    
    /**
     * @var int
     */
    protected $position = 0;
    
    /**
     * SellingUnitImagesCollection constructor.
     *
     * @param OptionIdOptionValuesIdDto[] $images
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
    public function current(): OptionIdOptionValuesIdDto
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
        if (!$value instanceof OptionIdOptionValuesIdDto) {
            
            throw new InvalidArgumentException(static::class . ' only accepts ' . OptionIdOptionValuesIdDto::class);
        }
        
        if (empty($offset)) {
            $this->values[] = $value;
        } else {
            $this->values[$offset] = $value;
        }
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
    public function jsonSerialize(): array
    {
        return $this->values;
    }
    
    
    /**
     * @return array
     */
    public function toAssociativeArray(): array
    {
        $result = [];
        
        if (count($this)) {
            
            foreach ($this as $dto) {
                
                /** @var OptionIdOptionValuesIdDto $dto */
                $result[] = ['option'=>$dto->optionId(), 'value'=>$dto->optionValuesId()];
            }
        }
        
        return $result;
    }
}