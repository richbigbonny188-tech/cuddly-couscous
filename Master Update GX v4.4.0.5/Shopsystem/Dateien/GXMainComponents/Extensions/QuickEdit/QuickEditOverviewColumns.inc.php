<?php

/* --------------------------------------------------------------
   QuickEditOverviewColumns.inc.php 2020-12-18
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2017 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class QuickEditOverviewColumns
 *
 * @category   System
 * @package    Extensions
 * @subpackage QuickEdit
 */
class QuickEditOverviewColumns implements QuickEditOverviewColumnsInterface
{
    /**
     * @var CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var array
     */
    protected $columns = [];
    
    /**
     * @var LanguageTextManager
     */
    protected $languageTextManager;
    
    
    /**
     * QuickEditOverviewColumns constructor.
     */
    public function __construct()
    {
        $this->db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        
        $this->languageTextManager = MainFactory::create('LanguageTextManager',
                                                         'admin_quick_edit',
                                                         $_SESSION['languages_id']);
        
        // ID
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('ID')))
            ->setName(new StringType('id'))
            ->setField(new StringType('products.products_id'))
            ->setType(new DataTableColumnType(DataTableColumnType::NUMBER));
        
        // Category
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('CATEGORY')))
            ->setName(new StringType('category'))
            ->setField(new StringType('categories_description.categories_name'))
            ->setType(new DataTableColumnType(DataTableColumnType::STRING))
            ->setOptions($this->_getCategoriesConfiguration());
        
        // Manufacturer
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('MANUFACTURER')))
            ->setName(new StringType('manufacturer'))
            ->setField(new StringType('products.manufacturers_id'))
            ->setType(new DataTableColumnType(DataTableColumnType::STRING))
            ->setOptions($this->_getManufacturerConfiguration());
        
        // Name
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('NAME')))
            ->setName(new StringType('name'))
            ->setField(new StringType('products_description.products_name'))
            ->setType(new DataTableColumnType(DataTableColumnType::STRING));
        
        // Model
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('MODEL')))
            ->setName(new StringType('model'))
            ->setField(new StringType('products.products_model'))
            ->setType(new DataTableColumnType(DataTableColumnType::STRING));
        
        // Quantity
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('QUANTITY')))
            ->setName(new StringType('quantity'))
            ->setField(new StringType('products.products_quantity'))
            ->setType(new DataTableColumnType(DataTableColumnType::NUMBER));
        
        // Price
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('PRICE')))
            ->setName(new StringType('price'))
            ->setField(new StringType('products.products_price'))
            ->setType(new DataTableColumnType(DataTableColumnType::NUMBER));
        
        // Discount
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('DISCOUNT')))
            ->setName(new StringType('discount'))
            ->setField(new StringType('products.products_discount_allowed'))
            ->setType(new DataTableColumnType(DataTableColumnType::NUMBER));
        
        // Special price
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('SPECIAL_PRICE')))
            ->setName(new StringType('specialPrice'))
            ->setField(new StringType('specials.specials_new_products_price'))
            ->setType(new DataTableColumnType(DataTableColumnType::NUMBER));
        
        // Tax
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('TAX')))
            ->setName(new StringType('tax'))
            ->setField(new StringType('products.products_tax_class_id'))
            ->setType(new DataTableColumnType(DataTableColumnType::STRING))
            ->setOptions($this->_getTaxConfiguration());
        
        // Shipping time
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('SHIPPINGTIME')))
            ->setName(new StringType('shippingStatusName'))
            ->setField(new StringType('products.products_shippingtime'))
            ->setType(new DataTableColumnType(DataTableColumnType::STRING))
            ->setOptions($this->_getShipmentConfiguration());
        
        // Weight
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('WEIGHT')))
            ->setName(new StringType('weight'))
            ->setField(new StringType('products.products_weight'))
            ->setType(new DataTableColumnType(DataTableColumnType::NUMBER));
        
        // Shipping costs
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('SHIPPINGCOSTS')))
            ->setName(new StringType('shippingCosts'))
            ->setField(new StringType('products.nc_ultra_shipping_costs'))
            ->setType(new DataTableColumnType(DataTableColumnType::NUMBER));
        
        // Status
        $this->columns[] = MainFactory::create('DataTableColumn')
            ->setTitle(new StringType($this->languageTextManager->get_text('STATUS')))
            ->setName(new StringType('status'))
            ->setField(new StringType('products.products_status'))
            ->setType(new DataTableColumnType(DataTableColumnType::STRING))
            ->setOptions($this->_getStatusConfiguration());
    }
    
    
    /**
     * Get the DataTableColumnCollection of the table.
     *
     * @return DataTableColumnCollection Returns the DataTableColumnCollection of the table.
     */
    public function getColumns()
    {
        return MainFactory::create('DataTableColumnCollection', $this->columns);
    }
    
    
    /**
     * Serializes the data of a table column.
     *
     * @return array Returns the serialized table column data.
     */
    public function serializeColumns()
    {
        return array_map(function ($column) {
            /** @var DataTableColumn $column */
            return [
                'title'   => $column->getTitle(),
                'name'    => $column->getName(),
                'field'   => $column->getField(),
                'type'    => $column->getType(),
                'source'  => $column->getSource(),
                'options' => $column->getOptions(),
                'tooltip' => $this->_getTooltipValues($column->getName())
            ];
        },
            $this->columns);
    }
    
    
    /**
     * Returns the text phrase for the desired value.
     *
     * @param string $name identifier of the wanted text phrase.
     *
     * @return array Returns the text phrase for the desired value.
     */
    protected function _getTooltipValues($name)
    {
        $tooltipsValues = [
            'filter' => [
                'category'      => $this->languageTextManager->get_text('TOOLTIP_FILTER_CATEGORY'),
                'name'          => $this->languageTextManager->get_text('TOOLTIP_FILTER_NAME'),
                'model'         => $this->languageTextManager->get_text('TOOLTIP_FILTER_MODEL'),
                'quantity'      => $this->languageTextManager->get_text('TOOLTIP_FILTER_QUANTITY'),
                'price'         => $this->languageTextManager->get_text('TOOLTIP_FILTER_PRICE'),
                'discount'      => $this->languageTextManager->get_text('TOOLTIP_FILTER_DISCOUNT'),
                'specialPrice'  => $this->languageTextManager->get_text('TOOLTIP_FILTER_SPECIAL_PRICE'),
                'tax'           => $this->languageTextManager->get_text('TOOLTIP_FILTER_TAX'),
                'shippingTime'  => $this->languageTextManager->get_text('TOOLTIP_FILTER_SHIPPING_TIME'),
                'weight'        => $this->languageTextManager->get_text('TOOLTIP_FILTER_WEIGHT'),
                'shippingCosts' => $this->languageTextManager->get_text('TOOLTIP_FILTER_SHIPPING_COSTS'),
            ],
            'edit'   => [
                'quantity'      => $this->languageTextManager->get_text('TOOLTIP_EDIT_QUANTITY'),
                'price'         => $this->languageTextManager->get_text('TOOLTIP_EDIT_PRICE'),
                'discount'      => $this->languageTextManager->get_text('TOOLTIP_EDIT_DISCOUNT'),
                'specialPrice'  => $this->languageTextManager->get_text('TOOLTIP_EDIT_SPECIAL_PRICE'),
                'weight'        => $this->languageTextManager->get_text('TOOLTIP_EDIT_WEIGHT'),
                'shippingCosts' => $this->languageTextManager->get_text('TOOLTIP_EDIT_SHIPPING_COSTS'),
            ]
        ];
        
        return [
            'filter' => array_key_exists($name, $tooltipsValues['filter']) ? $tooltipsValues['filter'][$name] : '',
            'edit'   => array_key_exists($name, $tooltipsValues['edit']) ? $tooltipsValues['edit'][$name] : ''
        ];
    }
    
    
    /**
     * Returns a list of all status names and status identifiers.
     *
     * @return array Returns a list of all status names and status identifiers.
     */
    protected function _getStatusConfiguration()
    {
        return [
            [
                'id'    => 1,
                'value' => $this->languageTextManager->get_text('ACTIVE')
            ],
            [
                'id'    => 0,
                'value' => $this->languageTextManager->get_text('INACTIVE')
            ]
        ];
    }
    
    
    /**
     * Returns a list of all shipment names and shipment identifiers.
     *
     * @return array Returns a list of all shipment names and shipment identifiers.
     */
    protected function _getShipmentConfiguration()
    {
        $result = $this->db->select(['shipping_status_id', 'shipping_status_name'])
            ->where('language_id',
                    $_SESSION['languages_id'])
            ->get('shipping_status')
            ->result_array();
        
        $shipmentConfiguration = array_map(function ($value) {
            return [
                'id'    => $value['shipping_status_id'],
                'value' => $value['shipping_status_name']
            ];
        },
            $result);
        
        array_unshift($shipmentConfiguration,
                      [
                          'id'    => 0,
                          'value' => TEXT_NONE
                      ]);
        
        return $shipmentConfiguration;
    }
    
    
    /**
     * Returns a list of all tax names and tax identifiers.
     *
     * @return array Returns a list of all tax names and tax identifiers.
     */
    protected function _getTaxConfiguration()
    {
        $result = $this->db->select(['tax_class.tax_class_id', 'tax_class_title', 'tax_rate'])
            ->join('tax_class',
                   'tax_class_id',
                   'inner',
                   'USING')
            ->join('zones_to_geo_zones',
                   'zones_to_geo_zones.zone_country_id=' . (int)STORE_COUNTRY . '',
                   'inner')
            ->where('tax_zone_id=geo_zone_id')
            ->get('tax_rates')
            ->result_array();
        
        $taxConfiguration = array_map(function ($value) {
            return [
                'id'    => $value['tax_class_id'],
                'value' => sprintf('%01.2f', $value['tax_rate']) . '% ' . $value['tax_class_title']
            ];
        },
            $result);
        
        array_unshift($taxConfiguration,
                      [
                          'id'    => 0,
                          'value' => TEXT_NONE
                      ]);
        
        return $taxConfiguration;
    }
    
    
    /**
     * Returns a list of all manufacturer names and manufacturer identifiers.
     *
     * @return array Returns a list of all manufacturer and manufacturer identifiers.
     */
    protected function _getManufacturerConfiguration()
    {
        $result = $this->db->select(['manufacturers_id', 'manufacturers_name'])->get('manufacturers')->result_array();
        
        $manufacturers = array_map(function ($value) {
            return [
                'id'    => $value['manufacturers_id'],
                'value' => $value['manufacturers_name']
            ];
        },
            $result);
        
        return array_merge([['id' => 0, 'value' => 'Ohne Angabe']], $manufacturers);
    }
    
    
    /**
     * Returns a list of all category names and category identifiers.
     *
     * @return array Returns a list of all category names and category identifiers.
     */
    protected function _getCategoriesConfiguration()
    {
        $columns = [
            'categories_description.categories_id',
            'categories_description.categories_name',
            'categories.parent_id'
        ];
        
        $result = $this->db->select($columns)
            ->join('categories',
                   'categories_description.categories_id = categories.categories_id')
            ->where('language_id', 2)
            ->order_by('categories_name', 'asc')
            ->get('categories_description')
            ->result_array();
        
        $categories = array_map(function ($value) {
            $parentCategoriesName = $this->_getParentCategoriesName($value['parent_id']);
            $parentCategoriesName = empty($parentCategoriesName) ? '' : ' (' . $parentCategoriesName . ')';
            
            return [
                'id'    => $value['categories_id'],
                'value' => $value['categories_name'] . $parentCategoriesName
            ];
        },
            $result);
    
        $uncategorized = ['id' => "0", 'value' => $this->languageTextManager->get_text('UNCATEGORIZED')];
        array_splice($categories, 0, 0, [$uncategorized]); # pushes new value to position 0 without removing original value at position 0
        
        return $categories;
    }
    
    
    /**
     * Returns the corresponding category name based on the parentId.
     *
     * @param int $parentCategoryId Parent category ID.
     *
     * @return string Returns the corresponding category name based on the parentId.
     */
    protected function _getParentCategoriesName($parentCategoryId)
    {
        $result = $this->db->select('categories_name')
            ->where('categories_id', $parentCategoryId)
            ->where('language_id',
                    2)
            ->get('categories_description')
            ->row();
        
        return $result->categories_name ?? '';
    }
}