<?php
/*------------------------------------------------------------------------------
 CachedPropertyReader.php 2020-12-08
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Database\Readers;

use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\Properties\Properties\Collections\CombinationCollectionInterface;
use Gambio\Shop\Properties\Properties\Entities\Combination;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

class CachedPropertyReader extends PropertyReader
{
    static protected $hasSurcharge              = [];
    static protected $getCombinationsFor        = [];
    static protected $hasNonLinearSurcharge     = [];
    static protected $hasProperties             = [];
    static protected $getCombinationModifierIds = [];
    static protected $numberOfProperties        = [];
    static protected $cheapestCombinationFor    = [];
    
    
    /**
     * @inheritDoc
     */
    protected function hasSurcharge(int $productId): bool
    {
        if (!isset(static::$hasSurcharge[$productId])) {
            static::$hasSurcharge[$productId] = parent::hasSurcharge($productId);
        }
        
        return static::$hasSurcharge[$productId];
    }
    
    
    /**
     * @inheritDoc
     */
    public function hasProperties(ProductId $productId): bool
    {
        if (!isset(static::$hasProperties[$productId->value()])) {
            static::$hasProperties[$productId->value()] = parent::hasProperties($productId);
        }
        
        return static::$hasProperties[$productId->value()];
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCombinationModifierIds(int $combinationId): iterable
    {
        if (!isset(static::$getCombinationModifierIds[$combinationId])) {
            static::$getCombinationModifierIds[$combinationId] = parent::getCombinationModifierIds($combinationId);
        }
        
        return static::$getCombinationModifierIds[$combinationId];
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCombinationsFor(SellingUnitId $id, int $limit = 2): CombinationCollectionInterface
    {
        if (!isset(static::$getCombinationsFor[$id->hash() . "_{$limit}"])) {
            static::$getCombinationsFor[$id->hash()] = parent::getCombinationsFor($id, $limit);
        }
        
        return static::$getCombinationsFor[$id->hash()];
    }
    
    
    /**
     * @inheritDoc
     */
    protected function hasNonLinearSurcharge(int $productId): bool
    {
        $key = $productId;
        if (!isset(static::$hasNonLinearSurcharge[$key])) {
            static::$hasNonLinearSurcharge[$key] = parent::hasNonLinearSurcharge($productId);
        }
        
        return static::$hasNonLinearSurcharge[$key];
    }
    
    
    /**
     * @inheritDoc
     */
    protected function getNumberOfProperties(ProductId $productId): int
    {
        $key = $productId->value();
        if (!isset(static::$numberOfProperties[$key])) {
            static::$numberOfProperties[$key] = parent::getNumberOfProperties($productId);
        }
        
        return static::$numberOfProperties[$key];
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCheapestCombinationFor(SellingUnitId $id): ?Combination
    {
        $key = $id->hash();
        if (!isset(static::$cheapestCombinationFor[$key])) {
            static::$cheapestCombinationFor[$key] = parent::getCheapestCombinationFor($id);
        }
        
        return static::$cheapestCombinationFor[$key];
    }
    
}