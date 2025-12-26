<?php
/**
 * Vpe.php 2020-3-26
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

namespace Gambio\Shop\SellingUnit\Unit\ValueObjects;

/**
 * Class Vpe
 * @package Gambio\Shop\SellingUnit\Unit\ValueObjects
 */
class Vpe
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var float
     */
    protected $value;
    
    /**
     * @var string
     */
    protected $name;
    
    
    /**
     * Vpe constructor.
     *
     * @param int    $id
     * @param string $name
     * @param float  $value
     */
    public function __construct(int $id, string $name, float $value)
    {
        $this->id    = $id;
        $this->value = $value;
        $this->name  = $name;
    }
    
    
    /**
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }
    
    
    /**
     * @return float
     */
    public function value(): float
    {
        return $this->value;
    }
    
    
    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
    
}
