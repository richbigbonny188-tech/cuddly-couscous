<?php
/*------------------------------------------------------------------------------
 PropertyReader.php 2020-12-01
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Database\Readers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\Properties\Database\Criterias\CheckStockBeforeShoppingCartCriteria;
use Gambio\Shop\Properties\Database\Criterias\CheckStockCriteria;
use Gambio\Shop\Properties\Database\Exceptions\CombinationNotFoundException;
use Gambio\Shop\Properties\Database\Exceptions\IncompletePropertyListException;
use Gambio\Shop\Properties\Database\Exceptions\ProductDoesntHavePropertiesException;
use Gambio\Shop\Properties\Database\Readers\Interfaces\PropertyReaderInterface;
use Gambio\Shop\Properties\ProductModifiers\Database\ValueObjects\PropertyModifierIdentifier;
use Gambio\Shop\Properties\Properties\Builders\CombinationBuilderInterface;
use Gambio\Shop\Properties\Properties\Collections\CombinationCollection;
use Gambio\Shop\Properties\Properties\Collections\CombinationCollectionInterface;
use Gambio\Shop\Properties\Properties\Entities\Combination;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationEan;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationId;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationModel;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationOrder;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationQuantity;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationSurcharge;
use Gambio\Shop\Properties\Properties\ValueObjects\CombinationWeight;
use Gambio\Shop\Properties\Properties\ValueObjects\ShippingStatus;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Vpe;

/**
 * Class PropertyReader
 *
 * @package Gambio\Shop\Properties\Database\Readers
 */
class PropertyReader implements PropertyReaderInterface
{
    
    /**
     * @var array
     */
    protected $cache = [
        'getCombinationModifierIds' => [],
        'hasProperties'             => []
    ];
    
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var CombinationBuilderInterface
     */
    private $builder;
    /**
     * @var CheckStockBeforeShoppingCartCriteria
     */
    private $checkStockBeforeShoppingCartCriteria;
    /**
     * @var CheckStockCriteria
     */
    private $checkStockCriteria;
    
    
    /**
     * PropertyReader constructor.
     *
     * @param Connection                           $connection
     * @param CombinationBuilderInterface          $builder
     * @param CheckStockBeforeShoppingCartCriteria $checkStockBeforeShoppingCartCriteria
     * @param CheckStockCriteria                   $checkStockCriteria
     */
    public function __construct(
        Connection $connection,
        CombinationBuilderInterface $builder,
        CheckStockBeforeShoppingCartCriteria $checkStockBeforeShoppingCartCriteria,
        CheckStockCriteria $checkStockCriteria
    ) {
        $this->connection                           = $connection;
        $this->builder                              = $builder;
        $this->checkStockBeforeShoppingCartCriteria = $checkStockBeforeShoppingCartCriteria;
        $this->checkStockCriteria                   = $checkStockCriteria;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCombinationModifierIds(int $combinationId): iterable
    {
        
        
        $result  = [];
        $records = $this->connection->createQueryBuilder()
            ->select('distinct products_id, properties_values_id')
            ->from('products_properties_index')
            ->where('products_properties_index.products_properties_combis_id = :combination_id')
            ->setParameter('combination_id', $combinationId)
            ->execute()
            ->fetchAll();
        foreach ($records as $row) {
            if (empty($result[(int)$row['products_id']])) {
                $result[(int)$row['products_id']] = [];
            }
            $result[(int)$row['products_id']][] = (int)$row['properties_values_id'];
        }
        
        return $result;
    }
    
    
    /**
     * @param int    $productId
     * @param string $whereAnd
     * @param string $having
     * @param int    $limit
     *
     * @return string
     */
    protected function createQuery(int $productId, string $whereAnd = '', string $having = '', int $limit = 2): string
    {
        return "
            SELECT `ppc`.`products_properties_combis_id`,
                   `ppc`.`products_id`,
                   `ppc`.`sort_order`,
                   `ppc`.`combi_model`,
                   `ppc`.`combi_weight`,
                   `ppc`.`combi_ean`,
                   `ppc`.`combi_quantity`,
                   `ppc`.`combi_shipping_status_id`,
                   `ppc`.`combi_price_type`,
                   `ppc`.`combi_price`,
                   `ppc`.`products_vpe_id`,
                   `ppc`.`vpe_value`,
                   `pvpe`.`products_vpe_name`,
                   `p`.`gm_show_weight`,
                   `p`.`use_properties_combis_weight`,
                   `p`.`use_properties_combis_quantity`,
                   `p`.`use_properties_combis_shipping_time`,
                   `ppc`.`combi_shipping_status_id`,
                   `p`.`products_quantity`
            FROM `products` AS `p`
            INNER JOIN (SELECT `ppc`.*
                         FROM `products_properties_combis` AS `ppc`
                         WHERE `ppc`.`products_id` = {$productId}
                         ORDER BY `ppc`.`products_id`, `ppc`.`combi_price`
                        ) `ppc`
            INNER JOIN `products_properties_combis_values` AS `ppcvx2`
                ON `ppc`.`products_properties_combis_id` = `ppcvx2`.`products_properties_combis_id`
            LEFT JOIN `products_vpe` as `pvpe`
             ON `ppc`.`products_vpe_id` = `pvpe`.`products_vpe_id`
             AND `pvpe`.`language_id` = {$this->activeLanguageId()}
             WHERE p.`products_id` = {$productId}
            {$whereAnd}
            GROUP BY
                `ppc`.`products_properties_combis_id`,
                   `ppc`.`products_id`,
                   `ppc`.`sort_order`,
                   `ppc`.`combi_model`,
                   `ppc`.`combi_weight`,
                   `ppc`.`combi_ean`,
                   `ppc`.`combi_quantity`,
                   `ppc`.`combi_shipping_status_id`,
                   `ppc`.`combi_price_type`,
                   `ppc`.`combi_price`,
                   `ppc`.`products_vpe_id`,
                   `ppc`.`vpe_value`,
                   `pvpe`.`products_vpe_name`,
                   `ppc`.`vpe_value`,
                   `p`.`gm_show_weight`,
                   `p`.`products_quantity`,
                   `p`.`use_properties_combis_weight`,
                   `p`.`use_properties_combis_quantity`,
                   `p`.`use_properties_combis_shipping_time`,
                   `ppc`.`combi_shipping_status_id`,
                   `p`.`products_quantity`
            {$having}
            ORDER BY `ppc`.`products_id`, `ppc`.`combi_price`
            LIMIT {$limit}
            ";
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCombinationsFor(SellingUnitId $id, int $limit = 2): CombinationCollectionInterface
    {
        $result = new CombinationCollection();
        
        [$whereAnd, $having] = $this->filterProperties($id);
        $sql  = $this->createQuery($id->productId()->value(), $whereAnd, $having, $limit);
        $data = $this->connection->query($sql)->fetchAll();
        
        $nonLinearSurcharge = (count($data) > 0) ? $this->hasNonLinearSurcharge($id->productId()->value()) : false;
        
        foreach ($data as $row) {
            $result[] = $this->createCombination($row, $nonLinearSurcharge);
        }
        
        return $result;
    }
    
    
    /**
     * @param ProductId $productId
     *
     * @return int
     */
    protected function getNumberOfProperties(ProductId $productId): int
    {
        
        $records = $this->connection->createQueryBuilder()
            ->select('count(distinct ppi.properties_id) AS total')
            ->from('products_properties_index', 'ppi')
            ->where('ppi.products_id = :products_id')
            ->setParameter('products_id', $productId->value())
            ->setMaxResults(1)
            ->execute()
            ->fetchAll();
        
        foreach ($records as $row) {
            return (int)$row['total'];
        }
        
        return 0;
    }
    
    
    /**
     * @inheritDoc
     */
    public function hasProperties(ProductId $productId): bool
    {
        return $this->getNumberOfProperties($productId) > 0;
    }
    
    
    /**
     * @param string $type
     * @param float  $surcharge
     * @param bool   $nonLinear
     *
     * @return CombinationSurcharge|null
     */
    protected function createSurcharge(string $type, float $surcharge, bool $nonLinear): ?CombinationSurcharge
    {
        if (($surcharge && $type === 'calc') || $nonLinear) {
            return new CombinationSurcharge($surcharge, $nonLinear);
        }
        
        return null;
    }
    
    
    /**
     * @param int    $id
     * @param string $name
     * @param float  $value
     *
     * @return Vpe|null
     */
    protected function createVpe(int $id, string $name, float $value): ?Vpe
    {
        if ($id && $name && $value) {
            return new Vpe($id, $name, $value);
        }
        
        return null;
    }
    
    
    /**
     * @param int $productId
     *
     * @return bool
     */
    protected function hasNonLinearSurcharge(int $productId): bool
    {
        $result = $this->hasSurcharge($productId);
        if ($result) {
            $sql = "SELECT
                    round((p.products_price + ppc.combi_price) / ppc.vpe_value,2) AS base_price
                FROM
                    products AS p
                LEFT JOIN
                    products_properties_combis AS ppc ON (ppc.products_id = p.products_id)
                WHERE
                    p.products_id = {$productId}
                    AND ppc.vpe_value != 0
                GROUP BY
                    base_price";
            try {
                $data      = $this->connection->query($sql)->fetchAll();
                $countData = count($data);
                $result    = $countData > 1 || $countData === 0;
            } catch (DBALException $e) {
                echo $e->getMessage();
            }
        }
        
        return $result;
    }
    
    
    /**
     * @param int $productId
     *
     * @return bool
     * @throws
     */
    protected function hasSurcharge(int $productId): bool
    {
        $sql = "SELECT DISTINCT
                    combi_price
                FROM
                    products_properties_combis
                WHERE
                    products_id = {$productId}
                GROUP BY
                    combi_price";
        try {
            return count($this->connection->query($sql)->fetchAll()) > 1;
        } catch (DBALException $e) {
            return true;
        }
    }
    
    
    /**
     * @return int
     */
    protected function activeLanguageId(): int
    {
        return (int)$_SESSION['languages_id'];
    }
    
    
    /**
     * @inheritDoc
     */
    public function getCombinationFor(SellingUnitId $id): ?Combination
    {
        if (!$this->hasProperties($id->productId())) {
            throw new ProductDoesntHavePropertiesException();
        }
        
        $list = [];
        foreach ($id->modifiers() as $modifier) {
            if ($modifier instanceof PropertyModifierIdentifier) {
                $list[] = $modifier;
            }
        }
        if (count($list) == $this->getNumberOfProperties($id->productId())) {
            $result = $this->getCombinationsFor($id, 1);
            if (empty($result)) {
                throw new CombinationNotFoundException();
            }
            
            return $result[0];
        } else {
            throw new IncompletePropertyListException();
        }
        
        return null;
    }
    
    
    /**
     * @param array $row
     * @param bool  $nonLinearSurcharge
     *
     * @param bool  $cheapest
     *
     * @return Combination
     */
    protected function createCombination(array $row, bool $nonLinearSurcharge, bool $cheapest = false): Combination
    {
        $this->builder->withId(new CombinationId($row['products_properties_combis_id']))
            ->withOrder(new CombinationOrder((int)$row['sort_order']))
            ->withModel(new CombinationModel($row['combi_model']))
            ->withEan(new CombinationEan($row['combi_ean']))
            ->withSurcharge($this->createSurcharge($row['combi_price_type'],
                                                   (float)$row['combi_price'],
                                                   $nonLinearSurcharge))
            ->withVpe($this->createVpe((int)$row['products_vpe_id'],
                                       (string)$row['products_vpe_name'],
                                       (float)$row['vpe_value']))
            ->withWeight(new CombinationWeight($row['combi_weight'],
                                               (int)$row['gm_show_weight'] > 0,
                                               (int)$row['use_properties_combis_weight'] === 1));
        if ((int)$row['use_properties_combis_shipping_time'] && (int)$row['combi_shipping_status_id']) {
            $this->builder->withShippingStatus(new ShippingStatus($row['combi_shipping_status_id']));
        }
        
        try {
            
            
            if ($this->checkStockCriteria->getSourceConfiguration((int)$row['use_properties_combis_quantity'],
                                                                  false,
                                                                  true)) {
                $this->builder->withQuantity(new CombinationQuantity($row['combi_quantity']));
            }
        } catch (\InvalidArgumentException $e) {
            //silent the message
        }
        
        if ($cheapest) {
            return $this->builder->buildCheapest();
        } else {
            return $this->builder->build();
        }
    }
    
    
    /**
     * @param int $productId
     *
     * @return int
     * @throws DBALException
     */
    protected function getCombinationStockConfiguration(int $productId): int
    {
        $sql  = "SELECT `use_properties_combis_quantity` FROM `products` WHERE `products_id` = $productId";
        $data = $this->connection->query($sql)->fetchAll();
        
        return (int)$data[0]['use_properties_combis_quantity'];
    }
    
    
    /**
     * @param SellingUnitId $id
     *
     * @return mixed|void
     * @throws DBALException
     */
    public function getCheapestCombinationFor(SellingUnitId $id): ?Combination
    {
        if (!$this->hasProperties($id->productId())) {
            return null;
        }
        
        [$propertyFilter, $having] = $this->filterProperties($id);
        $stockFilter = $this->filterStock($id);
        
        $sql = $this->createQuery($id->productId()->value(),
                                  $propertyFilter . $stockFilter,
                                  $having,
                                  2);
        
        $data = $this->connection->query($sql)->fetchAll();
        
        if(count($data) === 0) {
            $sql = $this->createQuery($id->productId()->value(),
                                      $propertyFilter,
                                      $having,
                                      2);

            $data = $this->connection->query($sql)->fetchAll();
        }
        
        $nonLinearSurcharge = (count($data) > 0) ? $this->hasNonLinearSurcharge($id->productId()->value()) : false;
        
        foreach ($data as $row) {
            return $this->createCombination($row, $nonLinearSurcharge, $this->isCheapest($id));
        }
        
        return null;
    }
    
    
    /**
     * @param SellingUnitId $id
     *
     * @return bool
     */
    protected function isCheapest(SellingUnitId $id): bool
    {
        $list = [];
        foreach ($id->modifiers() as $modifier) {
            if ($modifier instanceof PropertyModifierIdentifier) {
                $list[] = $modifier;
            }
        }
        
        return count($list) != $this->getNumberOfProperties($id->productId());
    }
    
    
    /**
     * @param SellingUnitId $id
     *
     * @return string[]
     */
    protected function filterProperties(SellingUnitId $id)
    {
        $whereAnd       = '';
        $having         = '';
        $propertyIdList = [];
        foreach ($id->modifiers() as $modifierId) {
            if ($modifierId instanceof PropertyModifierIdentifier) {
                $propertyIdList[] = $modifierId->value();
            }
        }
        $listCount = count($propertyIdList);
        if ($listCount) {
            $inList   = implode(',', $propertyIdList);
            $whereAnd = "\n AND ppcvx2.properties_values_id IN ($inList)";
            $having   = "\n HAVING COUNT(DISTINCT ppcvx2.products_properties_combis_values_id) = $listCount";
        }
        
        return [$whereAnd, $having];
    }
    
    
    /**
     * @param SellingUnitId $id
     *
     * @return string
     * @throws DBALException
     */
    protected function filterStock(SellingUnitId $id)
    {
        $stockFilter = '';
        
        $configuration = $this->getCombinationStockConfiguration($id->productId()->value());
        if ($this->checkStockBeforeShoppingCartCriteria->checkStockForCombinationConfiguration($configuration)) {
            $stockField  = $this->checkStockBeforeShoppingCartCriteria->getStockSourceConfiguration($configuration,
                                                                                                    '`p`.`products_quantity`',
                                                                                                    '`ppc`.`combi_quantity`');
            $stockFilter = "\n AND coalesce($stockField,0) > 0";
        }
        
        return $stockFilter;
    }
}
