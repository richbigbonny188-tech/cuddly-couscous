<?php
/*------------------------------------------------------------------------------
 CombinationWeight.php 2020-10-26
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Properties\ValueObjects;

use Gambio\Shop\SellingUnit\Unit\ValueObjects\Weight;

class CombinationWeight extends Weight
{
    /**
     * @var bool
     */
    private $mainWeight;
    
    
    /**
     * CombinationWeight constructor.
     *
     * @param float $weight
     * @param bool  $show
     * @param bool  $mainWeight
     */
    public function __construct(float $weight, bool $show, bool $mainWeight)
    {
        parent::__construct($weight, $show);
        $this->mainWeight = $mainWeight;
    }
    
    
    /**
     * @return bool
     */
    public function isMainWeight(): bool
    {
        return $this->mainWeight;
    }
    
}