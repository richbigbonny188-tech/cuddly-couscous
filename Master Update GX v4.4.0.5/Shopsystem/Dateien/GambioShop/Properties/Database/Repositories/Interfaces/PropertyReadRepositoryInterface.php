<?php
/*------------------------------------------------------------------------------
 PropertyReadRepositoryInterface.php 2020-11-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Database\Repositories\Interfaces;

use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\Properties\Database\Exceptions\CombinationNotFoundException;
use Gambio\Shop\Properties\Database\Exceptions\IncompletePropertyListException;
use Gambio\Shop\Properties\Database\Exceptions\ProductDoesntHavePropertiesException;
use Gambio\Shop\Properties\Properties\Collections\CombinationCollectionInterface;
use Gambio\Shop\Properties\Properties\Entities\Combination;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\SellingUnitIdBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

interface PropertyReadRepositoryInterface
{
    /**
     * @param ProductId $productId
     *
     * @return mixed
     */
    public function hasProperties(ProductId $productId);
    
    
    /**
     * @param int                           $combinationId
     *
     * @param SellingUnitIdBuilderInterface $builder
     *
     * @return void
     */
    public function addPropertyInfoToBuilder(int $combinationId, SellingUnitIdBuilderInterface $builder): void;
    
    
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
     * @return Combination|null
     * @throws CombinationNotFoundException
     * @throws IncompletePropertyListException
     * @throws ProductDoesntHavePropertiesException
     */
    public function getCombinationFor(SellingUnitId $id): ?Combination;
    
    
    /**
     * @param SellingUnitId $id
     *
     * @return Combination|null
     */
    public function getCheapestCombinationFor(SellingUnitId $id): ?Combination;
    
}