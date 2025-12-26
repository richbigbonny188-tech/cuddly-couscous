<?php
/* --------------------------------------------------------------
   GoogleAnalyticsTrackingRepositoryReader.inc.php 2020-10-16
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class GoogleAnalyticsTrackingRepositoryReader
 */
class GoogleAnalyticsTrackingRepositoryReader implements GoogleAnalyticsTrackingRepositoryReaderInterface
{
    /**
     * @var CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var bool
     */
    protected $netPrice;
    
    /**
     * @var int
     */
    protected $taxCountryId;
    
    
    /**
     * GoogleAnalyticsTrackingRepositoryReader constructor.
     *
     * @param CI_DB_query_builder $db
     * @param BoolType            $netPrices
     */
    public function __construct(CI_DB_query_builder $db, BoolType $netPrices)
    {
        $this->db       = $db;
        $this->netPrice = $netPrices->asBool();
    }
    
    
    /**
     * Fetches impression data by the given product id.
     *
     * @param IdType $productId       Product id.
     * @param IdType $languageId      Language id.
     * @param IdType $customerGroupId Customer group id.
     *
     * @return array Data that contains impression information.
     */
    public function fetchImpression(
        IdType $productId,
        IdType $languageId,
        IdType $customerGroupId
    ) {
        $result = $this->_impressionQuerySetup($languageId, $customerGroupId)
            ->where('p.products_id',
                    $productId->asInt())
            ->get()
            ->row_array();
        if($result === null) $result = array('id'=>'','name'=>'');
        $this->_prepareImpressionResult($result,
                                        $productId->asInt(),
                                        $this->_customerGroupsMaxDiscount($customerGroupId));
        
        return $result;
    }
    
    
    /**
     * Fetches impressions data by the given product ids.
     *
     * @param IdCollection $productIds      Product ids.
     * @param IdType       $languageId      Language id.
     * @param IdType       $customerGroupId Customer group id.
     *
     * @return array Data that contains impressions information.
     */
    public function fetchImpressions(
        IdCollection $productIds,
        IdType $languageId,
        IdType $customerGroupId
    ) {
        $result = $this->_impressionQuerySetup($languageId, $customerGroupId)
            ->where_in('p.products_id',
                       $productIds->getIntArray())
            ->order_by('FIND_IN_SET(`p`.`products_id`, "' . implode(',', $productIds->getIntArray()) . '")',
                       'ASC',
                       false)
            ->get()
            ->result_array();
        
        $customerGroupMaxDiscount = $this->_customerGroupsMaxDiscount($customerGroupId);

        foreach ($result as $key => &$data) {
            $this->_prepareImpressionResult($data, $productIds->getItem($key)->asInt(), $customerGroupMaxDiscount);
        }
        
        return $result;
    }
    
    
    /**
     * Fetches product data by the given product id and optional variants.
     *
     * @param IdType                          $productId       Product Id.
     * @param IdType                          $languageId      Language Id.
     * @param IdType                          $customerGroupId Customer group Id.
     * @param GoogleAnalyticsVariantInterface $variant         Variants data if exists.
     *
     * @return array Data that contains product information.
     */
    public function fetchProductByVariant(
        IdType $productId,
        IdType $languageId,
        IdType $customerGroupId,
        GoogleAnalyticsVariantInterface $variant
    ) {
        if ($variant->both()) {
            return $this->_fetchByCombinedVariant($productId, $customerGroupId, $languageId, $variant);
        }
        
        if ($variant->propertiesOnly()) {
            return $this->_fetchByPropertyVariant($productId, $customerGroupId, $languageId, $variant);
        }
        
        if ($variant->attributesOnly()) {
            return $this->_fetchByAttributeVariant($productId, $customerGroupId, $languageId, $variant);
        }
        
        $result = $this->_productDataQuerySetup($customerGroupId, $languageId)
            ->select('p.products_model as id')
            ->where('p.products_id',
                    $productId->asInt())
            ->get()
            ->row_array();
        
        $customerGroupMaxDiscount = $this->_customerGroupsMaxDiscount($customerGroupId);
        if($result === null) $result = array('id'=>'','name'=>'');
        $this->_prepareImpressionResult($result, $productId->asInt(), $customerGroupMaxDiscount);
        $result['variant'] = null;
        
        return $result;
    }
    
    
    /**
     * Fetches product data by the given combined product id.
     *
     * @param GoogleAnalyticsCombinedProductIdInterface $combinedProductId  Id combination of product, attribute and
     *                                                                      property combination ids.
     * @param IdType                                    $languageId         Language Id.
     * @param IdType                                    $customerGroupId    Customer group Id.
     *
     * @return array Data that contains product information.
     */
    public function fetchProductByCombinedId(
        GoogleAnalyticsCombinedProductIdInterface $combinedProductId,
        IdType $languageId,
        IdType $customerGroupId
    ) {
        if ($combinedProductId->containsBoth()) {
            $attributeCombinationSubSelect = $this->_attributeCombinationSubSelect($combinedProductId->productId(),
                                                                                   $combinedProductId->attributeIds()
                                                                                       ->valueIds());
            $attributePriceSubSelect       = $this->_attributePriceSubSelect($combinedProductId->productId());
            $attributeVariantSubSelect     = $this->_attributeVariantSubSelect($combinedProductId->productId(),
                                                                               $languageId,
                                                                               $combinedProductId->attributeIds()
                                                                                   ->optionIds(),
                                                                               $combinedProductId->attributeIds()
                                                                                   ->valueIds());
            
            $variantSelection = 'CONCAT(GROUP_CONCAT(CONCAT(`ppi`.`properties_name`, ": ", `ppi`.`values_name`) ORDER BY
	                    `ppi`.`properties_sort_order` SEPARATOR ", "), ", ", `attribute_variant`.`attribute_variant_description`) as `variant`';
            
            $result = $this->db->select('CONCAT(`p`.`products_model`, "-", CONCAT(`ppc`.`combi_model`, "-", `attribute_combi`.`attribute_combi_model`)) as `id`',
                                        false)
                ->select('attribute_price_result.attributes_price as attributePrice')
                ->select('products_discount_allowed as allowedDiscount')
                ->select('m.manufacturers_name as brand')
                ->select('p.products_price as productPrice')
                ->select('s.specials_new_products_price as specialPrice')
                ->select('pd.products_name as name')
                ->select('pobcs.personal_offer as personalOffer')
                ->select('ppc.combi_price as combinationPrice')
                ->select($variantSelection, false)
                ->from('products as p')
                ->join('(' . $attributeCombinationSubSelect . ') as attribute_combi',
                       '`attribute_combi`.`products_id` = `p`.`products_id`',
                       'left',
                       false)
                ->join('(' . $attributePriceSubSelect . ') as attribute_price_result',
                       '`attribute_price_result`.`products_id` = `p`.`products_id`',
                       'left',
                       false)
                ->join('(' . $attributeVariantSubSelect . ') as `attribute_variant`',
                       '`attribute_variant`.`products_id` = `p`.`products_id`',
                       'left',
                       false)
                ->join('products_properties_combis as ppc', 'ppc.products_id = p.products_id')
                ->join('manufacturers as m',
                       'p.manufacturers_id = m.manufacturers_id',
                       'left outer')
                ->join('specials as s', 'p.products_id = s.products_id', 'left outer')
                ->join('products_description as pd',
                       'p.products_id = pd.products_id')
                ->join('personal_offers_by_customers_status_' . $customerGroupId->asInt() . ' as pobcs',
                       'p.products_id = pobcs.products_id',
                       'left outer')
                ->join('products_properties_index as ppi',
                       'ppi.products_properties_combis_id = ppc.products_properties_combis_id AND ppi.language_id = '
                       . $languageId->asInt(),
                       'left')
                ->where('p.products_id', $combinedProductId->productId())
                ->where('pd.language_id',
                        $languageId->asInt())
                ->where('ppc.products_properties_combis_id', $combinedProductId->propertyId())
                ->get()
                ->row_array();
            
            $customerGroupDiscount = $this->_customerGroupsMaxDiscount($customerGroupId);
            if($result === null) $result = array('id'=>'','name'=>'');
            $this->_prepareImpressionResult($result, $combinedProductId->productId(), $customerGroupDiscount);
            
            return $result;
        }
        
        if ($combinedProductId->containsPropertyId() && !$combinedProductId->attributeIds()) {
            $result = $this->_productDataQuerySetup($customerGroupId, $languageId)
                ->select('CONCAT(`p`.`products_model`, "-", `ppc`.`combi_model`) as `id`',
                         false)
                ->select('GROUP_CONCAT(CONCAT(`ppi`.`properties_name`, ": ", `ppi`.`values_name`) ORDER BY `ppi`.`properties_sort_order` SEPARATOR ", ") as `variant`',
                         false)
                ->select('ppc.combi_price as combinationPrice')
                ->join('products_properties_combis as ppc',
                       'ppc.products_id = p.products_id')
                ->join('`products_properties_index` as `ppi`',
                       '`ppi`.`products_properties_combis_id` = `ppc`.`products_properties_combis_id` AND `ppi`.`language_id` ='
                       . $languageId->asInt(),
                       '',
                       false)
                ->where('p.products_id', $combinedProductId->productId())
                ->where('ppc.products_properties_combis_id',
                        $combinedProductId->propertyId())
                ->group_by('ppc.products_properties_combis_id')
                ->get()
                ->row_array();
            
            $customerGroupMaxDiscount = $this->_customerGroupsMaxDiscount($customerGroupId);
            if($result === null) $result = array('id'=>'','name'=>'');
            $this->_prepareImpressionResult($result, $combinedProductId->productId(), $customerGroupMaxDiscount);
            
            return $result;
        }
        
        if ($combinedProductId->attributeIds()) {
            $paMinusSumSelect       = 'SUM(case when `paminus`.`options_values_price` IS NOT NULL then `paminus`.`options_values_price` else 0 end)';
            $paPlusSumSelect        = 'SUM(case when `paplus`.`options_values_id` IS NOT NULL then `paplus`.`options_values_price` else 0 end)';
            $combinationPriceSelect = '0 - ' . $paMinusSumSelect . ' + ' . $paPlusSumSelect . ' as `attributes_price`';
            
            $priceSubSelect = $this->db->select('p2.products_id')
                ->select($combinationPriceSelect)
                ->from('products as p2')
                ->join('products_attributes as paminus',
                       'paminus.products_id = p2.products_id AND paminus.price_prefix = "-" AND paminus.options_values_id IN ('
                       . implode(',', $combinedProductId->attributeIds()->valueIds()) . ')',
                       'left')
                ->join('products_attributes as paplus',
                       'paplus.products_id = p2.products_id AND paplus.price_prefix = "+" AND paplus.options_values_id IN ('
                       . implode(',', $combinedProductId->attributeIds()->valueIds()) . ')',
                       'left')
                ->where('p2.products_id', $combinedProductId->productId())
                ->get_compiled_select();
            
            $result = $this->_productDataQuerySetup($customerGroupId, $languageId)
                ->select('CONCAT(`p`.`products_model`, "-", GROUP_CONCAT(`pa`.`attributes_model` ORDER BY `pa`.`products_attributes_id` SEPARATOR "-")) as `id`',
                         false)
                ->select('combi_price_result.attributes_price as attributePrice')
                ->select('GROUP_CONCAT(CONCAT(`po`.`products_options_name`, ": ", `pov`.`products_options_values_name`) ORDER BY `po`.`products_options_name`, `pov`.`products_options_values_name` SEPARATOR ", ") as `variant`',
                         false)
                ->join('products_attributes as pa', 'p.products_id = pa.products_id', 'left')
                ->join('(' . $priceSubSelect . ') as combi_price_result',
                       'combi_price_result.products_id = p.products_id',
                       '',
                       false)
                ->join('products_options as po',
                       'pa.options_id = po.products_options_id AND pd.language_id = po.language_id')
                ->join('products_options_values as pov',
                       'pa.options_values_id = pov.products_options_values_id AND pd.language_id = pov.language_id')
                ->where('p.products_id', $combinedProductId->productId())
                ->where_in('pa.options_values_id',
                           $combinedProductId->attributeIds()->valueIds())
                ->group_by('combi_price_result.attributes_price')
                ->group_by('pobcs.personal_offer')
                ->get()
                ->row_array();
            
            $customerGroupDiscount = $this->_customerGroupsMaxDiscount($customerGroupId);
            if($result === null) $result = array('id'=>'','name'=>'');
            $this->_prepareImpressionResult($result, $combinedProductId->productId(), $customerGroupDiscount);
            
            return $result;
        }
        
        $result = $this->_productDataQuerySetup($customerGroupId, $languageId)
            ->select('p.products_model as id')
            ->where('p.products_id',
                    $combinedProductId->productId())
            ->get()
            ->row_array();
        
        $customerGroupMaxDiscount = $this->_customerGroupsMaxDiscount($customerGroupId);
        if($result === null) $result = array('id'=>'','name'=>'');
        $this->_prepareImpressionResult($result, $combinedProductId->productId(), $customerGroupMaxDiscount);
        $result['variant'] = null;
        
        return $result;
    }
    
    
    /**
     * Fetches purchase data by the given order id.
     *
     * @param IdType $orderId Order Id.
     *
     * @return array Data that contains purchase information.
     */
    public function fetchPurchase(IdType $orderId)
    {
        $storeName = $this->db->select('value')
                         ->from('gx_configurations')
                         ->where('key',
                                 'configuration/STORE_NAME')
                         ->get()
                         ->row_array()['value'];
        
        $shippingCostSubSelect = $this->_getCompiledOrderTotalSelect($orderId, 'ot_shipping', 'shippingCost');
        
        $result = $this->db->select('o.orders_id as transactionId')
            ->select('o.currency as currency')
            ->select($shippingCostSubSelect,
                     false)
            ->from('orders as o')
            ->where('o.orders_id', $orderId->asInt())
            ->get()
            ->row_array();
        $tax    = $this->db->select('SUM(`value`) as `tax`', false)
            ->from('orders_total')
            ->where('orders_id',
                    $orderId->asInt())
            ->where('class', 'ot_tax')
            ->get()
            ->row_array();
        
        $valueType = $this->netPrice ? 'net' : 'gross';
        $value     = $this->db->select('SUM(`' . $valueType . '`) as `value`')
            ->from('orders_tax_sum_items')
            ->where('order_id',
                    $orderId->asInt())
            ->get()
            ->row_array();
        
        if (!isset($value['value']) || !$value['value']) {
            $value = $this->db->select('value')
                ->from('orders_total')
                ->where('orders_id', $orderId->asInt())
                ->where('class',
                        'ot_total')
                ->get()
                ->row_array();
        }
        
        $result = array_merge($result, $tax, $value);
        
        $attributeModelSubSelect = $this->db->select('op.orders_products_id')
            ->select('GROUP_CONCAT(`pa`.`attributes_model` ORDER BY `pa`.`products_attributes_id` SEPARATOR "-") as `attribute_model`',
                     false)
            ->from('orders_products as op')
            ->join('orders_products_attributes as opa',
                   'op.orders_products_id = opa.orders_products_id',
                   'left outer')
            ->join('products_attributes as pa',
                   'pa.products_id = op.products_id',
                   'left outer')
            ->where('op.orders_id', $orderId->asInt())
            ->where('`pa`.`options_id`', '`opa`.`options_id`', false)
            ->where('`pa`.`options_values_id`',
                    '`opa`.`options_values_id`',
                    false)
            ->group_by('op.orders_products_id')
            ->get_compiled_select();
        
        $propertyVariantSubSelect = $this->db->select('op.orders_products_id')
            ->select('GROUP_CONCAT(CONCAT(`opp`.`properties_name`, ": ", `opp`.`values_name`) ORDER BY `opp`.`orders_products_properties_id` SEPARATOR ", ") as `property_variant`',
                     false)
            ->from('orders_products as op')
            ->join('orders_products_properties as opp',
                   'op.orders_products_id = opp.orders_products_id',
                   'left outer')
            ->where('op.orders_id', $orderId->asInt())
            ->group_by('op.orders_products_id')
            ->get_compiled_select();
        
        $items = $this->db->select('op.products_model as products_model')
            ->select('op.products_name as name')
            ->select('op.products_quantity as quantity')
            ->select('op.products_price as price')
            ->select('op.products_tax as item_tax')
            ->select('m.manufacturers_name as brand')
            ->select('attribute_model.attribute_model as attribute_model')
            ->select('property_variant.property_variant as property_variant')
            ->select('GROUP_CONCAT(CONCAT(`opa`.`products_options`, ": ", `opa`.`products_options_values`) ORDER BY `opa`.`orders_products_attributes_id` SEPARATOR ", ") as `attribute_variant`',
                     false)
            ->from('orders_products as op')
            ->join('products as p', 'p.products_id = op.products_id', 'left')
            ->join('manufacturers as m',
                   'm.manufacturers_id = p.manufacturers_id',
                   'left outer')
            ->join('orders_products_attributes as opa',
                   'op.orders_products_id = opa.orders_products_id',
                   'left outer')
            ->join('(' . $attributeModelSubSelect . ') as `attribute_model`',
                   '`op`.`orders_products_id` = `attribute_model`.`orders_products_id`',
                   'left outer',
                   false)
            ->join('(' . $propertyVariantSubSelect . ') as `property_variant`',
                   '`op`.`orders_products_id` = `property_variant`.`orders_products_id`',
                   'left outer',
                   false)
            ->where('op.orders_id', $orderId->asInt())
            ->group_by('op.products_model')
            ->group_by('op.products_name')
            ->group_by('op.products_quantity')
            ->group_by('op.products_price,')
            ->group_by('op.products_tax')
            ->group_by('m.manufacturers_name')
            ->group_by('op.orders_products_id')
            ->order_by('op.orders_products_id')
            ->get()
            ->result_array();
        
        foreach ($items as $key => &$item) {
            $item['id'] = $item['attribute_model'] ? $item['products_model'] . '-'
                                                     . $item['attribute_model'] : $item['products_model'];
            
            $item['price'] = $this->netPrice ? $this->_gross2NetByTaxRate($item['item_tax'],
                                                                          $item['price']) : (float)$item['price'];
            
            $item['variant'] = null;
            if ($item['property_variant'] && $item['attribute_variant']) {
                $item['variant'] = $item['property_variant'] . ', ' . $item['attribute_variant'];
            }
            if ($item['property_variant']) {
                $item['variant'] = $item['property_variant'];
            }
            if ($item['attribute_variant']) {
                $item['variant'] = $item['attribute_variant'];
            }
            
            $item['listPosition'] = ($key) + 1;
            
            unset($item['products_model']);
            unset($item['attribute_model']);
            unset($item['item_tax']);
            unset($item['property_variant']);
            unset($item['attribute_variant']);
        }
        
        return array_merge($result,
                           [
                               'affiliation' => $storeName,
                               'items'       => $items
                           ]);
    }
    
    
    protected function _getCompiledOrderTotalSelect(IdType $orderId, $otClass, $alias)
    {
        $compiledSelect = '(' . $this->db->select('ot.value')
                ->from('orders_total as ot')
                ->where('ot.orders_id',
                        $orderId->asInt())
                ->where('ot.class', $otClass)
                ->get_compiled_select() . ') AS `' . $alias . '`';
        
        return $compiledSelect;
    }
    
    
    /**
     * Calculates the gross price by the given net price and tax rate.
     *
     * @param float $taxRate  Tax rate for calculation.
     * @param float $netPrice Net price.
     *
     * @return float By given tax rate and net value calculated gross price.
     */
    protected function _net2GrossByTaxRate($taxRate, $netPrice)
    {
        $tax = $netPrice * $taxRate / 100;
        
        return $netPrice + $tax;
    }
    
    
    protected function _gross2NetByTaxRate($taxRate, $grossPrice)
    {
        $asd = (float)$taxRate / 100 + 1;
        
        return (float)$grossPrice / $asd;
    }
    
    
    /**
     * Calculates the gross price by the given and price and product id.
     * The product id is required to fetch the right geo zone of the product.
     *
     * @param int   $productId Product id.
     * @param float $netPrice  Net price.
     *
     * @return float Calculated gross price.
     */
    protected function _net2Gross($productId, $netPrice)
    {
        if (null === $this->taxCountryId) {
            $this->taxCountryId = (int)$this->db->select('value')
                                           ->where('key',
                                                   'configuration/STORE_COUNTRY')
                                           ->get('gx_configurations')
                                           ->row_array()['value'];
        }
        
        $taxRate = (float)$this->db->select('tax_rate')
                              ->from('tax_rates as tr')
                              ->join('zones_to_geo_zones as ztgz',
                                     'tr.tax_zone_id = ztgz.geo_zone_id')
                              ->join('products as p', 'tr.tax_class_id = p.products_tax_class_id')
                              ->where('ztgz.zone_country_id',
                                      $this->taxCountryId)
                              ->where('p.products_id', $productId)
                              ->group_by('tax_rate')
                              ->get()
                              ->row_array()['tax_rate'];
        
        return $this->_net2GrossByTaxRate($taxRate, $netPrice);
    }
    
    
    /**
     * Fetches the maximal discount value for the given customer group.
     *
     * @param IdType $customerGroupId Id of customer group.
     *
     * @return array Discount of customer group.
     */
    protected function _customerGroupsMaxDiscount(IdType $customerGroupId)
    {
        return $this->db->select('customers_status_discount')
            ->select('customers_status_discount_attributes')
            ->from('customers_status')
            ->where('customers_status_id', $customerGroupId->asInt())
            ->get()
            ->row_array();
    }
    
    
    /**
     * Prepares the select query to fetch impression data.
     *
     * @param IdType $languageId      Language Id.
     * @param IdType $customerGroupId Customer Group Id.
     *
     * @return CI_DB_query_builder Used query builder instance.
     */
    protected function _impressionQuerySetup(IdType $languageId, IdType $customerGroupId)
    {
        
        return $this->_productDataQuerySetup($customerGroupId, $languageId)
            ->select('p.products_model as id')
            ->select('MIN(ppc.combi_price) as combinationPrice')
            ->join('products_properties_combis as ppc', 'p.products_id = ppc.products_id', 'left outer')
            ->group_by('ppc.products_id')
            ->group_by('p.products_model')
            ->group_by('p.products_discount_allowed')
            ->group_by('pd.products_name')
            ->group_by('m.manufacturers_name')
            ->group_by('p.products_price')
            ->group_by('pobcs.personal_offer')
            ->group_by('s.specials_new_products_price')
            ->group_by('p.products_id');
    }
    
    
    /**
     * Prepares the impression result data set.
     * Unnecessary elements with keys "productPrice", ""personalOffer, "specialPrice" and "allowedDiscount" will be
     * removed.
     *
     * @param array $data                  Impression result data set.
     * @param int   $productId             Product Id.
     * @param array $customerGroupDiscount Discount data for customer group.
     */
    protected function _prepareImpressionResult(array &$data, $productId, array $customerGroupDiscount)
    {
        if ($data['id'] === '' && $data['name'] === '') {
            $data['id'] = $productId;
        }
        if (array_key_exists('productPrice', $data)) {
            $data['price'] = (float)$data['productPrice'];
        }
        if (array_key_exists('personalOffer', $data) && $data['personalOffer'] && $data['personalOffer'] !== '0.0000') {
            $data['price'] = (float)$data['personalOffer'];
        }
        
        if (array_key_exists('allowedDiscount', $data) && $data['allowedDiscount'] !== '0.00'
            && $customerGroupDiscount['customers_status_discount'] !== '0.00') {
            $data['price'] = $this->_processDiscount($data['price'],
                                                     $data['allowedDiscount'],
                                                     $customerGroupDiscount['customers_status_discount']);
        }
        
        // override if special exist. specials price has highest priority and lever other settings
        if (array_key_exists('specialPrice', $data) && $data['specialPrice']) {
            $data['price'] = (float)$data['specialPrice'];
        }
        
        if (array_key_exists('combinationPrice', $data) && $data['combinationPrice']) {
            $combinationPrice = (float)$data['combinationPrice'];
            
            if ((int)$customerGroupDiscount['customers_status_discount_attributes'] === 1) {
                $combinationPrice = $this->_processDiscount($combinationPrice,
                                                            $data['allowedDiscount'],
                                                            $customerGroupDiscount['customers_status_discount']);
            }
            
            $data['price'] = $data['price'] + $combinationPrice;
        }
        
        if (array_key_exists('attributePrice', $data) && $data['attributePrice']) {
            $attributesPrice = (float)$data['attributePrice'];
            
            if ((int)$customerGroupDiscount['customers_status_discount_attributes'] === 1) {
                $attributesPrice = $this->_processDiscount($attributesPrice,
                                                           $data['allowedDiscount'],
                                                           $customerGroupDiscount['customers_status_discount']);
            }
            
            $data['price'] = $data['price'] + $attributesPrice;
        }
        
        $this->_unsetKeyIfExists($data, 'productPrice')
            ->_unsetKeyIfExists($data, 'personalOffer')
            ->_unsetKeyIfExists($data,
                                'specialPrice')
            ->_unsetKeyIfExists($data, 'allowedDiscount')
            ->_unsetKeyIfExists($data, 'combinationPrice')
            ->_unsetKeyIfExists($data, 'attributePrice');
        
        if (!$this->netPrice) {
            $data['price'] = $this->_net2Gross($productId, $data['price']);
        }
    }
    
    
    /**
     * Query setup to fetch the main products data.
     *
     * @param IdType $customerGroupId Customer group Id.
     * @param IdType $languageId      Language Id.
     *
     * @return CI_DB_query_builder
     */
    protected function _productDataQuerySetup(IdType $customerGroupId, IdType $languageId)
    {
        return $this->db->select('pd.products_name as name')
            ->select('p.products_price as productPrice')
            ->select('m.manufacturers_name as brand')
            ->select('pobcs.personal_offer as personalOffer')
            ->select('s.specials_new_products_price as specialPrice')
            ->select('p.products_discount_allowed as allowedDiscount')
            ->from('products as p')
            ->join('products_description as pd', 'p.products_id = pd.products_id')
            ->join('manufacturers as m', 'p.manufacturers_id = m.manufacturers_id', 'left outer')
            ->join('specials as s', 'p.products_id = s.products_id', 'left outer')
            ->join('`personal_offers_by_customers_status_' . $customerGroupId->asInt() . '` as `pobcs`',
                   '`p`.`products_id` = `pobcs`.`products_id` AND `pobcs`.`quantity` = 1.0000',
                   'left outer',
                   false)
            ->where('pd.language_id', $languageId->asInt())
            ->group_by('pobcs.personal_offer');
    }
    
    
    /**
     * Calculates discounts.
     *
     * @param string|float $price                    Main price (will be casted to float).
     * @param string|float $allowedDiscount          Products allowed discount (will be casted to float).
     * @param string|float $customerGroupMaxDiscount Customer groups allowed discount (will be casted to float).
     *
     * @return float|int New price with calculated discount.
     */
    protected function _processDiscount($price, $allowedDiscount, $customerGroupMaxDiscount)
    {
        $price                    = (float)$price;
        $allowedDiscount          = (float)$allowedDiscount;
        $customerGroupMaxDiscount = (float)$customerGroupMaxDiscount;
        
        $discountRate = $allowedDiscount >= $customerGroupMaxDiscount ? $customerGroupMaxDiscount : $allowedDiscount;
        $discount     = $price * $discountRate / 100;
        
        return $price - $discount;
    }
    
    
    /**
     * Removes an array element by the given key name, if exists.
     *
     * @param array  $data Array to be mutated.
     * @param string $key  Key of element to be removed.
     *
     * @return $this Same instance for chained method calls.
     */
    protected function _unsetKeyIfExists(array &$data, $key)
    {
        if (array_key_exists($key, $data)) {
            unset($data[$key]);
        }
        
        return $this;
    }
    
    
    /**
     * Fetches product data with attribute variants.
     *
     * @param IdType                          $productId       Product Id.
     * @param IdType                          $customerGroupId Customer Group Id.
     * @param IdType                          $languageId      Language Id.
     * @param GoogleAnalyticsVariantInterface $variant         Variants data if exists.
     *
     * @return array
     */
    protected function _fetchByAttributeVariant(
        IdType $productId,
        IdType $customerGroupId,
        IdType $languageId,
        GoogleAnalyticsVariantInterface $variant
    ) {
        $paMinusSumSelect       = 'SUM(case when `paminus`.`options_values_price` IS NOT NULL then `paminus`.`options_values_price` else 0 end)';
        $paPlusSumSelect        = 'SUM(case when `paplus`.`options_values_id` IS NOT NULL then `paplus`.`options_values_price` else 0 end)';
        $combinationPriceSelect = '0 - ' . $paMinusSumSelect . ' + ' . $paPlusSumSelect . ' as `attributes_price`';
        
        $priceSubSelect = $this->db->select('p2.products_id')
            ->select($combinationPriceSelect)
            ->from('products as p2')
            ->join('products_attributes as paminus',
                   'paminus.products_id = p2.products_id AND paminus.price_prefix = "-" AND paminus.options_values_id IN ('
                   . implode(',', $variant->attributes()->valueIds()) . ')',
                   'left')
            ->join('products_attributes as paplus',
                   'paplus.products_id = p2.products_id AND paplus.price_prefix = "+" AND paplus.options_values_id IN ('
                   . implode(',', $variant->attributes()->valueIds()) . ')',
                   'left')
            ->where('p2.products_id', $productId->asInt())
            ->get_compiled_select();
        
        $result = $this->_productDataQuerySetup($customerGroupId, $languageId)
            ->select('CONCAT(`p`.`products_model`, "-", GROUP_CONCAT(`pa`.`attributes_model` ORDER BY `pa`.`products_attributes_id` SEPARATOR "-")) as `id`',
                     false)
            ->select('combi_price_result.attributes_price as attributePrice')
            ->select('GROUP_CONCAT(CONCAT(`po`.`products_options_name`, ": ", `pov`.`products_options_values_name`) ORDER BY `po`.`products_options_name`, `pov`.`products_options_values_name` SEPARATOR ", ") as `variant`',
                     false)
            ->join('products_attributes as pa', 'p.products_id = pa.products_id', 'left')
            ->join('(' . $priceSubSelect . ') as combi_price_result',
                   'combi_price_result.products_id = p.products_id',
                   '',
                   false)
            ->join('products_options as po',
                   'pa.options_id = po.products_options_id AND pd.language_id = po.language_id')
            ->join('products_options_values as pov',
                   'pa.options_values_id = pov.products_options_values_id AND pd.language_id = pov.language_id')
            ->where('p.products_id', $productId->asInt())
            ->where_in('pa.options_values_id',
                       $variant->attributes()->valueIds())
            ->group_by('combi_price_result.attributes_price')
            ->group_by('pobcs.personal_offer')
            ->get()
            ->row_array();
        
        $customerGroupDiscount = $this->_customerGroupsMaxDiscount($customerGroupId);
        if($result === null) $result = array('id'=>'','name'=>'');
        $this->_prepareImpressionResult($result, $productId->asInt(), $customerGroupDiscount);
        
        return $result;
    }
    
    
    /**
     * Fetches product data with property variants.
     *
     * @param IdType                          $productId       Product Id.
     * @param IdType                          $customerGroupId Customer Group Id.
     * @param IdType                          $languageId      Language Id.
     * @param GoogleAnalyticsVariantInterface $variant         Variants data if exists.
     *
     * @return array
     */
    protected function _fetchByPropertyVariant(
        IdType $productId,
        IdType $customerGroupId,
        IdType $languageId,
        GoogleAnalyticsVariantInterface $variant
    ) {
        $propertyValues = $variant->properties()->valueIds();
        
        $combinationIdSubSelect = $this->db->select('ppcv.products_properties_combis_id')
            ->select('COUNT(`ppcv`.`products_properties_combis_id`) as `propertyValuesCount`',
                     false)
            ->from('products_properties_combis_values as ppcv')
            ->join('products_properties_combis as ppc',
                   'ppcv.products_properties_combis_id = ppc.products_properties_combis_id')
            ->where_in('ppcv.properties_values_id', $propertyValues)
            ->where('ppc.products_id', $productId->asInt())
            ->group_by('ppcv.products_properties_combis_id')
            ->having('propertyValuesCount', count($propertyValues))
            ->get_compiled_select();
        
        $mainQuery = $this->_productDataQuerySetup($customerGroupId, $languageId)
            ->select('CONCAT(`p`.`products_model`, "-", `ppc`.`combi_model`) as `id`',
                     false)
            ->select('GROUP_CONCAT(CONCAT(`ppi`.`properties_name`, ": ", `ppi`.`values_name`) ORDER BY `ppi`.`properties_sort_order` SEPARATOR ", ") as `variant`',
                     false)
            ->select('ppc.combi_price as combinationPrice')
            ->from('@COMBINATION_ID_SUB_SELECT@')
            ->join('products_properties_combis as ppc',
                   'ppc.products_properties_combis_id = combi_id_counts.products_properties_combis_id')
            ->join('`products_properties_index` as `ppi`',
                   '`ppi`.`products_properties_combis_id` = `combi_id_counts`.`products_properties_combis_id` AND `ppi`.`language_id` ='
                   . $languageId->asInt(),
                   '',
                   false)
            ->where('p.products_id', $productId->asInt())
            ->where('`ppc`.`products_properties_combis_id`',
                    '`combi_id_counts`.`products_properties_combis_id`',
                    false)
            ->group_by('combi_id_counts.products_properties_combis_id')
            ->get_compiled_select();
        
        $query  = str_replace('`@COMBINATION_ID_SUB_SELECT@`',
                              '(' . $combinationIdSubSelect . ') as combi_id_counts',
                              $mainQuery);
        $result = $this->db->query($query)->row_array();
        
        $customerGroupMaxDiscount = $this->_customerGroupsMaxDiscount($customerGroupId);
        if($result === null) $result = array('id'=>'','name'=>'');
        $this->_prepareImpressionResult($result, $productId->asInt(), $customerGroupMaxDiscount);
        
        return $result;
    }
    
    
    /**
     * Fetches product data with combined variants.
     *
     * @param IdType                          $productId       Product Id.
     * @param IdType                          $customerGroupId Customer Group Id.
     * @param IdType                          $languageId      Language Id.
     * @param GoogleAnalyticsVariantInterface $variant         Variants data if exists.
     *
     * @return array
     */
    protected function _fetchByCombinedVariant(
        IdType $productId,
        IdType $customerGroupId,
        IdType $languageId,
        GoogleAnalyticsVariantInterface $variant
    ) {
        $combinationIdSubSelect        = $this->_combinationIdSubSelect($productId, $variant);
        $attributeCombinationSubSelect = $this->_attributeCombinationSubSelect($productId->asInt(),
                                                                               $variant->attributes()->valueIds());
        $attributePriceSubSelect       = $this->_attributePriceSubSelect($productId->asInt());
        $attributeVariantSubSelect     = $this->_attributeVariantSubSelect($productId->asInt(),
                                                                           $languageId,
                                                                           $variant->attributes()->optionIds(),
                                                                           $variant->attributes()->valueIds());
        
        $variantSelection = 'CONCAT(GROUP_CONCAT(CONCAT(`ppi`.`properties_name`, ": ", `ppi`.`values_name`) ORDER BY
	                    `ppi`.`properties_sort_order` SEPARATOR ", "), ", ", `attribute_variant`.`attribute_variant_description`) as `variant`';
        
        $result = $this->db->select('CONCAT(`p`.`products_model`, "-", CONCAT(`ppc`.`combi_model`, "-", `attribute_combi`.`attribute_combi_model`)) as `id`',
                                    false)
            ->select('attribute_price_result.attributes_price as attributePrice')
            ->select('products_discount_allowed as allowedDiscount')
            ->select('m.manufacturers_name as brand')
            ->select('p.products_price as productPrice')
            ->select('s.specials_new_products_price as specialPrice')
            ->select('pd.products_name as name')
            ->select('pobcs.personal_offer as personalOffer')
            ->select('ppc.combi_price as combinationPrice')
            ->select($variantSelection, false)
            ->from('products as p')
            ->join('(' . $combinationIdSubSelect . ') AS combi_id_counts',
                   '`p`.`products_id` = `combi_id_counts`.`products_id`',
                   'left',
                   false)
            ->join('(' . $attributeCombinationSubSelect . ') as attribute_combi',
                   '`attribute_combi`.`products_id` = `p`.`products_id`',
                   'left',
                   false)
            ->join('(' . $attributePriceSubSelect . ') as attribute_price_result',
                   '`attribute_price_result`.`products_id` = `p`.`products_id`',
                   'left',
                   false)
            ->join('(' . $attributeVariantSubSelect . ') as `attribute_variant`',
                   '`attribute_variant`.`products_id` = `p`.`products_id`',
                   'left',
                   false)
            ->join('products_properties_combis as ppc',
                   'ppc.products_properties_combis_id = combi_id_counts.products_properties_combis_id')
            ->join('manufacturers as m', 'p.manufacturers_id = m.manufacturers_id', 'left outer')
            ->join('specials as s',
                   'p.products_id = s.products_id',
                   'left outer')
            ->join('products_description as pd', 'p.products_id = pd.products_id')
            ->join('personal_offers_by_customers_status_' . $customerGroupId->asInt() . ' as pobcs',
                   'p.products_id = pobcs.products_id',
                   'left outer')
            ->join('products_properties_index as ppi',
                   'ppi.products_properties_combis_id = combi_id_counts.products_properties_combis_id AND ppi.language_id = '
                   . $languageId->asInt() . ' AND ppi.properties_values_id IN (' . implode(',',
                                                                                           $variant->properties()
                                                                                               ->valueIds()) . ')',
                   'left')
            ->where('p.products_id', $productId->asInt())
            ->where('pd.language_id', $languageId->asInt())
            ->where('`ppc`.`products_properties_combis_id`',
                    '`combi_id_counts`.`products_properties_combis_id`',
                    false)
            ->get()
            ->row_array();
        
        $customerGroupDiscount = $this->_customerGroupsMaxDiscount($customerGroupId);
        if($result === null) $result = array('id'=>'','name'=>'');
        $this->_prepareImpressionResult($result, $productId->asInt(), $customerGroupDiscount);
        
        return $result;
    }
    
    
    /**
     * Returns a select mysql query string that fetches property combination id.
     *
     * @param IdType                          $productId Product Id.
     * @param GoogleAnalyticsVariantInterface $variant   Variants data if exists.
     *
     * @return string Compiled select that fetches the property combination id.
     */
    protected function _combinationIdSubSelect(IdType $productId, GoogleAnalyticsVariantInterface $variant)
    {
        return $this->db->select('ppc.products_id')
            ->select('ppcv.products_properties_combis_id')
            ->select('COUNT(`ppcv`.`products_properties_combis_id`) as `propertyValuesCount`',
                     false)
            ->from('products_properties_combis_values as ppcv')
            ->join('products_properties_combis as ppc',
                   'ppc.products_properties_combis_id = ppcv.products_properties_combis_id')
            ->where('ppc.products_id', $productId->asInt())
            ->where_in('ppcv.properties_values_id',
                       $variant->properties()->valueIds())
            ->group_by('ppcv.products_properties_combis_id')
            ->having('propertyValuesCount',
                     count($variant->properties()->valueIds()))
            ->get_compiled_select();
    }
    
    
    /**
     * Returns a select mysql query string that fetches attribute combination id.
     *
     * @param int   $productId         Product Id.
     * @param array $attributeValueIds Variants data if exists.
     *
     * @return string Compiled select that fetches the attribute combination id.
     */
    protected function _attributeCombinationSubSelect($productId, array $attributeValueIds)
    {
        return $this->db->select('pa.products_id')
            ->select('GROUP_CONCAT(`pa`.`attributes_model` SEPARATOR "-") as `attribute_combi_model`',
                     false)
            ->from('products_attributes as pa')
            ->where('pa.products_id', $productId)
            ->where_in('pa.options_values_id',
                       $attributeValueIds)
            ->group_by('pa.products_id')
            ->get_compiled_select();
    }
    
    
    /**
     * Returns a select mysql query string that fetches attribute price.
     *
     * @param int $productId Product Id.
     *
     * @return string Compiled select that fetches the attribute price.
     */
    protected function _attributePriceSubSelect($productId)
    {
        $positiveAttributePriceSelection = 'SUM(case when `paminus`.`options_values_price` IS NOT NULL then `paminus`.`options_values_price` else 0 end)';
        $negativeAttributePriceSelection = 'SUM(case when `paplus`.`options_values_price` IS NOT NULL then `paplus`.`options_values_price` else 0 end)';
        $attributePriceSelection         = '0 - ' . $positiveAttributePriceSelection . ' + '
                                           . $negativeAttributePriceSelection . ' as attributes_price';
        
        return $this->db->select('p.products_id')
            ->select($attributePriceSelection)
            ->from('products as p')
            ->join('products_attributes as paminus',
                   'paminus.products_id = p.products_id AND paminus.price_prefix = "-" AND paminus.options_values_id IN (2, 4)')
            ->join('products_attributes as paplus',
                   'paplus.products_id = p.products_id AND paplus.price_prefix = "+" AND paplus.options_values_id IN (2, 4)')
            ->where('p.products_id', $productId)
            ->get_compiled_select();
    }
    
    
    /**
     * Returns a select mysql query string that fetches attribute variant names.
     *
     * @param int    $productId          Product Id.
     * @param IdType $languageId         Language Id.
     * @param array  $attributeOptionIds Attribute option Ids.
     * @param array  $attributeValueIds  Attribute value Ids.
     *
     * @return string Compiled  select that fetches the attribute variants name.
     */
    protected function _attributeVariantSubSelect(
        $productId,
        IdType $languageId,
        array $attributeOptionIds,
        array $attributeValueIds
    ) {
        $attributesVariantSelection = 'GROUP_CONCAT(CONCAT(`po`.`products_options_name`, ": ", `pov`.`products_options_values_name`)
	             ORDER BY `po`.`products_options_name`, `pov`.`products_options_values_name` SEPARATOR
	             ", ") as `attribute_variant_description`';
        
        return $this->db->select('pa.products_id')
            ->select($attributesVariantSelection, false)
            ->from('products_attributes as pa')
            ->join('products_options as po',
                   'pa.options_id = po.products_options_id AND po.language_id = ' . $languageId->asInt())
            ->join('products_options_values as pov',
                   'pa.options_values_id = pov.products_options_values_id AND pov.language_id = '
                   . $languageId->asInt())
            ->where('pa.products_id', $productId)
            ->where_in('pa.options_id', $attributeOptionIds)
            ->where_in('pa.options_values_id',
                       $attributeValueIds)
            ->get_compiled_select();
    }
}
