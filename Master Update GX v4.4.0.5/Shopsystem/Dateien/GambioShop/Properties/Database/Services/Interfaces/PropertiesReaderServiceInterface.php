<?php
/*------------------------------------------------------------------------------
 PropertiesReaderServiceInterface.php 2020-12-08
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Database\Services\Interfaces;

use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\Properties\Database\Exceptions\CombinationNotFoundException;
use Gambio\Shop\Properties\Database\Exceptions\IncompletePropertyListException;
use Gambio\Shop\Properties\Database\Exceptions\ProductDoesntHavePropertiesException;
use Gambio\Shop\Properties\Properties\Collections\CombinationCollectionInterface;
use Gambio\Shop\Properties\Properties\Entities\Combination;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\SellingUnitIdBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

interface PropertiesReaderServiceInterface
{
    
    /**
     * @param int                           $combinationId
     *
     * @param SellingUnitIdBuilderInterface $builder
     *
     * @return void
     */
    public function addPropertyInfoToBuilder(int $combinationId, SellingUnitIdBuilderInterface $builder): void;
    
    
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
     * @param bool          $silentExceptions
     *
     * @return mixed
     * @throws IncompletePropertyListException
     * @throws ProductDoesntHavePropertiesException
     * @throws CombinationNotFoundException
     */
    public function getCombinationFor(SellingUnitId $id, $silentExceptions = true): ?Combination;
    
    
    /**
     * @param SellingUnitId $id
     *
     * @return Combination|null
     */
    public function getCheapestCombinationFor(SellingUnitId $id): ?Combination;
}