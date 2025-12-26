<?php
/*------------------------------------------------------------------------------
 AvailableCombinationQuantity.php 2020-11-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Properties\ValueObjects;

use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

class AvailableCombinationQuantity extends AbstractPropertyQuantity
{
    
    /**
     * @var CombinationId
     */
    private $combinationId;
    /**
     * @var SellingUnitId
     */
    
    /**
     * PropertyQuantity constructor.
     *
     * @param float                                 $quantity
     * @param string                                $measureUnit
     * @param CombinationId                         $combinationId
     * @param ModifierIdentifierCollectionInterface $collection
     *
     */
    public function __construct(
        float $quantity,
        string $measureUnit,
        CombinationId $combinationId,
        ModifierIdentifierCollectionInterface $collection
    ) {
        parent::__construct($quantity, $measureUnit, $collection);
        $this->combinationId = $combinationId;
    }
    
    
    /**
     * @return CombinationId
     */
    public function combinationId(): CombinationId
    {
        return $this->combinationId;
    }
    
}
