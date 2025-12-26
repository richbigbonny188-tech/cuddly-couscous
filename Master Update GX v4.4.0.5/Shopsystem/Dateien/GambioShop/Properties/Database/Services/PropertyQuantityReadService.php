<?php
/*------------------------------------------------------------------------------
 PropertyQuantityReadService.php 2020-12-21
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Properties\Database\Services;

use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollection;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\Properties\Database\Criterias\CheckStockCriteria;
use Gambio\Shop\Properties\Database\Exceptions\CombinationNotFoundException;
use Gambio\Shop\Properties\Database\Repositories\Interfaces\PropertyReadRepositoryInterface;
use Gambio\Shop\Properties\Database\Services\Interfaces\PropertyQuantityReadServiceInterface;
use Gambio\Shop\Properties\ProductModifiers\Database\ValueObjects\PropertyModifierIdentifier;
use Gambio\Shop\Properties\Properties\Entities\Combination;
use Gambio\Shop\Properties\Properties\ValueObjects\AbstractPropertyQuantity;
use Gambio\Shop\Properties\Properties\ValueObjects\AvailableCombinationQuantity;
use Gambio\Shop\SellingUnit\Unit\Exceptions\InsufficientQuantityException;
use Gambio\Shop\SellingUnit\Unit\Exceptions\InvalidQuantityException;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\ScopedQuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use ProductDataInterface;
use Gambio\Core\Language\TextManager;

/**
 * Class PropertyQuantityReadService
 * @package Gambio\Shop\Properties\Database\Services
 */
class PropertyQuantityReadService implements PropertyQuantityReadServiceInterface
{
    /**
     * @var PropertyReadRepositoryInterface
     */
    protected $repository;
    /**
     * @var CheckStockCriteria
     */
    private $criteria;
    /**
     * @var TextManager
     */
    private $textManager;
    
    
    /**
     * PropertyQuantityReadService constructor.
     *
     * @param PropertyReadRepositoryInterface $repository
     * @param CheckStockCriteria              $criteria
     * @param TextManager             $textManager
     */
    public function __construct(
        PropertyReadRepositoryInterface $repository,
        CheckStockCriteria $criteria,
        TextManager $textManager
    
    ) {
        $this->repository  = $repository;
        $this->criteria    = $criteria;
        $this->textManager = $textManager;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getAvailableQuantityBy(
        SellingUnitId $id,
        ProductDataInterface $product,
        QuantityInterface $requested
    ): ?AbstractPropertyQuantity {
        try {
            if ($this->criteria->getSourceConfiguration($product->getPropertiesQuantityCheck(), true, false)) {
                //if the product is not configured to use combination stock
                return null;
            }
        } catch (\InvalidArgumentException $e) {
            return null;
        }
        
        try {
            $combination = $this->repository->getCombinationFor($id);
        } catch (CombinationNotFoundException $e) {
            $message = $this->textManager->getPhraseText('COMBI_NOT_EXIST',
                                                    'properties_dropdown',
                                                    $id->language()->value());
            throw new InvalidQuantityException($message);
        }
        
        if ($combination && $combination->quantity()) {
            return $this->getAvailableQuantityByCombination($id, $combination, $product, $requested);
        }
        
        return null;
    }
    
    
    /**
     * @param SellingUnitId        $sellingUnitId
     * @param Combination          $combination
     * @param ProductDataInterface $product
     * @param QuantityInterface    $requested
     *
     * @return AvailableCombinationQuantity|null
     */
    public function getAvailableQuantityByCombination(
        SellingUnitId $sellingUnitId,
        Combination $combination,
        ProductDataInterface $product,
        QuantityInterface $requested
    ): ?AvailableCombinationQuantity {
        $collection = $this->createModifierIdCollection($sellingUnitId);
        $qty        = $combination->quantity() ? $combination->quantity()->value() : null;
        if ($requested && $requested instanceof ScopedQuantityInterface) {
            /** @var ScopedQuantityInterface $requested */
            $qty -= $requested->scope()->quantityFor($sellingUnitId->productId(), $collection);
        }
        $result = new AvailableCombinationQuantity($qty, $product->measureUnit(), $combination->id(), $collection);
        
        if ($requested->value() > $qty
            && $this->criteria->checkStockForCombinationConfiguration($product->getPropertiesQuantityCheck())) {
            $phraseName = $this->criteria->allowCheckout() ? 'COMBI_NOT_AVAILABLE_BUT_ALLOWED' : 'COMBI_NOT_AVAILABLE';
            $text      = $this->textManager->getPhraseText($phraseName,
                                                      'properties_dropdown',
                                                      $sellingUnitId->language()->value());

            $result->stackException(new InsufficientQuantityException($sellingUnitId->productId()->value(),
                                                                      $qty,
                                                                      $text));
        }
        
        return $result;
    }
    
    
    /**
     * @param SellingUnitId $id
     *
     * @return ModifierIdentifierCollectionInterface
     */
    protected function createModifierIdCollection(SellingUnitId $id): ModifierIdentifierCollectionInterface
    {
        $propertyIdList = [];
        foreach ($id->modifiers() as $modifierId) {
            if ($modifierId instanceof PropertyModifierIdentifier) {
                $propertyIdList[] = $modifierId;
            }
        }
        
        return new ModifierIdentifierCollection($propertyIdList);
    }
}
