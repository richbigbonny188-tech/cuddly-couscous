<?php
/*------------------------------------------------------------------------------
 WeightBuilderInterface.php 2020-12-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\SellingUnit\Unit\Builders\Interfaces;

use Gambio\Shop\SellingUnit\Unit\ValueObjects\Weight;

/**
 * Interface WeightBuilderInterface
 * @package Gambio\Shop\SellingUnit\Unit\Builders\Interfaces
 */
interface WeightBuilderInterface
{
    /**
     * @param Weight $weight
     *
     * @return void
     */
    public function addWeight(Weight $weight) : void;
    
    
    /**
     * hide the weight information
     */
    public function hideWeight() : void;
    
    
    /**
     * @param Weight|null $weight
     * @param int         $priority
     */
    public function setMainWeight(?Weight $weight, int $priority) : void;
    
    
    /**
     * @return void
     */
    public function reset() : void;
    
    
    /**
     * @return Weight
     */
    public function build(): ?Weight;
}