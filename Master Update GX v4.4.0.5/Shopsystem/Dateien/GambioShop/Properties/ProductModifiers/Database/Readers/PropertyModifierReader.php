<?php
/*------------------------------------------------------------------------------
 PropertyModifierReader.php 2020-11-19
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);


namespace Gambio\Shop\Properties\ProductModifiers\Database\Readers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOBuilderInterface;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOCollection;
use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOCollectionInterface;
use Gambio\Shop\Properties\ProductModifiers\Database\ValueObjects\PropertyGroupIdentifier;
use Gambio\Shop\Properties\ProductModifiers\Database\ValueObjects\PropertyModifierIdentifier;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

/**
 * Class PropertyModifierReader
 *
 * @package  Gambio\Shop\Properties\ProductModifiers\Database\Readers
 * @codeCoverageIgnore
 * @internal currently untestable due to the usage of the CI_DB_query_builder
 * documentation about stock rules can be found at:
            https://sources.gambio-server.net/gambio/gxdev/-/wikis/Stock-management
 */
class PropertyModifierReader implements PropertyModifierReaderInterface
{
    
    /**
     * @var ModifierDTOBuilderInterface
     */
    protected $builder;
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var bool
     */
    private $stockCheck;
    /**
     * @var bool
     */
    private $attributeStockCheck;
    /**
     * @var bool
     */
    private $stockAllowCheckout;
    /**
     * @var bool
     */
    protected $checkStockBeforeShoppingCart;
    
    
    /**
     * PropertyModifierReader constructor.
     *
     * @param Connection                  $connection
     * @param ModifierDTOBuilderInterface $builder
     * @param bool                        $stockCheck
     * @param bool                        $attributeStockCheck
     * @param bool                        $stockAllowCheckout
     * @param bool                        $checkStockBeforeShoppingCart
     */
    public function __construct(
        Connection $connection,
        ModifierDTOBuilderInterface $builder,
        bool $stockCheck,
        bool $attributeStockCheck,
        bool $stockAllowCheckout,
        bool $checkStockBeforeShoppingCart
    ) {
        $this->connection                   = $connection;
        $this->builder                      = $builder;
        $this->stockCheck                   = $stockCheck;
        $this->attributeStockCheck          = $attributeStockCheck;
        $this->stockAllowCheckout           = $stockAllowCheckout;
        $this->checkStockBeforeShoppingCart = $checkStockBeforeShoppingCart;
    }
    
    
    /**
     * @param float $priceValueFloat
     *
     * @return mixed[]
     */
    protected function pricePrefixAndValueFromFloat(float $priceValueFloat): array
    {
        $pricePrefix     = $priceValueFloat >= 0 ? '+ ' : '- ';
        $priceValueFloat = $pricePrefix === '- ' ? $priceValueFloat * -1 : $priceValueFloat;
        
        return [$pricePrefix, $priceValueFloat];
    }
    
    
    /**
     * @param SellingUnitId $id
     *
     * @return array
     * @throws DBALException
     */
    protected function getProductPropertiesConfiguration(SellingUnitId $id) {
        $selectedPropertyValues = $this->selectedPropertyValues($id);
        $indexedSelected = [];
        $result = [
            'selected_values' => [],
            'use_properties_combis_quantity' => '',
            'properties_dropdown_mode' => '',
            'properties' => []
        ];
        
        $sql ="
                SELECT `products`.`use_properties_combis_quantity`
                        , `products`.`properties_dropdown_mode`
                        , `p`.`properties_id`
                        , `p`.`display_type`
                        , `pv`.`properties_values_id`
                        , `pv`.`display_image`
                        , `pv`.`value_price`
                        , `pvd`.`values_name`
                FROM `products`
                     INNER JOIN (
                            SELECT DISTINCT `ppi`.`products_id`, `ppi`.`properties_values_id`, `ppi`.`properties_id`
                            FROM `products_properties_index` `ppi`
                            WHERE `ppi`.products_id = {$id->productId()->value()}
                     ) `ppi`
                         ON `ppi`.`products_id` = `products`.`products_id`
                     INNER JOIN `properties_values` `pv`
                         ON `pv`.`properties_values_id` = `ppi`.`properties_values_id`
                     INNER JOIN `properties_values_description` `pvd`
                         ON `pvd`.`properties_values_id` = `pv`.`properties_values_id`
                        and `pvd`.`language_id` = {$id->language()->value()}
                     INNER JOIN `properties` `p`
                         ON `p`.`properties_id` = `ppi`.`properties_id`
                WHERE `products`.`products_id` = {$id->productId()->value()}
                ORDER BY `p`.`sort_order`, `p`.`properties_id`, pv.`sort_order`, pv.`properties_values_id`";
        $data = $this->connection->query($sql);
        while ($record = $data->fetch()) {
            $result['use_properties_combis_quantity'] = (int)$record['use_properties_combis_quantity'];
            $result['properties_dropdown_mode'] = $record['properties_dropdown_mode'];
            if(!isset($result['properties'][$record['properties_id']])) {
                $result['properties'][$record['properties_id']] = [
                    'values'       => [],
                    'display_type' => $record['display_type'],
                    'selected'     => false
                ];
            }
            $result['properties'][$record['properties_id']]['values'][(int)$record['properties_values_id']] = [
                'display_image' => $record['display_image'],
                'value_price'   => (double)$record['value_price'],
                'values_name'   => $record['values_name'],
                'selectable'   => $result['properties_dropdown_mode'] == '',
                'selected'     => false
            ];

            if(in_array((int)$record['properties_values_id'], $selectedPropertyValues)) {
                $result['properties'][$record['properties_id']]['values'][(int)$record['properties_values_id']]['selected'] = true;
                $result['properties'][$record['properties_id']]['selected'] = true;
                $indexedSelected[(int)$record['properties_id']] = (int)(int)$record['properties_values_id'];
            }
        }
        $result['selected_values'] = $indexedSelected;
        return $result;
    }
    
    
    /**
     * @inheritDoc
     * @throws DBALException
     */
    public function getModifierBySellingUnit(SellingUnitId $id): ModifierDTOCollectionInterface
    {
        $result    = new ModifierDTOCollection();
        try {
            $configuration = $this->getProductPropertiesConfiguration($id);
        } catch (DBALException $e) {
            $configuration = [];
        }
        if (!count($configuration) || !count($configuration['properties'])) {
            return $result;
        }

        $validValues = [];
        if($configuration['properties_dropdown_mode']) {
            $validValues = $this->getSelectablePropertyValues(
                $id->productId()->value(),
                $configuration,
                $id->language()->value()
            );
        }
        
        foreach ($configuration['properties'] as $property_id => $property) {
            foreach ($property['values'] as $properties_values_id => $value) {
        
        
                [$pricePrefix, $priceValueFloat] = $this->pricePrefixAndValueFromFloat((float)$value['value_price']);
        
                $propertyId = new PropertyModifierIdentifier($properties_values_id);
        
                $result->addModifier($this->builder->withId($propertyId)
                                         ->withGroupId(new PropertyGroupIdentifier($property_id))
                                         ->withType($property['display_type'])
                                         ->withName($value['values_name'])
                                         ->withSelectable($value['selectable'] || in_array($properties_values_id, $validValues))
                                         ->withSelected($value['selected'])
                                         ->withPrice($priceValueFloat)
                                         ->withPricePrefix($pricePrefix)
                                         ->withImage($value['display_image'] ? 'product_images/property_images/'
                                                                              . $value['display_image'] : '')
                                         ->withSource('property')
                                         ->build());
            }
        }
        
        return $result;
    }
    
    
    
    /**
     * @param int $configuration
     *
     * @return string
     */
    public function stockField(int $configuration) : ?string {
        switch ($configuration) {
            case 0:
                return $this->attributeStockCheck ? '`ppc`.`combi_quantity`' : '`products`.`products_quantity`';
            case 1:
                return '`products`.`products_quantity`';
            case 2:
                return '`ppc`.`combi_quantity`';
            default:
                return '';
        }
    }
    
    protected function stockCheck(int $configuration): bool
    {
        /**
         * 0 = Use General configuration
         * 1 = Use product Quantity
         * 2 = Use combination configuration
         * 3 = Dont check stock
         */
        switch ($configuration) {
            case 0:
            case 1:
            case 2:
                return $this->stockCheck && $this->checkStockBeforeShoppingCart && !$this->stockAllowCheckout;
            default:
                return false;
        }
    }
    
    
    /**
     * @param int   $productId
     * @param array $configuration
     *
     * @param int   $languageId
     *
     * @return array
     * @throws DBALException
     */
    protected function getSelectablePropertyValues(int $productId, array $configuration, int $languageId): array
    {
        $stockSql = "";
        $mainSql  = "
            SELECT DISTINCT `ppi`.`properties_id`, `ppi`.`properties_values_id`
            FROM `products_properties_index` `ppi`
        ";
        
        if($this->stockCheck($configuration['use_properties_combis_quantity'])
           && $stockField = $this->stockField($configuration['use_properties_combis_quantity'])) {
            $mainSql.="
            INNER JOIN `products` ON `products`.`products_id` = `ppi`.`products_id`
            INNER JOIN `products_properties_combis` ppc ON `ppc`.`products_properties_combis_id` = `ppi`.`products_properties_combis_id`
            WHERE `ppi`.`products_id` = {$productId}
            AND `ppi`.`language_id` = {$languageId}
            AND {$stockField} >= products.gm_min_order";
        } else {
            $mainSql.="\nWHERE `ppi`.`products_id` ={$productId}";
        }

        //there there is more than 1 property selected and only valid options are selectable
        if(count($configuration['selected_values'])) {
            $data = [];
            foreach ($configuration['properties'] as $id=>$property) {
                $sql = $mainSql."\nAND `ppi`.`properties_id` = {$id}";
                $selectedValues = $configuration['selected_values'];
                unset($selectedValues[$id]);
                if(count($selectedValues)) {
                    $having = count($selectedValues);
                    $sql .= "\nAND `ppi`.`products_properties_combis_id` IN (
                            SELECT `ppi2`.`products_properties_combis_id`
                            FROM `products_properties_index` `ppi2`
                            WHERE `ppi2`.`products_id` = {$productId}
                            AND `ppi2`.`language_id` = {$languageId}
                            AND   `ppi2`.`properties_values_id` in (" . implode(',', $selectedValues) . ")
                            GROUP BY `ppi2`.`products_properties_combis_id`
                            HAVING (COUNT(`ppi2`.`properties_values_id`) = $having)
                    )";
                }
                $records = $this->connection->query($sql);
                $data[] = $records->fetchAll(0);
            }

            $data = array_merge(...$data);
            
        } else {
            $data = $this->connection->query($mainSql)->fetchAll(0);
        }
    
        
        $specificOrder = $configuration['properties_dropdown_mode'] == 'dropdown_mode_2';
        $current = null;
        $selectablePropertyValues = [];
        $previousWasSelected = true;//start as true, meaning that the first option is always enabled
        $currentIsSelected = false;//start as false
        foreach ($data as $row) {
            $row = array_map('intval', $row);
            if($current === null){
                $current = $row['properties_id'];
            }

            if($row['properties_id'] !== $current) {
                $current =     $row['properties_id'];
                $previousWasSelected = $currentIsSelected;
            }
            $currentIsSelected = $currentIsSelected || in_array($row['properties_values_id'], $configuration['selected_values']);
            
            if($previousWasSelected  || $specificOrder === false) {
                $selectablePropertyValues[] = $row['properties_values_id'];
            }
        }

        return array_values($selectablePropertyValues);
    }
    
    protected function selectedPropertyValues(SellingUnitId $id)
    {
        $result = [];
        foreach ($id->modifiers() as $modifier){
            if($modifier instanceof PropertyModifierIdentifier)
                $result[] = $modifier->value();
            }
        return $result;
    }
    
}