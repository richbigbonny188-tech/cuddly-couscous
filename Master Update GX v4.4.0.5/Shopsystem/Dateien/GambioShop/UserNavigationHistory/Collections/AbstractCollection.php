<?php
/*--------------------------------------------------------------
   AbstractCollection.php 2020-09-02
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\UserNavigationHistory\Collections;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;
use JsonSerializable;

/**
 * Class AbstractCollection
 * @package Gambio\Shop\UserNavigationHistory\Collections
 */
abstract class AbstractCollection implements Countable, Iterator, ArrayAccess, JsonSerializable
{
    /**
     * @var Object[]
     */
    protected $values = [];
    
    /**
     * @var int
     */
    protected $position = 0;
    
    
    /**
     * AbstractCollection constructor.
     *
     * @param Object[] $values
     */
    public function __construct(array $values = [])
    {
        if (count($values)) {
            
            foreach ($values as $value) {
                
                $this[] = $value;
            }
        }
    }
    
    
    /**
     * @return mixed
     */
    public function currentValue()
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
        if (false === $this->isValid($value)) {
            
            throw new InvalidArgumentException(static::class . ' does not accepts ' . get_class($value));
        }
        
        if (empty($offset)) {
            $this->values[] = $value;
        } else {
            $this->values[$offset] = $value;
        }
    }
    
    
    /**
     * @param $value
     *
     * @return bool
     */
    abstract protected function isValid($value): bool;
    
    
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
}