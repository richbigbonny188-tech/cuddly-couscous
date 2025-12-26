<?php
/*------------------------------------------------------------------------------
 PropertyReadRepository.php 2020-11-18
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Database\Repositories;

use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\Properties\Database\Readers\Interfaces\PropertyReaderInterface;
use Gambio\Shop\Properties\Database\Repositories\Interfaces\PropertyReadRepositoryInterface;
use Gambio\Shop\Properties\ProductModifiers\Database\ValueObjects\PropertyModifierIdentifier;
use Gambio\Shop\Properties\Properties\Collections\CombinationCollectionInterface;
use Gambio\Shop\Properties\Properties\Entities\Combination;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\SellingUnitIdBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

class PropertyReadRepository implements PropertyReadRepositoryInterface
{
    /**
     * @var PropertyReaderInterface
     */
    private $reader;
    
    
    /**
     * PropertyReadRepository constructor.
     *
     * @param PropertyReaderInterface $reader
     */
    public function __construct(PropertyReaderInterface $reader)
    {
        $this->reader = $reader;
    }
    
    
    /**
     * @inheritDoc
     */
    public function hasProperties(ProductId $productId): bool
    {
        return $this->reader->hasProperties($productId);
    }
    
    
    /**
     * @inheritDoc
     */
    public function addPropertyInfoToBuilder(int $combinationId, SellingUnitIdBuilderInterface $builder): void
    {
        $data = $this->reader->getCombinationModifierIds($combinationId);
        foreach ($data as $productId => $modifiers) {
            $builder->withProductId(new ProductId((int)$productId));
            foreach ($modifiers as $id) {
                $builder->withModifierId(new PropertyModifierIdentifier((int)$id));
            }
        }
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCombinationsFor(SellingUnitId $id, int $limit = 2): CombinationCollectionInterface
    {
        return $this->reader->getCombinationsFor($id, $limit);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCombinationFor(SellingUnitId $id): ?Combination
    {
        return $this->reader->getCombinationFor($id);
    }
    
    
    /**
     * @inheritcDoc
     */
    public function getCheapestCombinationFor(SellingUnitId $id): ?Combination
    {
        return $this->reader->getCheapestCombinationFor($id);
    }
}