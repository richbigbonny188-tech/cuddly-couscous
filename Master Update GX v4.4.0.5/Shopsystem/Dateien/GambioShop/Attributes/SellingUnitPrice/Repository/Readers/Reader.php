<?php
/*--------------------------------------------------------------------
 Reader.php 2020-2-28
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnitPrice\Repository\Readers;

use Doctrine\DBAL\Connection;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\AbstractModifierIdentifier;
use Gambio\Shop\Attributes\SellingUnitPrice\Exceptions\NoAttributeOptionValuesIdInModifierCollectionFoundException;
use Gambio\Shop\Attributes\SellingUnitPrice\Repository\Dto\OptionIdOptionValuesIdDto;
use Gambio\Shop\Attributes\SellingUnitPrice\Repository\Dto\OptionIdOptionValuesIdDtoCollection;

/**
 * Class Reader
 * @package Gambio\Shop\Attributes\SellingUnitPrice\Repository\Readers
 */
class Reader implements ReaderInterface
{
    /**
     * @var Connection
     */
    protected $connection;
    
    
    /**
     * Reader constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getOptionIdOptionValuesId(
        ModifierIdentifierCollectionInterface $identifierCollection,
        ProductId $productId
    ): OptionIdOptionValuesIdDtoCollection {
        
        $optionValuesIds = $this->optionValuesIds($identifierCollection, $productId);
        
        $queryResult = $this->connection->createQueryBuilder()
            ->select('options_id, options_values_id')
            ->from('products_attributes')
            ->where('options_values_id IN(' . implode(', ', $optionValuesIds) . ')')
            ->andWhere('products_id = ' . $productId->value())
            ->execute();
        
        if ($queryResult->rowCount() !== 0) {
    
            $collection = new OptionIdOptionValuesIdDtoCollection;
    
            foreach ($queryResult->fetchAll() as ['options_id' => $optionId, 'options_values_id' => $valueId]) {
        
                $collection[] = new OptionIdOptionValuesIdDto((int)$optionId, (int)$valueId);
            }
            
            return $collection;
        }
        
        throw $this->noAttributeOptionValuesIdInModifierCollectionFoundException($productId);
    }
    
    
    /**
     * @param ModifierIdentifierCollectionInterface $identifierCollection
     *
     * @param ProductId                             $productId
     *
     * @return array
     * @throws NoAttributeOptionValuesIdInModifierCollectionFoundException
     */
    protected function optionValuesIds(
        ModifierIdentifierCollectionInterface $identifierCollection,
        ProductId $productId
    ): array {
        if (count($identifierCollection)) {
            
            $result = [];
            
            foreach ($identifierCollection as $identifier) {
                
                if ($identifier->type() === 'attribute') {
                    
                    $result[] = $identifier->value();
                }
            }
            if (count($result)) {
                
                return $result;
            }
        }
        
        throw $this->noAttributeOptionValuesIdInModifierCollectionFoundException($productId);
    }
    
    
    /**
     * @param ProductId $productId
     *
     * @return NoAttributeOptionValuesIdInModifierCollectionFoundException
     */
    protected function noAttributeOptionValuesIdInModifierCollectionFoundException(
        ProductId $productId
    ): NoAttributeOptionValuesIdInModifierCollectionFoundException {
        
        return new NoAttributeOptionValuesIdInModifierCollectionFoundException('No Attribute options ids found for Product '
                                                                               . $productId->value());
    }
}