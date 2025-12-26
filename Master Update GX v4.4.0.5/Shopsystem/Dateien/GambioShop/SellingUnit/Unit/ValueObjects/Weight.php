<?php
/*--------------------------------------------------------------------
 Weight.php 2020-12-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

namespace Gambio\Shop\SellingUnit\Unit\ValueObjects;

/**
 * Class Weight
 * @package Gambio\Shop\SellingUnit\Unit\ValueObjects
 */
class Weight
{
    /**
     * @var float
     */
    protected $weight;
    /**
     * @var bool
     */
    protected $show;
    
    
    /**
     * Weight constructor.
     *
     * @param float $weight
     * @param bool  $show
     */
    public function __construct(float $weight, bool $show)
    {
        $this->weight = $weight;
        $this->show = $show;
    }

    
    /**
     * @return bool
     */
    public function show(): bool
    {
        return $this->show;
    }


    /**
     * @return float
     */
    public function value(): float
    {
        return $this->weight;
    }
}

