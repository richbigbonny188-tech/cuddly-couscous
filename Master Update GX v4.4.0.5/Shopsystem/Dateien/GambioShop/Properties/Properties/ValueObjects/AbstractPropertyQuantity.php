<?php
/*------------------------------------------------------------------------------
 AbstractPropertyQuantity.php 2020-11-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Properties\ValueObjects;

use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\AbstractQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ModifierQuantityInterface;

/**
 * Class AbstractPropertyQuantity
 * @package Gambio\Shop\Properties\Properties\ValueObjects
 */
class AbstractPropertyQuantity extends AbstractQuantity implements ModifierQuantityInterface
{
    /**
     * @var ModifierIdentifierCollectionInterface
     */
    protected $collection;
    
    
    /**
     * AbstractPropertyQuantity constructor.
     *
     * @param float                                 $quantity
     * @param string                                $measureUnit
     * @param ModifierIdentifierCollectionInterface $collection
     *
     */
    public function __construct(
        float $quantity,
        string $measureUnit,
        ModifierIdentifierCollectionInterface $collection
    ) {
        parent::__construct($quantity, $measureUnit);
        $this->collection = $collection;
    }
    
    
    /**
     * @inheritDoc
     */
    public function linkedModifiers(): ModifierIdentifierCollectionInterface
    {
        return $this->collection;
    }
}
