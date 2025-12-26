<?php
/*------------------------------------------------------------------------------
 PropertyReaderInterface.php 2020-11-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Database\Readers\Interfaces;

use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\Properties\Database\Exceptions\CombinationNotFoundException;
use Gambio\Shop\Properties\Database\Exceptions\IncompletePropertyListException;
use Gambio\Shop\Properties\Database\Exceptions\ProductDoesntHavePropertiesException;
use Gambio\Shop\Properties\Properties\Collections\CombinationCollectionInterface;
use Gambio\Shop\Properties\Properties\Entities\Combination;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

interface PropertyReaderInterface
{
    /**
     * @param int $combinationId
     *
     * @return ModifierIdentifierCollectionInterface
     */
    public function getCombinationModifierIds(int $combinationId): iterable;
    
    
    /**
     * @param ProductId $productId
     *
     * @return mixed
     */
    public function hasProperties(ProductId $productId): bool;
    
    
    /**
     * @param SellingUnitId $id
     * @param int           $limit
     *
     * @return CombinationCollectionInterface
     */
    public function getCombinationsFor(SellingUnitId $id, int $limit = 2): CombinationCollectionInterface;
    
    
    /**
     * @param SellingUnitId $id
     *
     * @return mixed
     * @throws CombinationNotFoundException
     * @throws IncompletePropertyListException
     * @throws ProductDoesntHavePropertiesException
     */
    public function getCombinationFor(SellingUnitId $id): ?Combination;
    
    
    /**
     * @param SellingUnitId $id
     *
     * @return mixed
     */
    public function getCheapestCombinationFor(SellingUnitId $id): ?Combination;
    
}