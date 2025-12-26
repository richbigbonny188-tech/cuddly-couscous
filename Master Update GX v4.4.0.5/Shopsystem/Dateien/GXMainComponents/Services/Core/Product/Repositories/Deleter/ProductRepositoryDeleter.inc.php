<?php

/* --------------------------------------------------------------
   ProductRepositoryDeleter.inc.php 2019-05-15
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class ProductRepositoryDeleter
 *
 * @category   System
 * @package    Product
 * @subpackage Repositories
 */
class ProductRepositoryDeleter implements ProductRepositoryDeleterInterface
{
    /**
     * Database connection.
     *
     * @var CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var \ProductRepositoryDeleteHelper
     */
    protected $deleteHelper;
    
    
    /**
     * ProductRepositoryDeleter constructor.
     *
     * @param CI_DB_query_builder            $db
     * @param \ProductRepositoryDeleteHelper $deleteHelper
     */
    public function __construct(CI_DB_query_builder $db, ProductRepositoryDeleteHelper $deleteHelper)
    {
        $this->db           = $db;
        $this->deleteHelper = $deleteHelper;
    }
    
    
    /**
     * Removes a product by the given product id.
     *
     * @param IdType $productId Id of product entity.
     *
     * @return ProductRepositoryDeleter Same instance for chained method calls.
     */
    public function deleteById(IdType $productId)
    {
        $this->_removeFeatureSetsByProductId($productId)
            ->_removeAdditionalFieldRelationsByProductId($productId)
            ->_removePropertiesByProductId($productId)
            ->_removePersonalOffersByCustomerStatusByProductId($productId)
            ->_removeReviewsByProductId($productId)
            ->_removeRecordsByProductId($productId);
        
        $this->deleteHelper->productsContent($productId,
                                             $this->db,
                                             MainFactory::create('ProductsContentFileStorage'),
                                             MainFactory::create('ResponsiveFileManagerConfigurationStorage'))
            ->resetCategoriesAndAlsoPurchasedCache();
        
        return $this;
    }
    
    
    /**
     * Removes related feature set records by the given product id.
     *
     * @param \IdType $productId Product id of records to be removed.
     *
     * @return $this|ProductRepositoryDeleter Same instance for chained method calls.
     */
    protected function _removeFeatureSetsByProductId(IdType $productId)
    {
        // removal of feature set records
        $featureSets = $this->db->select('feature_set_id')
            ->from('feature_set_to_products')
            ->where('products_id',
                    $productId->asInt())
            ->get()
            ->result_array();
        $this->db->delete('feature_set_to_products', ['products_id' => $productId->asInt()]);
        foreach ($featureSets as $featureSet) {
            // check if feature set is used by another product
            $total = (int)$this->db->select('COUNT(*) as total')
                              ->from('feature_set_to_products')
                              ->where('feature_set_id',
                                      $featureSet['feature_set_id'])
                              ->get()
                              ->row_array()['total'];
            
            if ($total === 0) {
                $this->db->delete([
                                      'feature_index',
                                      'feature_set',
                                      'feature_set_values'
                                  ],
                                  ['feature_set_id' => $featureSet['feature_set_id']]);
            }
        }
        
        return $this;
    }
    
    
    /**
     * Removes related additional field records by the given product id.
     *
     * @param \IdType $productId Product id of records to be removed.
     *
     * @return $this|ProductRepositoryDeleter Same instance for chained method calls.
     */
    protected function _removeAdditionalFieldRelationsByProductId(IdType $productId)
    {
        $additionalFields = $this->db->select('additional_field_id')
            ->from('additional_fields')
            ->where('item_type',
                    'product')
            ->get()
            ->result_array();
        array_map(function ($e) use ($productId) {
            $additionalFieldId = (int)$e['additional_field_id'];
            // we can use row_array(), because item_id and additional_field_id are unique in combination
            $additionalFieldValueId = (int)$this->db->select('additional_field_value_id')
                                               ->from('additional_field_values')
                                               ->where([
                                                           'item_id'             => $productId->asInt(),
                                                           'additional_field_id' => $additionalFieldId
                                                       ])
                                               ->get()
                                               ->row_array()['additional_field_value_id'];
            
            $this->db->delete('additional_field_value_descriptions',
                              ['additional_field_value_id' => $additionalFieldValueId]);
            $this->db->delete('additional_field_values', ['additional_field_value_id' => $additionalFieldValueId]);
        },
            $additionalFields);
        
        return $this;
    }
    
    
    /**
     * Removes related property records by the given product id.
     *
     * @param \IdType $productId Product id of records to be removed.
     *
     * @return $this|ProductRepositoryDeleter Same instance for chained method calls.
     */
    protected function _removePropertiesByProductId(IdType $productId)
    {
        $combinationIds = array_map(function ($e) {
            return (int)$e['products_properties_combis_id'];
        },
            $this->db->select('products_properties_combis_id')
                ->from('products_properties_combis')
                ->where(['products_id' => $productId->asInt()])
                ->get()
                ->result_array());
        foreach ($combinationIds as $combinationId) {
            $this->db->delete('customers_basket', ['products_id LIKE' => '%x' . $combinationId]);
            $this->deleteHelper->propertyCombinationImages(new IdType($combinationId));
        }
        $propertyTables = [
            'products_properties_combis',
            'products_properties_combis_values',
            'products_properties_index'
        ];
        if (count($combinationIds) > 0) {
            foreach ($propertyTables as $propertyTable) {
                $chunkedIds = array_chunk($combinationIds, 100);
                
                foreach ($chunkedIds as $ids) {
                    $this->db->where_in('products_properties_combis_id', $ids)->delete($propertyTable);
                }
            }
        }
        
        $this->db->delete('products_properties_admin_select', ['products_id', $productId]);
        
        return $this;
    }
    
    
    /**
     * Removes records from all dynamic personal_offer_by_customer_status_ tables by the given product id.
     *
     * @param \IdType $productId Product id of records to be removed.
     *
     * @return $this|ProductRepositoryDeleter Same instance for chained method calls.
     */
    protected function _removePersonalOffersByCustomerStatusByProductId(IdType $productId)
    {
        array_map(function ($e) use ($productId) {
            $this->db->delete('personal_offers_by_customers_status_' . (int)$e['customers_status_id'],
                              ['products_id' => $productId->asInt()]);
        },
            $this->db->select('customers_status_id')->distinct()->from('customers_status')->get()->result_array());
        
        return $this;
    }
    
    
    /**
     * Removes review records by the given product id.
     *
     * @param \IdType $productId Product id of records to be removed.
     *
     * @return $this|ProductRepositoryDeleter Same instance for chained method calls.
     */
    protected function _removeReviewsByProductId(IdType $productId)
    {
        array_map(function ($e) {
            $this->db->delete('reviews_description', ['reviews_id' => (int)$e['reviews_id']]);
        },
            $this->db->select('reviews_id')
                ->from('reviews')
                ->where(['products_id' => $productId->asInt()])
                ->get()
                ->result_array());
        $this->db->delete('reviews', ['products_id' => $productId->asInt()]);
        
        return $this;
    }
    
    
    /**
     * Removes records by products id in all related tables.
     *
     * @param \IdType $productId Product id of records to be removed.
     *
     * @return $this|ProductRepositoryDeleter Same instance for chained method calls.
     */
    protected function _removeRecordsByProductId(IdType $productId)
    {
        $affectedTables = [
            'products',
            'specials',
            'products_content',
            'products_images',
            'products_to_categories',
            'products_description',
            'products_attributes',
            'customers_basket',
            'customers_basket_attributes',
            'gm_prd_img_alt',
            'categories_index',
            'products_quantity_unit',
            'products_google_categories',
            'products_item_codes',
            'products_properties_admin_select'
        ];
        $this->db->delete($affectedTables, ['products_id' => $productId->asInt()]);
        
        return $this;
    }
}