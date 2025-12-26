<?php

/* --------------------------------------------------------------
   QuickEditOverviewAjaxController.inc.php 2020-12-18
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2018 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class QuickEditOverviewAjaxController
 *
 * @category System
 * @package  AdminHttpViewControllers
 */
class QuickEditOverviewAjaxController extends AdminHttpViewController
{
    /**
     * @var CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var QuickEditProductReadService
     */
    protected $quickEditProductReadService;
    
    /**
     * @var QuickEditService
     */
    protected $quickEditService;
    
    /**
     * @var QuickEditProductWriteService
     */
    protected $quickEditProductWriteService;
    
    /**
     * @var QuickEditOverviewColumns
     */
    protected $quickEditOverviewColumns;
    
    /**
     * @var QuickEditOverviewTooltips
     */
    protected $quickEditOverviewTooltips;
    
    /**
     * @var DataTableHelper
     */
    protected $dataTableHelper;
    
    /**
     * @var UserConfigurationService
     */
    protected $userConfigurationService;
    
    /**
     * @var array
     */
    protected $selectedCategories = [];
    
    
    /**
     * Initializes the required objects.
     */
    public function init()
    {
        $this->_validatePageToken();
        
        $this->db                           = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $this->quickEditOverviewColumns     = MainFactory::create('QuickEditOverviewColumns');
        $this->dataTableHelper              = MainFactory::create('DataTableHelper');
        $this->quickEditProductReadService  = StaticGXCoreLoader::getService('ProductRead');
        $this->quickEditProductWriteService = StaticGXCoreLoader::getService('ProductWrite');
        $this->quickEditOverviewTooltips    = MainFactory::create('QuickEditOverviewTooltips');
        $this->userConfigurationService     = StaticGXCoreLoader::getService('UserConfiguration');
        
        $quickEditServiceFactory = MainFactory::create('QuickEditServiceFactory', $this->db);
        $this->quickEditService  = $quickEditServiceFactory->createQuickEditService();
    }
    
    
    /**
     * Returns all the data for the DataTables instance of the QuickEdit main view.
     *
     * @return bool|JsonHttpControllerResponse Returns QuickEdit overview table data.
     */
    public function actionDataTable()
    {
        try {
            $response = [
                'data'            => $this->_getTableData(),
                'draw'            => (int)$_REQUEST['draw'],
                'recordsFiltered' => $this->_getFilteredProductsCount(),
                'recordsTotal'    => $this->_getRecordsTotal()
            ];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Returns the graduted prices for the overview page in JSON format.
     *
     * @return bool|JsonHttpControllerResponse Returns the graduated prices information in JSON format.
     */
    public function actionProductGraduatedPrices()
    {
        try {
            $response = [
                'data' => $this->_getProductGraduatedPrices()
            ];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Returns the tooltips for the overview page in JSON format.
     *
     * @return bool|JsonHttpControllerResponse Returns tooltips data in JSON format.
     */
    public function actionTooltips()
    {
        try {
            $response         = [];
            $columns          = $this->quickEditOverviewColumns->getColumns();
            $start            = new IntType($_REQUEST['start']);
            $length           = new IntType($_REQUEST['length']);
            $orderBy          = new StringType($this->dataTableHelper->getOrderByClause($columns));
            $filterParameters = $this->dataTableHelper->getFilterParameters($columns);
            
            $products = $this->quickEditProductReadService->between($start, $length)
                ->orderBy($orderBy)
                ->getFilteredProducts($filterParameters);
            
            foreach ($products as $product) {
                /** @var QuickEditProductListItem $product */
                $response[$product->getId()] = $this->quickEditOverviewTooltips->getRowTooltips($product);
            }
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Returns the filter options for the overview page in JSON format.
     *
     * @return bool|JsonHttpControllerResponse Returns filter options in JSON format.
     */
    public function actionFilterOptions()
    {
        try {
            $response = [];
            
            /** @var DataTableColumn $dataTableColumn */
            foreach ($this->quickEditOverviewColumns->getColumns() as $dataTableColumn) {
                $options = $dataTableColumn->getOptions();
                
                if (count($options) > 0) {
                    $response[$dataTableColumn->getName()] = $dataTableColumn->getOptions();
                }
            }
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Creates inventory list PDF.
     *
     * @return bool|JsonHttpControllerResponse Returns PDF creation status.
     */
    public function actionCreateInventoryFile()
    {
        try {
            $response = ['success' => false];
            $data     = $this->_getPostDataCollection()->getArray();
            
            if (in_array('inventoryList', $data, true)) {
                $document = MainFactory::create('QuickEditDocuments');
                
                if (array_key_exists('products', $data)) {
                    $response['success'] = $document->getProductsById($data['products']);
                } else {
                    $response['success'] = $document->getProducts();
                }
            }
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    public function actionDownloadInventoryFile()
    {
        $document = MainFactory::create('QuickEditDocuments');
        $filePath = $document->getLink();
        
        if (!$filePath['success']) {
            throw new RuntimeException('Inventory PDF document does not exists.');
        }
        
        $filePath = DIR_FS_CATALOG . $filePath['link'];
        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        
        header('Cache-Control: public');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Type: ' . $fileInfo->file($filePath));
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        
        return MainFactory::create('HttpControllerResponse', '');
    }
    
    
    /**
     * Updates product information.
     *
     * @return bool|JsonHttpControllerResponse Returns the operation status.
     */
    public function actionUpdate()
    {
        try {
            $result = true;
            foreach ($_REQUEST['data'] as $productId => $changes) {
                $result &= $this->quickEditProductWriteService->updateProductByClause((int)$productId, $changes);
            }
            
            $response = ['success' => $result];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Saves graduated prices of product.
     *
     * @return bool|JsonHttpControllerResponse Returns the operation status.
     *
     * @throws Exception If request does not have "customerStatuses" parameter.
     */
    public function actionSaveGraduatedPrices()
    {
        try {
            $customerStatuses = $this->_getPostData('customerStatuses');
            
            if (!is_array($customerStatuses)) {
                throw new Exception('Invalid POST value provided for graduated prices: ' . gettype($customerStatuses));
            }
            
            $productId         = $this->_getPostData('productId');
            $processedPriceIds = [];
            $taxRate           = $this->_getTaxRate($productId) / 100;
            
            /** @var array $customerStatuses */
            foreach ($customerStatuses as $customerStatusId => $graduatedPrices) {
                $table = 'personal_offers_by_customers_status_' . $customerStatusId;
                
                $processedPriceIds[$customerStatusId] = [];
                
                if ($graduatedPrices[0] === 'empty') {
                    continue;
                }
                
                foreach ($graduatedPrices as $graduatedPrice) {
                    $storedGraduatedPrice = $this->db->get_where($table,
                                                                 [
                                                                     'products_id' => $productId,
                                                                     'quantity'    => $graduatedPrice['quantity']
                                                                 ])->row_array();
                    
                    if (PRICE_IS_BRUTTO === 'true') {
                        $graduatedPrice['personal_offer'] = $graduatedPrice['personal_offer'] / ($taxRate + 1);
                    }
                    
                    $row = [
                        'quantity'       => $graduatedPrice['quantity'],
                        'personal_offer' => $graduatedPrice['personal_offer'],
                        'products_id'    => $productId
                    ];
                    
                    if (empty($storedGraduatedPrice)) {
                        $this->db->insert($table, $row);
                        $processedPriceIds[$customerStatusId][] = $this->db->insert_id();
                    } else {
                        $storedGraduatedPrice['personal_offer'] = $graduatedPrice['personal_offer'];
                        $this->db->update($table, $row, ['price_id' => $storedGraduatedPrice['price_id']]);
                        $processedPriceIds[$customerStatusId][] = $storedGraduatedPrice['price_id'];
                    }
                }
            }
            
            // Remove graduated prices that are not present in the request data.
            foreach ($processedPriceIds as $customerStatusId => $ids) {
                $table = 'personal_offers_by_customers_status_' . $customerStatusId;
                
                $storedGraduatedPrices = $this->db->get_where($table,
                                                              [
                                                                  'products_id' => $productId
                                                              ])->result_array();
                
                foreach ($storedGraduatedPrices as $storedGraduatedPrice) {
                    if (!in_array($storedGraduatedPrice['price_id'], $ids)) {
                        $this->db->delete($table, ['price_id' => $storedGraduatedPrice['price_id']]);
                    }
                }
            }
            
            $response = ['success' => true];
        } catch (Exception $ex) {
            $response = AjaxException::response($ex);
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Returns the QuickEdit overview table data.
     *
     * @return array Returns the QuickEdit overview table data.
     */
    protected function _getTableData()
    {
        $columns               = $this->quickEditOverviewColumns->getColumns();
        $start                 = new IntType($_REQUEST['start']);
        $length                = new IntType($_REQUEST['length']);
        $orderBy               = new StringType($this->dataTableHelper->getOrderByClause($columns));
        $filterParameters      = $this->dataTableHelper->getFilterParameters($columns);
        $filterByUncategorized = false;
        
        $this->handleUncategorizedFilter($filterParameters, $filterByUncategorized);
        
        $products = $this->quickEditProductReadService->between($start, $length)
            ->orderBy($orderBy)
            ->getFilteredProducts($filterParameters);
        
        if ($filterByUncategorized) {
    
            $products = $this->filterProductWithSelectedUncategorizedFilter($products);
        }
        
        return array_map(function ($product) {
            /** @var QuickEditProductListItem $product */
            
            return [
                'DT_RowId'           => $product->getId(),
                'DT_RowData'         => $this->_getTableRowData($product),
                'id'                 => $product->getId(),
                'category'           => $product->getCategory(),
                'manufacturer'       => $product->getManufacturer(),
                'model'              => $product->getModel(),
                'name'               => $product->getName(),
                'quantity'           => $product->getQuantity(),
                'shippingTimeId'     => $product->getShippingTimeId(),
                'shippingStatusName' => $product->getShippingStatusName(),
                'weight'             => $product->getWeight(),
                'shippingCosts'      => $product->getShippingCosts(),
                'taxClassId'         => $product->getTaxClassId(),
                'tax'                => $product->getTax(),
                'price'              => $product->getPrice(),
                'discount'           => $product->getDiscount(),
                'specialPrice'       => $product->getSpecialPrice(),
                'status'             => $product->getStatus(),
            ];
        },
            $products);
    }
    
    
    /**
     * Returns the various data needed to display one row of the table.
     *
     * @param QuickEditProductListItem $product Contains product information.
     *
     * @return array Returns the various data needed to display one row of the table as an array.
     */
    protected function _getTableRowData($product)
    {
        /** @var QuickEditProductListItem $product */
        return [
            'id'                 => $product->getId(),
            'model'              => $product->getModel(),
            'name'               => $product->getName(),
            'manufacturer'       => $product->getManufacturer(),
            'quantity'           => $product->getQuantity(),
            'shippingTimeId'     => $product->getShippingTimeId(),
            'shippingStatusName' => $product->getShippingStatusName(),
            'weight'             => $product->getWeight(),
            'shippingCosts'      => $product->getShippingCosts(),
            'taxClassId'         => $product->getTaxClassId(),
            'tax'                => $product->getTax(),
            'price'              => $product->getPrice(),
            'discount'           => $product->getDiscount(),
            'specialPrice'       => $product->getSpecialPrice(),
            'status'             => $product->getStatus(),
            'option'             => [
                'manufacturer' => $this->_getManufacturerConfiguration(),
                'shipment'     => $this->_getShipmentConfiguration(),
                'tax'          => $this->_getTaxConfiguration()
            ]
        ];
    }
    
    
    /**
     * Returns the special prices for the existing products.
     *
     * @return array Returns the special prices for the existing products.
     */
    protected function _getProductGraduatedPrices()
    {
        $result   = [];
        $products = [$_REQUEST['productId']];
        
        $productsGraduations = $this->quickEditService->getGraduatedPrices($products);
        
        foreach ($productsGraduations as $key => $value) {
            $result[] = [
                'customers' => $value['customer'],
            ];
        }
        
        return $result;
    }
    
    
    /**
     * Returns the number of existing products.
     *
     * @return int Returns the number of existing products.
     */
    protected function _getRecordsTotal()
    {
        return (int)$this->db->count_all('products');
    }
    
    
    /**
     * Taking into account the filters, the number of products is supplied.
     *
     * @return array Returns the number of products.
     */
    protected function _getFilteredProductsCount()
    {
        $columns          = $this->quickEditOverviewColumns->getColumns();
        $filterParameters = $this->dataTableHelper->getFilterParameters($columns);
        
        return $this->quickEditProductReadService->getFilteredProductsCount($filterParameters);
    }
    
    
    /**
     * Provides the configuration for the manufacturer.
     *
     * @return array Returns the query result as a pure array, or an empty array when no result is produced.
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
     * Provides the configuration for the shipping.
     *
     * @return array Returns the query result as a pure array, or an empty array when no result is produced.
     */
    protected function _getShipmentConfiguration()
    {
        $result = $this->db->select(['shipping_status_id', 'shipping_status_name'])
            ->where('language_id',
                    $_SESSION['languages_id'])
            ->get('shipping_status')
            ->result_array();
        
        $shipmentConfigured = array_map(function ($value) {
            return [
                'id'    => $value['shipping_status_id'],
                'value' => $value['shipping_status_name']
            ];
        },
            $result);
        
        array_unshift($shipmentConfigured,
                      [
                          'id'    => 0,
                          'value' => TEXT_NONE
                      ]);
        
        return $shipmentConfigured;
    }
    
    
    /**
     * Returns the configuration for the tax rates.
     *
     * @return array Returns the query result as a pure array, or an empty array when no result is produced.
     */
    protected function _getTaxConfiguration()
    {
        $result = $this->db->select(['tax_class.tax_class_id', 'tax_class_title', 'tax_rate'])
            ->join('tax_class',
                   'tax_class_id',
                   'inner')
            ->join('zones_to_geo_zones', 'tax_zone_id=geo_zone_id', 'inner')
            ->where('zones_to_geo_zones.zone_country_id=' . (int)STORE_COUNTRY)
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
     * Returns the tax rate for the specified product.
     *
     * @param int $productsId Requested product ID connected to tax rate.
     *
     * @return mixed Returns an object that contains the tax rate of a product.
     */
    protected function _getTaxRate($productsId)
    {
        $result = $this->db->select('tax_rate')
            ->join('tax_rates',
                   'tax_rates.tax_class_id = products.products_tax_class_id')
            ->join('zones_to_geo_zones', 'zones_to_geo_zones.zone_country_id = ' . (int)STORE_COUNTRY)
            ->where('products.products_id',
                    $productsId)
            ->where('tax_rates.tax_zone_id = zones_to_geo_zones.geo_zone_id')
            ->get('products')
            ->row();
        
        return $result->tax_rate;
    }
    
    
    /**
     * @description handling frontend filter category = uncategorized
     *
     * @param array $filterParameters
     * @param bool  $filterByUncategorized
     */
    protected function handleUncategorizedFilter(
        array &$filterParameters,
        bool &$filterByUncategorized
    ): void {
    
        //  user did not filter by any category
        if (isset($filterParameters['category']) === false) {
        
            return;
        }
    
        // only uncategorized is selected
        if ($filterParameters['category'] === ['0']) {
            
            $this->selectedCategories = [];
            // uncategorized + other categories is selected
        } elseif (in_array('0', $filterParameters['category'], true)) {
        
            $key = array_search('0', $filterParameters['category'], true);
            unset($filterParameters['category'][$key]);
            $this->selectedCategories = array_values($filterParameters['category']);
        } else {
            return; // uncategorized not selected. Products are filtered by the database
        }
    
        $filterByUncategorized = true;
        //  because the filtering now will happen in $this::removeUncategorizedProductsFromArray
        //  and not in the database the category filtering must be removed
        unset($filterParameters['category']);
    }
    
    
    /**
     * @description filtering products by selected categories and uncategorized products
     * @param array $products
     *
     * @return array
     */
    protected function filterProductWithSelectedUncategorizedFilter(array $products): array
    {
        // converting the category id to the name in the currently selected language
        $languageCode = new LanguageCode(new StringType($_SESSION['language_code']));
        /** @var CategoryReadService $categoryReader */
        $categoryReader = StaticGXCoreLoader::getService('CategoryRead');
        $categories     = array_map(static function (string $categoryId) use ($categoryReader, $languageCode): string {
        
            $categoryId = new IdType((int)$categoryId);
        
            return $categoryReader->getCategoryById($categoryId)->getName($languageCode);
        },
            $this->selectedCategories);
    
        $filteredProducts = array_filter($products,
            static function (QuickEditProductListItem $product) use ($categories): bool {
    
                $uncategorized      = $product->getCategory() === '';
                $inSelectedCategory = in_array($product->getCategory(), $categories, true);
            
                return $uncategorized || $inSelectedCategory;
            });
        
        // array keys need to be incremental for the frontend
        return array_values($filteredProducts);
    }
}