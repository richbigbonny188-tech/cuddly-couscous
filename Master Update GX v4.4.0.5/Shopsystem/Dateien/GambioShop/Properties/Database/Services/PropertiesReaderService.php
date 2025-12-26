<?php
/*------------------------------------------------------------------------------
 PropertiesReaderService.php 2020-12-08
  Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Database\Services;

use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\Properties\Database\Exceptions\CombinationNotFoundException;
use Gambio\Shop\Properties\Database\Exceptions\IncompletePropertyListException;
use Gambio\Shop\Properties\Database\Exceptions\ProductDoesntHavePropertiesException;
use Gambio\Shop\Properties\Database\Repositories\Interfaces\PropertyReadRepositoryInterface;
use Gambio\Shop\Properties\Database\Services\Interfaces\PropertiesReaderServiceInterface;
use Gambio\Shop\Properties\Properties\Collections\CombinationCollectionInterface;
use Gambio\Shop\Properties\Properties\Entities\Combination;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\SellingUnitIdBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

class PropertiesReaderService implements PropertiesReaderServiceInterface
{
    /**
     * @var PropertyReadRepositoryInterface
     */
    private $repository;
    
    
    /**
     * PropertiesReaderService constructor.
     *
     * @param PropertyReadRepositoryInterface $repository
     */
    public function __construct(PropertyReadRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    
    
    /**
     * @inheritDoc
     */
    public function hasProperties(ProductId $productId): bool
    {
        return $this->repository->hasProperties($productId);
    }
    
    
    /**
     * @inheritDoc
     */
    public function addPropertyInfoToBuilder(int $combinationId, SellingUnitIdBuilderInterface $builder): void
    {
        $this->repository->addPropertyInfoToBuilder($combinationId, $builder);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCombinationsFor(SellingUnitId $id, int $limit = 2): CombinationCollectionInterface
    {
        return $this->repository->getCombinationsFor($id, $limit);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCombinationFor(SellingUnitId $id, $silentExceptions = true): ?Combination
    {
        if(!$silentExceptions) {
            return $this->repository->getCombinationFor($id);
        } else {
            try {
                return $this->repository->getCombinationFor($id);
            } catch (CombinationNotFoundException $e) {
                return null;
            } catch (IncompletePropertyListException $e) {
                return null;
            } catch (ProductDoesntHavePropertiesException $e) {
                return null;
            }
        }
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCheapestCombinationFor(SellingUnitId $id): ?Combination
    {
        return $this->repository->getCheapestCombinationFor($id);
    }
}