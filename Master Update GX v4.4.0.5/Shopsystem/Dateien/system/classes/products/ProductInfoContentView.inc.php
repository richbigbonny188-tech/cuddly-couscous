<?php
/* --------------------------------------------------------------
   ProductInfoContentView.inc.php 2020-10-16
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------


   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_info.php,v 1.94 2003/05/04); www.oscommerce.com
   (c) 2003      nextcommerce (product_info.php,v 1.46 2003/08/25); www.nextcommerce.org
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: product_info.php 1320 2005-10-25 14:21:11Z matthias $)


   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist
   New Attribute Manager v4b                            Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com
   Cross-Sell (X-Sell) Admin 1                          Autor: Joshua Dechant (dreamscape)
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed functions
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

require_once DIR_FS_INC . 'xtc_get_download.inc.php';
require_once DIR_FS_INC . 'xtc_get_all_get_params.inc.php';
require_once DIR_FS_INC . 'xtc_date_long.inc.php';
require_once DIR_FS_INC . 'xtc_draw_hidden_field.inc.php';
require_once DIR_FS_INC . 'xtc_image_button.inc.php';
require_once DIR_FS_INC . 'xtc_draw_form.inc.php';
require_once DIR_FS_INC . 'xtc_draw_input_field.inc.php';
require_once DIR_FS_INC . 'xtc_image_submit.inc.php';
require_once DIR_FS_INC . 'xtc_check_categories_status.inc.php';
require_once DIR_FS_INC . 'xtc_get_products_mo_images.inc.php';
require_once DIR_FS_INC . 'xtc_get_vpe_name.inc.php';
require_once DIR_FS_INC . 'get_cross_sell_name.inc.php';
require_once DIR_FS_INC . 'xtc_get_products_stock.inc.php';
require_once DIR_FS_CATALOG . 'gm/inc/gm_prepare_number.inc.php';

/**
 * Class ProductInfoContentView
 */
class ProductInfoContentView extends ContentView
{
    protected $getArray = array(); // $_GET
    protected $postArray = array(); // $_POST

    protected $cheapestCombiArray = array();
    protected $combiId = 0;
    protected $currency = '';
    protected $currentCategoryId = 0;
    protected $currentCombiArray = array();
    protected $customerDiscount = 0.0;
    protected $fsk18DisplayAllowed = true;
    protected $fsk18PurchaseAllowed = true;
    protected $customerStatusId = -1;
    protected $hasProperties = false;
    protected $languageId = 0;
    protected $language = '';
    protected $lastListingSql = '';
    protected $main;
    protected $product;
    protected $productPriceArray = array();
    protected $showGraduatedPrices = false;
    protected $showPrice = true;
    protected $xtcPrice;
    /** @var GMSEOBoost_ORIGIN */
    protected $seoBoost;
    protected $maxImageHeight = 0;
    protected $additionalFields = array();
    protected $offerAggregationLimit = 100;
    protected $stockAllowCheckout = true;
    protected $stockCheck = true;
    protected $attributeStockCheck = true;
    protected $appendPropertiesModel = true;
    protected $showPriceTax = true;

    /**
     * @var VPEReadServiceInterface
     */
    protected $VPEReadService;
    /**
     * @var ManufacturerReadServiceInterface
     */
    protected $manufacturerReadService;

    /**
     * ProductInfoContentView constructor.
     * @param string $p_template
     */
    function __construct($p_template = 'default')
    {
        parent::__construct();
        $filepath = DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/module/product_info/';

        // get default template
        $c_template = $this->get_default_template($filepath, $p_template);

        $this->set_content_template('module/product_info/' . $c_template);
        $this->set_flat_assigns(true);
    }

    /**
     *
     */
    function prepare_data()
    {
        if ($this->product instanceof product && $this->product->isProduct()) {
            $this->_setPriceData();

            $this->_assignProductData();

            $this->_assignProductNavigator();
            $this->_assignFormTagData();
            $this->_assignWidgets();
            $this->_assignReviews();
            $this->_assignProductLists();
            $this->_assignItemCondition();
            $this->_assignRichSnippetData();
            $this->_setPaypalEcButton();

            // TODO move to control
            $this->_updateProductViewsStatistic();
            $this->_updateTracking();

            // test google analytics json
            //$serializer = new GAProductSerializer();
            //
            //$productId  = new IdType($this->product->data['products_id']);
            //$languageId = new IdType($_SESSION['languages_id']);
            //
            //$this->set_content_data('easyJson', $serializer->encode(GoogleAnalyticsServiceFactory::frontendService()
            //                                                                                     ->getProductsDataById($productId,
            //                                                                                                           $languageId),
            //                                                        JSON_PRETTY_PRINT));
        }
    }


    public function get_html()
    {
        if (($this->product instanceof product) === false || !$this->product->isProduct()) {
            // product not found in database
            $error = TEXT_PRODUCT_NOT_FOUND;

            /* @var ErrorMessageContentView $coo_error_message */
            $errorView = MainFactory::create_object('ErrorMessageContentView');
            $errorView->set_error($error);
            $htmlOutput = $errorView->get_html();
        } else {
            $this->prepare_data();
            $htmlOutput = $this->build_html();
        }

        return $htmlOutput;
    }


    protected function _assignProductData()
    {
        // assign properties and set $this->hasProperties flag
        $this->_setPropertiesData();

        $this->_assignAttributes();
        $this->_assignDate();
        $this->_assignDeactivatedButtonFlag();
        $this->_assignGPrint();
        $this->_assignDescription();
        $this->_assignDiscount();
        $this->_assignEan();
        $this->_assignGraduatedPrices();
        $this->_assignId();
        $this->_assignImageData();
        $this->_assignImageMaxHeight();
        $this->_assignLegalAgeFlag();
        $this->_assignModelNumber();
        $this->_assignName();
        $this->_assignNumberOfOrders();
        $this->_assignPrice();
        $this->_assignProductUrl();
        $this->_assignQuantity();
        $this->_assignShippingTime();
        $this->_assignStatus();
        $this->_assignVpe();
        $this->_assignWeight();
        $this->_assignAdditionalFields();

        if ($this->_showPrice()) {
            $this->_assignShippingLink();
            $this->_assignTaxInfo();
        }

        if ($this->_productIsForSale()) {
            $this->_assignInputFieldQuantity();
            $this->_assignPayPalInstallments();
        } else {
            $this->_assignDeprecatedIdHiddenField();
        }
    
        $this->set_content_data('IS_FOR_SALE', $this->_productIsForSale());
        $this->set_content_data('showManufacturerImages', gm_get_conf('SHOW_MANUFACTURER_IMAGE_PRODUCT_DETAILS'));
        $this->set_content_data('showProductRibbons', gm_get_conf('SHOW_PRODUCT_RIBBONS'));

        $showRating = false;
        if (gm_get_conf('ENABLE_RATING') === 'true') {
            $showRating = true;
        }
        $this->content_array['showRating'] = $showRating;
    }


    protected function _assignPayPalInstallments()
    {
        if (strtolower((string)@constant('MODULE_PAYMENT_PAYPAL3_INSTALLMENTS_STATUS')) === 'true') {
            $output = '';
            $paypalConfiguration = MainFactory::create('PayPalConfigurationStorage');
            $showSpecific = (bool)$paypalConfiguration->get('show_installments_presentment_specific_product');
            $hasAttributes = trim($this->content_array['MODULE_product_options']) !== '';
            $showComputed = $hasAttributes === false && $this->hasProperties === false
                && (bool)$paypalConfiguration->get('show_installments_presentment_specific_computed');
            $productPrice = $this->productPriceArray['plain'];
            $paypalInstallments = MainFactory::create('PayPalInstallments');
            if ($showSpecific && $productPrice >= $paypalInstallments->getMinimumAmount()['amount']
                && $productPrice <= $paypalInstallments->getMaximumAmount()['amount']) {
                if ($showComputed == true) {
                    try {
                        $response = $paypalInstallments->getInstallmentInfo($productPrice, $_SESSION['currency'], 'DE');
                        if (!empty($response->financing_options[0]->qualifying_financing_options)) {
                            $representativeOption = $paypalInstallments->getRepresentativeOption($response->financing_options[0]->qualifying_financing_options);
                            $specificContentView = MainFactory::create('PayPalInstallmentSpecificUpstreamPresentmentContentView');
                            $specificContentView->qualifyingOptions = $response->financing_options[0]->qualifying_financing_options;
                            $specificContentView->nonQualifyingOptions = $response->financing_options[0]->non_qualifying_financing_options;
                            $specificContentView->lender = implode(', ', [
                                COMPANY_NAME,
                                TRADER_STREET . ' ' . TRADER_STREET_NUMBER,
                                TRADER_ZIPCODE . ' ' . TRADER_LOCATION
                            ]);
                            $specificContentView->currency = $_SESSION['currency'];
                            $specificContentView->cashPurchasePrice = $productPrice;
                            $specificContentView->numberOfInstallments = $representativeOption->credit_financing->term;
                            $specificContentView->borrowingRate = $representativeOption->credit_financing->nominal_rate;
                            $specificContentView->annualPercentageRate = $representativeOption->credit_financing->apr;
                            $specificContentView->installmentAmount = $representativeOption->monthly_payment->value;
                            $specificContentView->totalAmount = $representativeOption->total_cost->value;
                            $specificContentView->representativeFinancingCode = $representativeOption->credit_financing->financing_code;
                            $output = $specificContentView->get_html();
                        }
                    } catch (Exception $e) {
                        $output = sprintf("<!-- PPI Exception -->\n");
                    }
                } else {
                    if ($this->hasProperties || $hasAttributes) {
                        $amount = 'dynamic';
                    } else {
                        $amount = $productPrice;
                    }
                    $contentView = MainFactory::create('ContentView');
                    $contentView->set_content_template('module/paypalinstallmentspecificstatic.html');
                    $contentView->set_flat_assigns(true);
                    $contentView->set_caching_enabled(false);
                    $contentView->set_content_data('amount', $amount);
                    $output = $contentView->get_html();
                }
            } else {
                $contentView = MainFactory::create('ContentView');
                $contentView->set_content_template('module/paypalinstallmentspecificoutofbounds.html');
                $contentView->set_flat_assigns(true);
                $contentView->set_caching_enabled(false);
                $output = $contentView->get_html();
            }
            $this->set_content_data('PayPalInstallments', $output);
        }
    }


    protected function _assignProductLists()
    {
        $this->_assignAlsoPurchased();
        $this->_assignCrossSelling();
        $this->_assignReverseCrossSelling();
        $this->_assignYoochoose();
    }


    protected function _assignWidgets()
    {
        $this->_assignWishlist();

        $this->_assignSocialServices();
        $this->_assignTellAFriend();
        $this->_assignPriceOffer();
        $this->_assignPrintLink();
    }


    protected function _assignProductNavigator()
    {
        if (ACTIVATE_NAVIGATOR == 'true') {
            /* @var ProductNavigatorContentView $view */
            $view = MainFactory::create_object('ProductNavigatorContentView');
            $view->setProduct($this->product);
            $view->setCategoryId($this->currentCategoryId);
            $view->setLastListingSql($this->lastListingSql);
            $view->setFSK18DisplayAllowed((int)$this->fsk18DisplayAllowed);
            $view->setCustomerStatusId($this->customerStatusId);
            $view->setLanguageId($this->languageId);
            $html = $view->get_html();
            $this->set_content_data('PRODUCT_NAVIGATOR', $html);
        }
    }


    protected function _updateProductViewsStatistic()
    {
        $query = 'UPDATE ' . TABLE_PRODUCTS_DESCRIPTION . '
					SET products_viewed = products_viewed+1
					WHERE
						products_id = ' . (int)$this->product->data['products_id'] . ' AND
						language_id = ' . (int)$this->languageId;

        xtc_db_query($query);
    }


    protected function _setPriceData()
    {
        $query = 'SELECT products_properties_combis_id
					FROM products_properties_combis
					WHERE products_id = ' . (int)$this->product->data['products_id'];
        $result = xtc_db_query($query);

        if (xtc_db_num_rows($result) >= 1) {
            if (xtc_db_num_rows($result) == 1) {
                $row = xtc_db_fetch_array($result);
                $this->combiId = $row['products_properties_combis_id'];
            }

            $coo_properties_control = MainFactory::create_object('PropertiesControl');
            if ($this->combiId > 0) {
                // GET selected combi (GET)
                $this->currentCombiArray = $coo_properties_control->get_combis_full_struct($this->combiId,
                    $this->languageId);
            }
            if ($this->currentCombiArray == false) {
                // GET CHEAPEST COMBI
                $this->cheapestCombiArray = $coo_properties_control->get_cheapest_combi($this->product->data['products_id'],
                    $this->languageId);
                $this->xtcPrice->showFrom_Attributes = true;
            }
        }

        $this->productPriceArray = $this->xtcPrice->xtcGetPrice($this->product->data['products_id'], true, 1,
            $this->product->data['products_tax_class_id'],
            $this->product->data['products_price'], 1);

        if (!empty($this->cheapestCombiArray) && $this->cheapestCombiArray['combi_price'] != 0) {
            $this->productPriceArray = $this->xtcPrice->xtcGetPrice($this->product->data['products_id'], true, 1,
                $this->product->data['products_tax_class_id'],
                $this->product->data['products_price'], 1, 0, true,
                true,
                $this->cheapestCombiArray['products_properties_combis_id']);
        }

        if (!empty($this->currentCombiArray) && $this->currentCombiArray['combi_price'] != 0) {
            $this->productPriceArray = $this->xtcPrice->xtcGetPrice($this->product->data['products_id'], true, 1,
                $this->product->data['products_tax_class_id'],
                $this->product->data['products_price'], 1, 0, true,
                true,
                $this->currentCombiArray['products_properties_combis_id']);
        }
    }


    protected function _assignAlsoPurchased()
    {
        /* @var AlsoPurchasedContentView $view */
        $view = MainFactory::create_object('AlsoPurchasedContentView');
        $view->set_coo_product($this->product);
        $html = $view->get_html();
        $this->set_content_data('MODULE_also_purchased', $html);
    }


    protected function _assignYoochoose()
    {
        if (defined('YOOCHOOSE_ACTIVE') && YOOCHOOSE_ACTIVE) {
            include_once(DIR_WS_INCLUDES . 'yoochoose/recommendations.php');
            include_once(DIR_WS_INCLUDES . 'yoochoose/functions.php');

            /* @var YoochooseAlsoInterestingContentView $view */
            $view = MainFactory::create_object('YoochooseAlsoInterestingContentView');
            $view->setProduct($this->product);
            $html = $view->get_html();
            $this->set_content_data('MODULE_yoochoose_also_interesting', $html);

            $yooHtml = '<img src="' . getTrackingURL('click', $this->product) . '" width="0" height="0" alt="">';

            if (array_key_exists('ycr', $this->getArray)) {
                $yooHtml .= '<img src="' . getTrackingURL('follow', $this->product) . '" width="0" height="0" alt="">';
            }

            $this->set_content_data('MODULE_yoochoose_product_tracking', $yooHtml . "\n");
        }
    }


    protected function _assignAttributes()
    {
        // CREATE ProductAttributesContentView OBJECT
        /* @var ProductAttributesContentView $view */
        $view = MainFactory::create_object('ProductAttributesContentView');

        // SET TEMPLATE
        $filepath = DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/module/product_options/';
        $c_template = $view->get_default_template($filepath, $this->product->data['options_template']);
        $view->set_content_template('module/product_options/' . $c_template);

        // SET DATA
        $view->set_coo_product($this->product);
        $view->set_language_id($this->languageId);

        // GET HTML
        $html = $view->get_html();
        $this->set_content_data('MODULE_product_options', $html);
    }


    protected function _assignCrossSelling()
    {
        /* @var CrossSellingContentView $view */
        $view = MainFactory::create_object('CrossSellingContentView');
        $view->set_type('cross_selling');
        $view->set_coo_product($this->product);
        $html = $view->get_html();
        $this->set_content_data('MODULE_cross_selling', $html);
    }


    protected function _assignReverseCrossSelling()
    {
        if (ACTIVATE_REVERSE_CROSS_SELLING == 'true') {
            /* @var CrossSellingContentView $view */
            $view = MainFactory::create_object('CrossSellingContentView');
            $view->set_type('reverse_cross_selling');
            $view->set_coo_product($this->product);
            $html = $view->get_html();
            $this->set_content_data('MODULE_reverse_cross_selling', $html);
        }
    }
    
    
    protected function _assignRichSnippetData()
    {
        $isActive = (bool)$this->product->data['products_fsk18'] === false
                    && ($this->stockAllowCheckout
                        || (($this->product->data['products_quantity'] > 0)
                            && !$this->stockAllowCheckout))
                    && (string)$this->product->data['gm_price_status'] === '0';
        if (!$isActive) {
            return;
        }
        
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        /** @var \LanguageHelper $languageHelper */
        $languageHelper = MainFactory::create('LanguageHelper', $db);
        $languageCode   = $languageHelper->getLanguageCodeById(new IdType((int)$this->languageId));
        $taxIncluded    = $this->showPriceTax;
        
        $itemCondition = 'NewCondition';
        if ($this->content_array['ITEM_CONDITION'] === 'refurbished') {
            $itemCondition = 'RefurbishedCondition';
        } elseif ($this->content_array['ITEM_CONDITION'] === 'used') {
            $itemCondition = 'UsedCondition';
        }
        
        $productsQuantity = xtc_get_products_stock($this->product->data['products_id']);
        $availability     = ($productsQuantity > 0 || !$this->stockCheck) ? 'InStock' : 'OutOfStock';
        
        $descriptionTabs = preg_split('/\[TAB.+?\]/', $this->product->data['products_description']);
        do {
            $description = array_shift($descriptionTabs);
        } while (empty($description) && !empty($descriptionTabs));
        $description = strtr($description, ["\n" => ' ', "\r" => ' ']);
        $description = strip_tags($description);
        if ($this->getSeoBoost()->boost_products === true) {
            $productUrl = xtc_href_link($this->getSeoBoost()
                                            ->get_boosted_product_url($this->product->data['products_id'],
                                                                      $this->product->data['products_name']));
        } else {
            $productUrl = xtc_href_link(FILENAME_PRODUCT_INFO,
                                        xtc_product_link($this->product->data['products_id'],
                                                         $this->product->data['products_name']) . '&no_boost=1');
        }
        
        $productImage = GM_HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_INFO_IMAGES . rawurlencode($this->product->data['products_image']);
        $linkedData = [
            '@context'      => 'http://schema.org',
            '@type'         => 'Product',
            'name'          => $this->product->data['products_name'],
            'description'   => $description,
            'image'         => [ $productImage, ],
            'url'           => $productUrl,
            'itemCondition' => $itemCondition,
        ];
        
        /** @var \xtcPrice_ORIGIN $xtPrice */
        $xtPrice  = $this->xtcPrice;
        $priceRaw = $xtPrice->xtcGetPrice($this->product->data['products_id'],
                                          false,
                                          1,
                                          $this->product->data['products_tax_class_id'],
                                          $this->product->data['products_price']);
        if (is_float($priceRaw)) {
            $price            = number_format($priceRaw, 2, '.', '');
            $priceValidUntil = '2100-01-01 00:00:00';
            $specialPriceData = $db->get_where('specials',
                                               [
                                                   'products_id' => (int)$this->product->data['products_id'],
                                                   'status'      => 1
                                               ])->row_array();
            $priceValidUntil = '2100-01-01 00:00:00';
            try {
                if (($specialPriceData !== null)) {
                    $expiresDateTime = new DateTime($specialPriceData['expires_date']);
                    if ($expiresDateTime->getTimestamp() > time()) {
                        $priceValidUntil = $expiresDateTime->format('c');
                    }
                }
            } catch (Exception $e) {
                $priceValidUntil = '2100-01-01 00:00:00';
            }
    
            $linkedData['offers'] = [
                '@type'              => 'Offer',
                'availability'       => $availability,
                'price'              => $price,
                'priceCurrency'      => $this->getCurrency(),
                'priceSpecification' => [
                    '@type'                 => 'http://schema.org/PriceSpecification',
                    'price'                 => $price,
                    'priceCurrency'         => $this->getCurrency(),
                    'valueAddedTaxIncluded' => $taxIncluded,
                ],
                'url'                => $productUrl,
            ];
        }
        
        if (!empty($priceValidUntil)) {
            $linkedData['offers']['priceValidUntil'] = $priceValidUntil;
        }
        
        $additionalImagesArray = xtc_get_products_mo_images($this->product->data['products_id']);
        if (is_array($additionalImagesArray) && !empty($additionalImagesArray)) {
            foreach ($additionalImagesArray as $imageArray) {
                $linkedData['image'][] = GM_HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_INFO_IMAGES
                                         . rawurlencode($imageArray['image_name']);
            }
        }
        
        if (!empty($this->product->data['products_model'])) {
            $linkedData['model'] = $this->product->data['products_model'];
            $linkedData['sku']   = $this->product->data['products_model'];
        }
        
        if ((float)$this->product->data['gm_min_order'] === 0) {
            $linkedData['offers']['availability'] = 'http://schema.org/OutOfStock';
        }
        
        if ($this->content_array['ITEM_CONDITION'] === 'refurbished') {
            $linkedData['offers']['itemCondition'] = 'http://schema.org/RefurbishedCondition';
        } elseif ($this->content_array['ITEM_CONDITION'] === 'used') {
            $linkedData['offers']['itemCondition'] = 'http://schema.org/UsedCondition';
        }
        
        if (in_array(strlen($this->product->data['products_ean']), [8, 12, 13, 14], true)) {
            $linkedData['offers']['gtin'
                                  . strlen($this->product->data['products_ean'])] = $this->product->data['products_ean'];
            $linkedData['gtin'
                        . strlen($this->product->data['products_ean'])]           = $this->product->data['products_ean'];
        }
        
        if (!empty($this->product->data['manufacturers_id'])
            && $manufacturer = $this->getManufacturerById($this->product->data['manufacturers_id'])) {
            
            $linkedData['manufacturer'] = [
                '@type' => 'Organization',
                'name'  => $manufacturer->getName(),
            ];
        }
        
        $db               = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $productItemCodes = $db->where('products_id',
                                       (int)$this->product->data['products_id'])
            ->get('products_item_codes')
            ->row_array();
        if (!empty($productItemCodes['brand_name'])) {
            $brand = [
                '@type' => 'Brand',
                'name'  => $productItemCodes['brand_name'],
            ];
            $linkedData['brand'] = $brand;
        }
        
        if (!empty($productItemCodes['code_isbn'])) {
            $linkedData['productId'] = 'isbn:' . $productItemCodes['code_isbn'];
        }
        
        if (!empty($productItemCodes['code_mpn'])) {
            $linkedData['mpn'] = $productItemCodes['code_mpn'];
        }
        
        if ($this->product->data['products_vpe_value'] > 0 && (int)$this->product->data['products_vpe']
            && $vpe = $this->getVPEById($this->product->data['products_vpe'])) {
            $linkedData['offers']['priceSpecification']['@type']             = 'http://schema.org/UnitPriceSpecification';
            $linkedData['offers']['priceSpecification']['referenceQuantity'] = [
                '@type'    => 'QuantitativeValue',
                'value'    => $this->product->data['products_vpe_value'],
                'unitText' => $vpe->getName($languageCode),
            ];
        }
        
        /** @var \xtcPrice_ORIGIN $xtcPrice */
        $xtcPrice = $this->getXtcPrice();
        
        $totalVariants = $this->getNumberOfVariants((int)$this->product->data['products_id']);
        if ($totalVariants < $this->getOfferAggregationLimit()) {
            $propertiesCombis = $this->getProductPropertiesCombis((int)$this->product->data['products_id']);
            if (!empty($propertiesCombis)) {
                $linkedData['model'] = [];
                foreach ($propertiesCombis as $combi) {
                    $productPrice      = $this->product->data['products_price'];
                    $combiProductId    = empty($combi['unique_products_id']) ? $this->product->data['products_id'] : $combi['unique_products_id'];
                    $combiPropertiesId = empty($combi['products_properties_combis_id']) ? 0 : (int)$combi['products_properties_combis_id'];
                    if (!empty($combiPropertiesId)) {
                        $combiPrice = $xtPrice->xtcGetPrice($combiProductId,
                                                            false,
                                                            1,
                                                            $this->product->data['products_tax_class_id'],
                                                            $productPrice,
                                                            0,
                                                            0,
                                                            true,
                                                            $combiPropertiesId > 0,
                                                            $combiPropertiesId);
                    }
                    if (!empty($combi['unique_products_id'])) {
                        $combiPrice = $xtPrice->getPprice($combiProductId);
                        if ($specialPriceData !== null) {
                            $combiPrice = $specialPriceData['specials_new_product_price'];
                        }
                        if ($combi['combi_price_type'] === 'calc') {
                            $combiPrice += $combi['combi_price'];
                        }
                        if ($combi['combi_price_type'] === 'fix') {
                            $combiPrice = $combi['combi_price'];
                        }
                        $combiPrice = $xtcPrice->xtcFormat($combiPrice,
                                                           false,
                                                           $this->product->data['products_tax_class_id'],
                                                           true);
                    }
                    $baseModel = !empty($this->product->data['products_model']) ? $this->product->data['products_model'] : '';
    
                    if (!empty($combi['combi_image'])) {
                        $combiImage = GM_HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES . 'product_images/properties_combis_images/'
                                      . rawurlencode($combi['combi_image']);
                    } else {
                        $combiImage = $productImage;
                    }
    
                    /*
                     * $this->product->data['use_properties_combis_quantity']
                     * 0 - global default
                     * 1 - product quantity
                     * 2 - combi quantity
                     * 3 - no check
                     */
                    $combiAvailability = null;
                    if (!empty($combi['products_properties_combis_id'])) {
                        // product variants via properties
                        $variantProductsId = $this->product->data['products_id'] . 'x'
                                             . $combi['products_properties_combis_id'];
                        if ($this->stockCheck
                            && (int)$this->product->data['use_properties_combis_quantity'] !== 3) {
                            if ((int)$this->product->data['use_properties_combis_quantity'] === 1
                                || ((int)$this->product->data['use_properties_combis_quantity'] === 0
                                    && !$this->attributeStockCheck)) {
                                // use product quantity
                                $combiAvailability = $availability;
                            } elseif ((int)$this->product->data['use_properties_combis_quantity'] === 2
                                      || ((int)$this->product->data['use_properties_combis_quantity'] === 0
                                          && $this->attributeStockCheck)) {
                                // use combi quantity
                                $combiAvailability = $combi['combi_quantity'] > 0 ? 'InStock' : 'OutOfStock';
                            }
                        } else {
                            // stock checks are disabled, assume InStock
                            $combiAvailability = 'InStock';
                        }
                    } else {
                        // product variants via attributes
                        $variantProductsId = $combi['unique_products_id'];
                        if ($this->stockCheck) {
                            if ($this->attributeStockCheck) {
                                $combiAvailability = $combi['combi_quantity'] > 0 ? 'InStock' : 'OutOfStock';
                            } else {
                                $combiAvailability = $availability;
                            }
                        } else {
                            $combiAvailability = 'InStock';
                        }
                    }
    
                    $variantUrl = xtc_href_link(FILENAME_PRODUCT_INFO,
                                                xtc_product_link($variantProductsId,
                                                                 $this->product->data['products_name'])
                                                . '&no_boost=1');
                    if (!empty($combi['products_properties_combis_id']) && $this->getSeoBoost()->boost_products === true) {
                        $variantUrl = xtc_href_link($this->getSeoBoost()
                                                        ->get_boosted_product_url($this->product->data['products_id'],
                                                                                  $this->product->data['products_name']),
                        'combi_id=' . $combi['products_properties_combis_id']);
                    }
                    
                    if (is_float($combiPrice)) {
                        $model = [
                            '@type'       => 'ProductModel',
                            'name'        => $this->product->data['products_name'],
                            //'description' => $description,
                            'model'       => $baseModel . ($this->appendPropertiesModel ? '-'
                                                                                          . $combi['combi_model'] : ''),
                            'sku'         => $baseModel . ($this->appendPropertiesModel ? '-'
                                                                                          . $combi['combi_model'] : ''),
                            'offers'      => [
                                '@type'              => 'Offer',
                                'price'              => number_format($combiPrice, 2, '.', ''),
                                'priceCurrency'      => $this->getCurrency(),
                                'priceSpecification' => [
                                    '@type'                 => 'http://schema.org/PriceSpecification',
                                    'price'                 => number_format($combiPrice, 2, '.', ''),
                                    'priceCurrency'         => $this->getCurrency(),
                                    'valueAddedTaxIncluded' => $taxIncluded,
                                ],
                                'url'         => $variantUrl,
                            ],
                            'url'         => $variantUrl,
                        ];
                        
                        if (!empty($combiImage)) {
                            $model['image'] = $combiImage;
                        }
    
                        if (!empty($brand)) {
                            $model['brand'] = $brand;
                        }
                        
                        if (!empty($priceValidUntil)) {
                            $model['offers']['priceValidUntil'] = $priceValidUntil;
                        }
                        
                        if (!empty($combiAvailability)) {
                            $model['offers']['availability'] = $combiAvailability;
                        }
                        
                        if (in_array(strlen($combi['combi_ean']), [8, 12, 13, 14], true)) {
                            $model['gtin' . strlen($combi['combi_ean'])] = $combi['combi_ean'];
                        }
                        
                        if ((int)$combi['products_vpe_id'] !== 0
                            && $vpe = $this->getVPEById((int)$combi['products_vpe_id'])) {
                            $model['offers']['priceSpecification']['@type']             = 'http://schema.org/UnitPriceSpecification';
                            $model['offers']['priceSpecification']['referenceQuantity'] = [
                                '@type'    => 'QuantitativeValue',
                                'value'    => $combi['vpe_value'],
                                'unitText' => $vpe->getName($languageCode),
                            ];
                        }
                        $linkedData['model'][] = $model;
                    }
                    unset($linkedData['availability']);
                }
            }
        } else {
            $productPrice                   = $this->product->data['products_price'];
            $db                             = StaticGXCoreLoader::getDatabaseQueryBuilder();
            $maxPropertiesCombisPriceResult = $db->query('SELECT products_id, MAX(IF(combi_price_type = \'fix\', combi_price, combi_price + '
                                                         . $productPrice
                                                         . ')) AS max_price FROM `products_properties_combis` WHERE products_id = '
                                                         . (int)$this->product->data['products_id']
                                                         . ' GROUP BY products_id');
            $minPropertiesCombisPriceResult = $db->query('SELECT products_id, MIN(IF(combi_price_type = \'fix\', combi_price, combi_price + '
                                                         . $productPrice
                                                         . ')) AS min_price FROM `products_properties_combis` WHERE products_id = '
                                                         . (int)$this->product->data['products_id']
                                                         . ' GROUP BY products_id');
            if ($maxPropertiesCombisPriceResult->num_rows() > 0 && $minPropertiesCombisPriceResult->num_rows() > 0) {
                $maxPropertiesCombisPriceRow = $maxPropertiesCombisPriceResult->row_array();
                $maxPropertiesCombisPrice    = $maxPropertiesCombisPriceRow['max_price'];
                $minPropertiesCombisPriceRow = $minPropertiesCombisPriceResult->row_array();
                $minPropertiesCombisPrice    = $minPropertiesCombisPriceRow['min_price'];
                $minCombiPrice               = $xtcPrice->xtcFormat($minPropertiesCombisPrice,
                                                                    false,
                                                                    $this->product->data['products_tax_class_id'],
                                                                    true);
                $maxCombiPrice               = $xtcPrice->xtcFormat($maxPropertiesCombisPrice,
                                                                    false,
                                                                    $this->product->data['products_tax_class_id'],
                                                                    true);
            } else {
                $maxAttributesPriceOffsetResult = $db->query('SELECT options_id, MAX(if(price_prefix = \'-\', -1, 1) * options_values_price) AS max_modifier FROM `products_attributes` where products_id = '
                                                             . (int)$this->product->data['products_id']
                                                             . ' GROUP BY options_id');
                $minAttributesPriceOffsetResult = $db->query('SELECT options_id, MIN(if(price_prefix = \'-\', -1, 1) * options_values_price) AS min_modifier FROM `products_attributes` where products_id = '
                                                             . (int)$this->product->data['products_id']
                                                             . ' GROUP BY options_id');
                
                $maxAttributesPriceOffset = 0;
                foreach ($maxAttributesPriceOffsetResult->result_array() as $maxAttributesPriceOffsetRow) {
                    $maxAttributesPriceOffset += $maxAttributesPriceOffsetRow['max_modifier'];
                }
                $minAttributesPriceOffset = 0;
                foreach ($minAttributesPriceOffsetResult->result_array() as $minAttributesPriceOffsetRow) {
                    $minAttributesPriceOffset += $minAttributesPriceOffsetRow['min_modifier'];
                }
                
                $minCombiPrice = $xtcPrice->xtcFormat($productPrice + $minAttributesPriceOffset,
                                                      false,
                                                      $this->product->data['products_tax_class_id'],
                                                      true);
                $maxCombiPrice = $xtcPrice->xtcFormat($productPrice + $maxAttributesPriceOffset,
                                                      false,
                                                      $this->product->data['products_tax_class_id'],
                                                      true);
            }
            
            $linkedData['offers'] = [
                '@type'         => 'AggregateOffer',
                'lowPrice'      => $minCombiPrice,
                'highPrice'     => $maxCombiPrice,
                'priceCurrency' => $_SESSION['currency'],
                'offerCount'    => $totalVariants,
                //'valueAddedTaxIncluded' => $taxIncluded,
            ];
        }
        
        $aggregateRating = $this->product->getAggregateRatingData();
        if ($aggregateRating['count'] > 0) {
            $linkedData['aggregateRating'] = [
                '@type'       => 'http://schema.org/AggregateRating',
                'ratingCount' => $aggregateRating['count'],
                'ratingValue' => $aggregateRating['averageRating'],
                'bestRating'  => 5,
                'worstRating' => 1,
            ];
            if (!empty($linkedData['model']) && is_array($linkedData['model'])) {
                foreach ($linkedData['model'] as $modelIdx => $model) {
                    $linkedData['model'][$modelIdx]['aggregateRating'] = $linkedData['aggregateRating'];
                }
            }
        }
        
        $reviews = $this->product->getReviews(PRODUCT_REVIEWS_VIEW);
        if (count($reviews) > 0) {
            $linkedData['review'] = [];
            foreach ($reviews as $review) {
                $linkedData['review'][] = [
                    '@type'         => 'http://schema.org/Review',
                    'datePublished' => $review['DATE_CLEAN'],
                    'author'        => [
                        '@type' => 'http://schema.org/Person',
                        'name'  => $review['AUTHOR'],
                    ],
                    'url'           => $review['URL'],
                    'reviewBody'    => $review['TEXT'],
                    'reviewRating'  => [
                        '@type'       => 'http://schema.org/Rating',
                        'ratingValue' => $review['RATING_CLEAN'],
                        'bestRating'  => 5,
                        'worstRating' => 1,
                    ]
                ];
            }
        }
        
        $jsonLD = json_encode($linkedData/*, JSON_PRETTY_PRINT*/);
        
        $this->set_content_data('JSONLD', $jsonLD);
    }
    
    
    /**
     * @param IdType $id
     * @return null|ManufacturerInterface
     */
    protected function getManufacturerById($id)
    {
        try {
            return $this->manufacturerReader()->getById(new IdType($id));
        } catch (EntityNotFoundException $e) {
            return null;
        }
    }

    /**
     * @return ManufacturerReadServiceInterface
     */
    protected function manufacturerReader()
    {
        if ($this->manufacturerReadService === null) {
            $this->manufacturerReadService = StaticGXCoreLoader::getService('ManufacturerRead');
        }
        return $this->manufacturerReadService;

    }

    /**
     * @param int $id
     * @return null|VPEInterface
     */
    protected function getVPEById($id)
    {
        try {
            return $this->VPEReadService()->getById(new IdType($id));
        } catch (EntityNotFoundException $e) {
            return null;
        }
    }

    /**
     * @return VPEReadServiceInterface
     */
    protected function VPEReadService()
    {
        if ($this->VPEReadService === null) {
            $this->VPEReadService = StaticGXCoreLoader::getService('VPERead');
        }
        return $this->VPEReadService;

    }

    /**
     * @param $productsId
     * @return int
     */
    protected function getNumberOfVariants($productsId)
    {
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        /* properties */
        $propertiesCountRow = $db->select('COUNT(*) AS num_combis')
            ->where('products_id', (int)$productsId)
            ->get('products_properties_combis')
            ->row_array();
        if ((int)$propertiesCountRow['num_combis'] > 0) {
            return (int)$propertiesCountRow['num_combis'];
        }

        $attribsCounts = $db->select('`options_id`, COUNT(`options_id`) AS num_values')
            ->where('products_id', (int)$productsId)
            ->group_by('options_id')
            ->get('products_attributes')
            ->result_array();
        $totalAttributes = 0;
        if (!empty($attribsCounts)) {
            $totalAttributes = 1;
            foreach ($attribsCounts as $attribCount) {
                $totalAttributes *= $attribCount['num_values'];
            }
        }

        return $totalAttributes;
    }

    /**
     * @param $productsId
     * @return array
     */
    protected function getProductPropertiesCombis($productsId)
    {
        /* properties */
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $combis = $db->where('products_id', (int)$productsId)
            ->order_by('sort_order', 'ASC')
            ->get('products_properties_combis')
            ->result_array();
        if (!empty($combis)) {
            return $combis;
        }

        /* attributes */
        $attributes = $db->where('products_id', (int)$productsId)
            ->order_by('options_values_id', 'ASC')
            ->order_by('sortorder', 'ASC')
            ->get('products_attributes')
            ->result_array();
        if (!empty($attributes)) {
            $attributesLists = [];
            $attributeModels = [];
            $attributePrices = [];
            $attributeStocks = [];
            $attributeEans = [];
            $optionsWithEans = [];
            $combiVpeId = 0;
            $combiVpeValue = 0.0;
            foreach ($attributes as $attribute) {
                $optionsId = $attribute['options_id'];
                $valuesId = $attribute['options_values_id'];
                if (!array_key_exists($optionsId, $attributesLists)) {
                    $attributesLists[$optionsId] = [];
                }
                $attributesLists[$optionsId][] = $valuesId;
                $attributeModels[$optionsId . '-' . $valuesId] = $attribute['attributes_model'];
                $priceFactor = $attribute['price_prefix'] === '-' ? -1 : 1;
                $attributePrices[$optionsId . '-' . $valuesId] = $priceFactor * $attribute['options_values_price'];
                $attributeStocks[$optionsId . '-' . $valuesId] = $attribute['attributes_stock'];
                $gmEan = trim($attribute['gm_ean']);
                if (!empty($gmEan)) {
                    $optionsWithEans[$optionsId] = true;
                    $attributeEans[$optionsId . '-' . $valuesId] = $gmEan;
                }
                if ($combiVpeId === 0 && (int)$attribute['products_vpe_id'] !== 0) {
                    $combiVpeId = (int)$attribute['products_vpe_id'];
                    $combiVpeValue = (float)$attribute['gm_vpe_value'];
                }
            }
            if (count($optionsWithEans) > 1) {
                $attributeEans = [];
            }
            $options = array_keys($attributesLists);
            $counts = [];
            foreach ($options as $optionId) {
                $counts[] = count($attributesLists[$optionId]);
            }

            $attribSet = array_fill(0, count($attributesLists), 0);
            $attributeCombis = [];
            do {
                $optionsToValues = [];
                foreach ($options as $oIndex => $optionId) {
                    $valuesId = $attributesLists[$options[$oIndex]][$attribSet[$oIndex]];
                    $optionsToValues[$optionId] = $valuesId;
                }
                $uPID = (string)$productsId;
                $combiModel = '';
                $combiPrice = 0.0;
                $combiQuantity = 999999;
                $combiEan = '';
                foreach ($optionsToValues as $optionsId => $valuesId) {
                    $uPID .= '{' . $optionsId . '}' . $valuesId;
                    $combiModel .= empty($combiModel) ? '' : '-';
                    $combiModel .= $attributeModels[$optionsId . '-' . $valuesId];
                    $combiPrice += $attributePrices[$optionsId . '-' . $valuesId];
                    $combiQuantity = min($combiQuantity, (float)$attributeStocks[$optionsId . '-' . $valuesId]);
                    $combiEan = !empty($attributeEans[$optionsId . '-' . $valuesId]) ? $attributeEans[$optionsId . '-' . $valuesId] : $combiEan;
                }
                $attributeCombis[] = [
                    'products_id' => $productsId,
                    'unique_products_id' => $uPID,
                    'combi_model' => $combiModel,
                    'combi_ean' => $combiEan,
                    'combi_quantity' => $combiQuantity,
                    'combi_price_type' => 'calc',
                    'combi_price' => $combiPrice,
                    'products_vpe_id' => $combiVpeId,
                    'vpe_value' => $combiVpeValue,
                ];
            } while ($this->incrementArray($attribSet, $counts) !== false);

            return $attributeCombis;
        }

        return [];
    }


    /**
     * Helper for iterating through combinations of attributes.
     *
     * @param $attribSet
     * @param $counts
     *
     * @return bool
     */
    protected function incrementArray(&$attribSet, $counts)
    {
        $incPos = 0;
        while (true) {
            if ($attribSet[$incPos] < $counts[$incPos] - 1) {
                $attribSet[$incPos]++;
                break;
            } else {
                if ($incPos < count($counts) - 1) {
                    $attribSet[$incPos] = 0;
                    $incPos++;
                } else {
                    return false;
                }
            }
        }

        return true;
    }


    protected function _assignItemCondition()
    {
        $itemCondition = 'new';

        $query = 'SELECT `google_export_condition`
                  FROM `products_item_codes`
                  WHERE `products_id` = ' . (int)$this->product->data['products_id'];

        $result = xtc_db_query($query);

        if (xtc_db_num_rows($result)) {
            $row = xtc_db_fetch_array($result);

            if ($row['google_export_condition'] === 'gebraucht') {
                $itemCondition = 'used';
            } elseif ($row['google_export_condition'] === 'erneuert') {
                $itemCondition = 'refurbished';
            }
        }

        $this->set_content_data('ITEM_CONDITION', $itemCondition);
    }


    protected function _assignReviews()
    {
        // Aggregate review data
        $this->set_content_data('AGGREGATE_REVIEW_DATA', $this->product->getAggregateRatingData());

        /* @var ProductReviewsContentView $view */
        $view = MainFactory::create_object('ProductReviewsContentView');
        $view->setProduct($this->product);
        $html = $view->get_html();
        if (trim($html) !== '') {
            $this->set_content_data('MODULE_products_reviews', $html);
        }
    }


    protected function _assignProductUrl()
    {
        if (xtc_not_null($this->product->data['products_url'])) {
            $this->set_content_data('PRODUCTS_URL', sprintf(TEXT_MORE_INFORMATION, xtc_href_link(FILENAME_REDIRECT,
                'action=product&id='
                . $this->product->data['products_id'],
                'NONSSL', true)));
        }
    }


    protected function _assignDate()
    {
        if ($this->product->data['products_date_available'] > date('Y-m-d H:i:s')) {
            $this->set_content_data('PRODUCTS_DATE_AVIABLE', sprintf(TEXT_DATE_AVAILABLE,
                xtc_date_long($this->product->data['products_date_available'])));
        } else {
            if ($this->product->data['products_date_added'] != '1000-01-01 00:00:00'
                && $this->product->data['gm_show_date_added'] == 1) {
                $this->set_content_data('PRODUCTS_ADDED', sprintf(TEXT_DATE_ADDED,
                    xtc_date_long($this->product->data['products_date_added'])));
            }
        }
    }


    protected function _assignGraduatedPrices()
    {
        /* @var GraduatedPricesContentView $view */
        $view = MainFactory::create_object('GraduatedPricesContentView');
        $view->set_coo_product($this->product);
        $view->set_customers_status_graduated_prices((int)$this->showGraduatedPrices);
        $html = $view->get_html();
        $this->set_content_data('MODULE_graduated_price', $html);
    }


    // TODO move out of view into control
    protected function _updateTracking()
    {
        $i = is_array($_SESSION['tracking']['products_history']) ? count($_SESSION['tracking']['products_history']) : 0;
        if ($i > 6) {
            array_shift($_SESSION['tracking']['products_history']);
            $_SESSION['tracking']['products_history'][6] = $this->product->data['products_id'];
            $_SESSION['tracking']['products_history'] = array_unique($_SESSION['tracking']['products_history']);
        } else {
            $_SESSION['tracking']['products_history'][$i] = $this->product->data['products_id'];
            $_SESSION['tracking']['products_history'] = array_unique($_SESSION['tracking']['products_history']);
        }
    }


    protected function _assignPrintLink()
    {
        if (gm_get_conf('SHOW_PRINT') == 'true') {
            $this->set_content_data('SHOW_PRINT', 1);
        }

        $this->_assignDeprecatedPrintLink();
    }


    protected function _assignSocialServices()
    {
        $this->_assignFacebook();
        $this->_assignWhatsApp();
        $this->_assignTwitter();
        $this->_assignPinterest();
    }


    protected function _assignFacebook()
    {
        if (gm_get_conf('SHOW_FACEBOOK') == 'true') {
            $this->set_content_data('SHOW_FACEBOOK', 1);
        }
    }


    protected function _assignWhatsApp()
    {
        if (gm_get_conf('SHOW_WHATSAPP') == 'true') {
            $this->set_content_data('SHOW_WHATSAPP', 1);
        }
    }


    protected function _assignTwitter()
    {
        if (gm_get_conf('SHOW_TWITTER') == 'true') {
            $this->set_content_data('SHOW_TWITTER', 1);
        }
    }


    protected function _assignPinterest()
    {
        if (gm_get_conf('SHOW_PINTEREST') == 'true') {
            $this->set_content_data('SHOW_PINTEREST', 1);
        }
    }


    protected function _assignImageMaxHeight()
    {
        $this->set_content_data('IMAGE_MAX_HEIGHT', $this->maxImageHeight);
    }


    protected function _assignImageData()
    {
        $imagesDataArray = [];
        $thumbnailsDataArray = [];

        /* @var GMAltText $altTextManager */
        $altTextManager = MainFactory::create_object('GMAltText');

        if ($this->product->data['products_image'] != '' && $this->product->data['gm_show_image'] == '1') {
            $imageArray = array(
                'image_name' => $this->product->data['products_image'],
                'image_id' => 0,
                'image_nr' => 0
            );

            $imagesDataArray[] = $this->_buildImageArray($imageArray, $altTextManager);
            $thumbnailsDataArray[] = $this->_buildThumbnailArray($imageArray, $altTextManager);
        }

        $additionalImagesArray = xtc_get_products_mo_images($this->product->data['products_id']);

        if (is_array($additionalImagesArray) && !empty($additionalImagesArray)) {
            foreach ($additionalImagesArray as $imageArray) {
                $imagesDataArray[] = $this->_buildImageArray($imageArray, $altTextManager);
                $thumbnailsDataArray[] = $this->_buildThumbnailArray($imageArray, $altTextManager);
            }
        }

        $this->set_content_data('images', $imagesDataArray);
        $this->set_content_data('thumbnails', $thumbnailsDataArray);

        $this->_assignDeprecatedDimensionValues();
    }


    /**
     * GX-Customizer
     */
    protected function _assignGPrint()
    {
        $gPrintProductManager = new GMGPrintProductManager();

        if ($gPrintProductManager->get_surfaces_groups_id($this->product->data['products_id']) !== false) {
            $gPrintConfiguration = new GMGPrintConfiguration($this->languageId);
            
            $this->set_content_data('GM_GPRINT', 1);
        }
    }


    /**
     * assign formated price or link to contact form if price status is "Preis auf Anfrage"
     */
    protected function _assignPrice()
    {
        $this->set_content_data('PRODUCTS_PRICE', $this->productPriceArray['formated']);

        if ($this->product->data['gm_price_status'] == 1) {
            $seoBoost = MainFactory::create_object('GMSEOBoost', [], true);
            $sefParameter = '';

            $query = "SELECT
							content_id,
							content_title
						FROM " . TABLE_CONTENT_MANAGER . "
						WHERE
							languages_id = '" . (int)$this->languageId . "' AND
							content_group = '7'";
            $result = xtc_db_query($query);
            if (xtc_db_num_rows($result)) {
                $row = xtc_db_fetch_array($result);
                $contactContentId = $row['content_id'];
                $contactContentTitle = $row['content_title'];

                if (defined('SEARCH_ENGINE_FRIENDLY_URLS') && SEARCH_ENGINE_FRIENDLY_URLS === 'false') {
                    $sefParameter = '&content=' . xtc_cleanName($contactContentTitle);
                }
            }
            if ($seoBoost->boost_content) {
                $contactUrl = xtc_href_link($seoBoost->get_boosted_content_url($contactContentId, $this->languageId)
                    . '?subject=' . rawurlencode(GM_SHOW_PRICE_ON_REQUEST . ': '
                        . $this->product->data['products_name']));
            } else {
                $contactUrl = xtc_href_link(FILENAME_CONTENT,
                    'coID=7&subject=' . rawurlencode(GM_SHOW_PRICE_ON_REQUEST . ': '
                        . $this->product->data['products_name'])
                    . $sefParameter);
            }

            $contactLinkHtml = '<a href="' . $contactUrl . '" class="btn btn-lg btn-price-on-request price-on-request">'
                . GM_SHOW_PRICE_ON_REQUEST . '</a>';

            $this->set_content_data('PRODUCTS_PRICE', $contactLinkHtml);
        }
    }


    protected function _assignDiscount()
    {
        if ($this->customerDiscount != 0) {
            $discount = $this->customerDiscount;

            if ($this->product->data['products_discount_allowed'] < $this->customerDiscount) {
                $discount = (double)$this->product->data['products_discount_allowed'];
            }

            if ($discount != 0) {
                $this->set_content_data('PRODUCTS_DISCOUNT', $discount . '%');
            }
        }
    }


    protected function _assignDescription()
    {
        /* @var GMTabTokenizer $tabTokenizer */
        $tabTokenizer = MainFactory::create_object('GMTabTokenizer',
            array(stripslashes($this->product->data['products_description'])));
        $description = $tabTokenizer->get_prepared_output();

        $this->set_content_data('PRODUCTS_DESCRIPTION', $description);
        $this->set_content_data('description', $tabTokenizer->head_content);

        $tabs = array();
        foreach ($tabTokenizer->tab_content as $key => $value) {
            $tabs[] = array('title' => strip_tags($value), 'content' => $tabTokenizer->panel_content[$key]);
        }

        $mediaContent = $this->_getMediaContentHtml();
        if (trim($mediaContent) !== '') {
            $languageTextManager = MainFactory::create_object('LanguageTextManager',
                array('products_media', $this->languageId));
            $tabs[] = array(
                'title' => $languageTextManager->get_text('text_media_content_tab'),
                'content' => $mediaContent
            );
        }

        $this->set_content_data('tabs', $tabs);
    }


    /**
     * @return bool
     */
    protected function _productIsForSale()
    {
        return ($this->showPrice
                && $this->xtcPrice->gm_check_price_status($this->product->data['products_id']) == 0)
            && ($this->product->data['products_fsk18'] == '0' || $this->fsk18PurchaseAllowed);
    }


    /**
     * @param array $imageArray
     * @param GMAltText $altTextManager
     *
     * @return array
     */
    protected function _buildImageArray(array $imageArray, GMAltText $altTextManager)
    {
        $imageMaxWidth = 369;
        $imageMaxHeight = 279;

        $infoImageSizeArray = @getimagesize(DIR_WS_INFO_IMAGES . $imageArray['image_name']);

        $imagePaddingLeft = 0;
        $imagePaddingTop = 0;

        if (isset($infoImageSizeArray[0]) && $infoImageSizeArray[0] < $imageMaxWidth) {
            $imagePaddingLeft = round(($imageMaxWidth - $infoImageSizeArray[0]) / 2);
        }

        if (isset($infoImageSizeArray[1]) && $infoImageSizeArray[1] < $imageMaxHeight) {
            $imagePaddingTop = round(($imageMaxHeight - $infoImageSizeArray[1]) / 2);
        }

        if ($this->maxImageHeight < $infoImageSizeArray[1]) {
            $this->maxImageHeight = $infoImageSizeArray[1];
        }

        $zoomImageFilepath = DIR_WS_POPUP_IMAGES . $imageArray['image_name'];

        if (file_exists(DIR_WS_ORIGINAL_IMAGES . $imageArray['image_name'])) {
            $zoomImageFilepath = DIR_WS_ORIGINAL_IMAGES . $imageArray['image_name'];
        }

        $imageDataArray = array(
            'IMAGE' => DIR_WS_INFO_IMAGES . $imageArray['image_name'],
            'IMAGE_ALT' => $altTextManager->get_alt($imageArray["image_id"], $imageArray['image_nr'],
                $this->product->data['products_id']),
            'IMAGE_NR' => $imageArray['image_nr'],
            'ZOOM_IMAGE' => $zoomImageFilepath,
            'PRODUCTS_NAME' => $this->product->data['products_name'],
            'PADDING_LEFT' => $imagePaddingLeft,
            'PADDING_TOP' => $imagePaddingTop,
            'IMAGE_POPUP_URL' => DIR_WS_POPUP_IMAGES . $imageArray['image_name'],
            'WIDTH' => $infoImageSizeArray[0],
            'HEIGHT' => $infoImageSizeArray[1]

        );

        return $imageDataArray;
    }


    /**
     * @param array $imageArray
     * @param GMAltText $altTextManager
     *
     * @return array
     */
    protected function _buildThumbnailArray(array $imageArray, GMAltText $altTextManager)
    {
        $thumbnailMaxWidth = 86;
        $thumbnailMaxHeight = 86;

        $thumbnailImageSizeArray = @getimagesize(DIR_WS_IMAGES . 'product_images/gallery_images/'
            . $imageArray['image_name']);

        $thumbnailPaddingLeft = 0;
        $thumbnailPaddingTop = 0;

        if (isset($thumbnailImageSizeArray[0]) && $thumbnailImageSizeArray[0] < $thumbnailMaxWidth) {
            $thumbnailPaddingLeft = round(($thumbnailMaxWidth - $thumbnailImageSizeArray[0]) / 2);
        }

        if (isset($thumbnailImageSizeArray[1]) && $thumbnailImageSizeArray[1] < $thumbnailMaxHeight) {
            $thumbnailPaddingTop = round(($thumbnailMaxHeight - $thumbnailImageSizeArray[1]) / 2);
        }

        $zoomImageFilepath = DIR_WS_POPUP_IMAGES . $imageArray['image_name'];

        if (file_exists(DIR_WS_ORIGINAL_IMAGES . $imageArray['image_name'])) {
            $zoomImageFilepath = DIR_WS_ORIGINAL_IMAGES . $imageArray['image_name'];
        }

        $thumbnailDataArray = array(
            'IMAGE' => DIR_WS_IMAGES . 'product_images/gallery_images/' . $imageArray['image_name'],
            'IMAGE_ALT' => $altTextManager->get_alt($imageArray["image_id"], $imageArray['image_nr'],
                $this->product->data['products_id']),
            'IMAGE_NR' => $imageArray['image_nr'],
            'ZOOM_IMAGE' => $zoomImageFilepath,
            'INFO_IMAGE' => DIR_WS_INFO_IMAGES . $imageArray['image_name'],
            'PRODUCTS_NAME' => $this->product->data['products_name'],
            'PADDING_LEFT' => $thumbnailPaddingLeft,
            'PADDING_TOP' => $thumbnailPaddingTop
        );

        return $thumbnailDataArray;
    }


    protected function _assignInputFieldQuantity()
    {
        $this->set_content_data('QUANTITY', gm_convert_qty($this->product->data['gm_min_order'], true));
        $this->set_content_data('DISABLED_QUANTITY', 0);

        if ((double)$this->product->data['gm_min_order'] != 1) {
            $this->set_content_data('GM_MIN_ORDER', gm_convert_qty($this->product->data['gm_min_order'], false));
        }

        $quantityStepping = (double)$this->product->data['gm_graduated_qty'];
        if ((double)$this->product->data['gm_graduated_qty'] != 1) {
            $this->set_content_data('GM_GRADUATED_QTY',
                gm_convert_qty($this->product->data['gm_graduated_qty'], false));
        }
        $this->set_content_data('QTY_STEPPING', $quantityStepping);

        $this->_assignDeprecatedPurchaseData();
    }


    protected function _assignWishlist()
    {
        if (gm_get_conf('GM_SHOW_WISHLIST') == 'true') {
            $this->set_content_data('SHOW_WISHLIST', 1);
        } else {
            $this->set_content_data('SHOW_WISHLIST', 0);
        }

        $this->_assignDeprecatedWishlist();
    }


    protected function _assignLegalAgeFlag()
    {
        if ($this->product->data['products_fsk18'] == '1') {
            $this->set_content_data('PRODUCTS_FSK18', 'true');
        }
    }


    /**
     * @return bool
     */
    protected function _showPrice()
    {
        return $this->showPrice
            && ($this->xtcPrice->gm_check_price_status($this->product->data['products_id']) == 0
                || ($this->xtcPrice->gm_check_price_status($this->product->data['products_id']) == 2
                    && $this->product->data['products_price'] > 0));
    }


    protected function _assignTaxInfo()
    {
        // price incl tax
        $tax_rate = $this->xtcPrice->TAX[$this->product->data['products_tax_class_id']];
        $tax_info = $this->main->getTaxInfo($tax_rate);
        $this->set_content_data('PRODUCTS_TAX_INFO', $tax_info);
    }


    protected function _assignShippingLink()
    {
        if ($this->xtcPrice->gm_check_price_status($this->product->data['products_id']) == 0) {
            $this->set_content_data('PRODUCTS_SHIPPING_LINK',
                $this->main->getShippingLink(true, $this->product->data['products_id']));
        }
    }


    protected function _assignTellAFriend()
    {
        if (gm_get_conf('GM_TELL_A_FRIEND') == 'true') {
            $this->set_content_data('GM_TELL_A_FRIEND', 1);
        }
    }


    protected function _assignPriceOffer()
    {
        if ($this->product->data['gm_show_price_offer'] == 1
            && $this->showPrice
            && $this->xtcPrice->gm_check_price_status($this->product->data['products_id']) == 0) {
            $this->set_content_data('GM_PRICE_OFFER', 1);
        }
    }


    protected function _assignStatus()
    {
        $this->set_content_data('PRODUCTS_STATUS', $this->product->data['products_status']);
    }


    protected function _assignNumberOfOrders()
    {
        $this->set_content_data('PRODUCTS_ORDERED', $this->product->data['products_ordered']);
    }


    protected function _assignFormTagData()
    {
        $this->set_content_data('FORM_ACTION_URL', xtc_href_link(FILENAME_PRODUCT_INFO,
            xtc_get_all_get_params(array('action'))
            . 'action=add_product', 'NONSSL', true, true, true));
        $this->set_content_data('FORM_ID', 'cart_quantity');
        $this->set_content_data('FORM_NAME', 'cart_quantity');
        $this->set_content_data('FORM_METHOD', 'post');

        $this->_assignDeprecatedFormTagData();
    }


    protected function _setPropertiesData()
    {
        $coo_stop_watch = LogControl::get_instance()->get_stop_watch();
        $coo_stop_watch->start('PropertiesView get_selection_form');

        $propertiesSelectionForm = $this->_buildPropertiesSelectionForm();
        $this->_assignPropertiesSelectionForm($propertiesSelectionForm);

        $coo_stop_watch->stop('PropertiesView get_selection_form');
        //$coo_stop_watch->log_total_time('PropertiesView get_selection_form');

        $this->hasProperties = trim($propertiesSelectionForm) != "";
    }


    protected function _assignModelNumber()
    {
        $modelNumber = $this->product->data['products_model'];

        if ($this->hasProperties) {
            // OVERRIDE PRODUCTS MODEL
            if ($this->currentCombiArray != false) {
                if (APPEND_PROPERTIES_MODEL == "true" && trim($this->currentCombiArray['combi_model']) != '') {
                    if (trim($modelNumber) != '') {
                        $modelNumber .= '-';
                    }
                    $modelNumber .= $this->currentCombiArray['combi_model'];
                } else {
                    if (APPEND_PROPERTIES_MODEL == "false" && trim($this->currentCombiArray['combi_model']) != '') {
                        $modelNumber = $this->currentCombiArray['combi_model'];
                    }
                }
            }
        }

        $this->set_content_data('SHOW_PRODUCTS_MODEL',
            gm_get_conf('SHOW_PRODUCTS_MODEL_IN_PRODUCT_DETAILS') === 'true');
        $this->set_content_data('PRODUCTS_MODEL', $modelNumber);
    }


    protected function _assignQuantity()
    {
        $quantity = 0;
        $quantityUnit = '';

        if ($this->product->data['gm_show_qty_info'] == 1) {
            $quantity = gm_convert_qty(xtc_get_products_stock($this->product->data['products_id']), false);
        }

        if ($this->product->data['quantity_unit_id'] > 0) {
            $quantityUnit = $this->product->data['unit_name'];
        }

        if ($this->hasProperties && $this->product->data['gm_show_qty_info'] == 1) {
            // OVERRIDE PRODUCTS QUANTITY
            if (($this->product->data['use_properties_combis_quantity'] == 0
                    && STOCK_CHECK == 'true'
                    && ATTRIBUTE_STOCK_CHECK == 'true')
                || $this->product->data['use_properties_combis_quantity'] == 2) {
                $quantity = $this->currentCombiArray['combi_quantity'];
                $this->set_content_data('SHOW_PRODUCTS_QUANTITY', true);

                if (empty($this->currentCombiArray)) {
                    $this->set_content_data('SHOW_PRODUCTS_QUANTITY', true);
                    $quantity = '-';
                }
            }
        }

        $this->set_content_data('PRODUCTS_QUANTITY', $quantity);
        $this->set_content_data('PRODUCTS_QUANTITY_UNIT', $quantityUnit);
    }


    protected function _assignDeactivatedButtonFlag()
    {
        $deactivateButton = false;

        if ($this->hasProperties) {
            if ($this->currentCombiArray == false) {
                $deactivateButton = true;
            } else {
                if ($this->product->data['gm_show_qty_info'] == 1) {
                    if (($this->product->data['use_properties_combis_quantity'] == 0
                            && STOCK_CHECK == 'true'
                            && ATTRIBUTE_STOCK_CHECK == 'true')
                        || $this->product->data['use_properties_combis_quantity'] == 2) {
                        if ($this->currentCombiArray['combi_quantity'] < gm_convert_qty($this->product->data['gm_min_order'],
                                false)
                            && STOCK_ALLOW_CHECKOUT == 'false') {
                            $deactivateButton = true;
                        }
                    }
                }
            }
        }

        $this->set_content_data('DEACTIVATE_BUTTON', $deactivateButton);
    }


    protected function _assignWeight()
    {
        $showWeight = 0;
        $weight = 0;

        if ($this->product->data['gm_show_weight'] == '1') {
            $showWeight = 1;
            $weight = gm_prepare_number($this->product->data['products_weight'],
                $this->xtcPrice->currencies[$this->xtcPrice->actualCurr]['decimal_point']);

            if ($this->hasProperties) {
                // OVERRIDE WEIGHT
                $weight = '-';

                if ($this->currentCombiArray != false) {
                    if ($this->product->data['use_properties_combis_weight'] == 0) {
                        $weight = gm_prepare_number($this->currentCombiArray['combi_weight']
                            + $this->product->data['products_weight'],
                            $this->xtcPrice->currencies[$this->xtcPrice->actualCurr]['decimal_point']);
                    } else {
                        $weight = gm_prepare_number($this->currentCombiArray['combi_weight'],
                            $this->xtcPrice->currencies[$this->xtcPrice->actualCurr]['decimal_point']);
                    }
                }
            }
        }

        $this->set_content_data('SHOW_PRODUCTS_WEIGHT', $showWeight);
        $this->set_content_data('PRODUCTS_WEIGHT', $weight);
    }


    protected function _assignVpe()
    {
        $vpeHtml = '';

        if ($this->product->data['products_vpe_status'] == 1 && $this->product->data['products_vpe_value'] != 0.0
            && $this->productPriceArray['plain'] > 0) {
            $price = $this->productPriceArray['plain'] * (1 / $this->product->data['products_vpe_value']);
            $priceFormatted = $this->xtcPrice->xtcFormat($price, true);
            $vpeName = xtc_get_vpe_name($this->product->data['products_vpe']);
            $vpeHtml = $priceFormatted . TXT_PER . $vpeName;

            if ($this->hasProperties) {
                $propertiesControl = MainFactory::create('PropertiesControl');

                // OVERRIDE VPE
                if (!empty($this->currentCombiArray) && $this->currentCombiArray['products_vpe_id'] > 0
                    && $this->currentCombiArray['vpe_value'] != 0) {
                    $vpeHtml = $this->_buildVpeHtml($this->productPriceArray['plain'],
                        $this->currentCombiArray['products_vpe_id'],
                        $this->currentCombiArray['vpe_value']);
                } elseif ($this->cheapestCombiArray['products_vpe_id'] > 0 && $this->cheapestCombiArray['vpe_value'] != 0
                    && !$propertiesControl->has_non_linear_combi_surcharge((int)$this->product->data['products_id'])) {
                    $vpeHtml = $this->_buildVpeHtml($this->productPriceArray['plain'],
                        $this->cheapestCombiArray['products_vpe_id'],
                        $this->cheapestCombiArray['vpe_value']);
                } elseif ($propertiesControl->has_non_linear_combi_surcharge((int)$this->product->data['products_id'])) {
                    $vpeHtml = '';
                }
            }
        }

        $this->set_content_data('PRODUCTS_VPE', $vpeHtml);
    }


    protected function _buildVpeHtml($price, $vpeId, $vpeValue)
    {
        $vpePrice = $price * (1 / $vpeValue);
        $priceFormatted = $this->xtcPrice->xtcFormat($vpePrice, true);
        $vpeName = xtc_get_vpe_name($vpeId);

        return $priceFormatted . TXT_PER . $vpeName;
    }

    /**
     *
     */
    protected function _assignShippingTime()
    {
        $languageTextManager = MainFactory::create('LanguageTextManager', 'product_info');

        $name = '';
        $image = '';
        $imageAlt = '';

        if (ACTIVATE_SHIPPING_STATUS == 'true'
            && $this->xtcPrice->gm_check_price_status($this->product->data['products_id']) == 0) {
            $name = $this->main->getShippingStatusName($this->product->data['products_shippingtime']);
            $image = $this->main->getShippingStatusImage($this->product->data['products_shippingtime']);
        }

        if ($this->hasProperties) {

            // OVERRIDE SHIPPING STATUS
            if (ACTIVATE_SHIPPING_STATUS == 'true'
                && $this->xtcPrice->gm_check_price_status($this->product->data['products_id']) == 0
                && $this->product->data['use_properties_combis_shipping_time'] == 1) {
                if ($this->currentCombiArray != false) {
                    $name = $this->main->getShippingStatusName($this->currentCombiArray['combi_shipping_status_id']);
                    $image = $this->main->getShippingStatusImage($this->currentCombiArray['combi_shipping_status_id']);
                } else {
                    $name = '';
                    $image = 'images/icons/status/gray.png';
                }
                $this->set_content_data('SHOW_SHIPPING_TIME', true);
            }
        }

        $imageAlt = ($name !== '') ? $name : $languageTextManager->get_text('unknown_shippingtime');

        $this->set_content_data('SHIPPING_NAME', $name);
        $this->set_content_data('SHIPPING_IMAGE', $image);
        $this->set_content_data('SHIPPING_IMAGE_ALT', $imageAlt);
        $this->set_content_data('ABROAD_SHIPPING_INFO_LINK_ACTIVE',
            $this->main->getShippingStatusInfoLinkActive($this->product->data['products_shippingtime']));
        $this->set_content_data('ABROAD_SHIPPING_INFO_LINK', main::get_abroad_shipping_info_link());
    }


    protected function _assignEan()
    {
        $this->set_content_data('PRODUCTS_EAN', $this->product->data['products_ean']);
    }


    protected function _assignId()
    {
        $this->set_content_data('PRODUCTS_ID', $this->product->data['products_id']);
    }


    protected function _assignName()
    {
        $this->set_content_data('PRODUCTS_NAME', $this->product->data['products_name']);
    }


    /**
     * @return string
     */
    protected function _buildPropertiesSelectionForm()
    {
        /* @var PropertiesView $view */
        $view = MainFactory::create_object('PropertiesView',
            array($this->getArray, $this->postArray));
        $propertiesSelectionForm = $view->get_selection_form($this->product->data['products_id'], $this->languageId,
            false, $this->currentCombiArray);

        return $propertiesSelectionForm;
    }


    /**
     * @param $propertiesSelectionForm
     */
    protected function _assignPropertiesSelectionForm($propertiesSelectionForm)
    {
        $this->set_content_data('properties_selection_form', $propertiesSelectionForm);
    }


    protected function _assignAdditionalFields()
    {
        $additionalFieldsHtml = '';

        if (gm_get_conf('SHOW_ADDITIONAL_FIELDS_PRODUCT_DETAILS') === 'true') {
            $view = MainFactory::create_object('AdditionalFieldContentView');
            $view->setLanguageId($this->languageId);
            $view->setAdditionalFields($this->additionalFields);
            $additionalFieldsHtml = $view->get_html();
        }

        $this->set_content_data('additional_fields', $additionalFieldsHtml);
    }


    ##### SETTER / GETTER #####


    /**
     * @return array
     */
    public function getGetArray()
    {
        return $this->getArray;
    }


    /**
     * $_GET-Data
     *
     * @param array $getArray
     */
    public function setGetArray(array $getArray)
    {
        $this->getArray = $getArray;
    }


    /**
     * @return array
     */
    public function getPostArray()
    {
        return $this->postArray;
    }


    /**
     * $_POST-Data
     *
     * @param array $postArray
     */
    public function setPostArray(array $postArray)
    {
        $this->postArray = $postArray;
    }


    /**
     * @param product $product
     */
    public function setProduct(product $product)
    {
        $this->product = $product;
    }


    /**
     * @return product
     */
    public function getProduct()
    {
        return $this->product;
    }


    /**
     * @param int $p_categoryId
     */
    public function setCurrentCategoryId($p_categoryId)
    {
        $this->currentCategoryId = (int)$p_categoryId;
    }


    /**
     * @return int
     */
    public function getCurrentCategoryId()
    {
        return $this->currentCategoryId;
    }


    /**
     * @return int
     */
    public function getCombiId()
    {
        return $this->combiId;
    }


    /**
     * @param int $p_combiId
     */
    public function setCombiId($p_combiId)
    {
        $this->combiId = (int)$p_combiId;
    }


    /**
     * @return int
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }


    /**
     * @param int $p_languageId
     */
    public function setLanguageId($p_languageId)
    {
        $this->languageId = (int)$p_languageId;
    }


    /**
     * @return main
     */
    public function getMain()
    {
        return $this->main;
    }


    /**
     * @param mixed $main
     */
    public function setMain(main $main)
    {
        $this->main = $main;
    }


    /**
     * @return xtcPrice
     */
    public function getXtcPrice()
    {
        return $this->xtcPrice;
    }


    /**
     * @param xtcPrice $xtcPrice
     */
    public function setXtcPrice(xtcPrice $xtcPrice)
    {
        $this->xtcPrice = $xtcPrice;
    }


    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }


    /**
     * @param string $p_currency
     */
    public function setCurrency($p_currency)
    {
        $this->currency = (string)$p_currency;
    }


    /**
     * @return boolean
     */
    public function getShowGraduatedPrices()
    {
        return $this->showGraduatedPrices;
    }


    /**
     * @param boolean $p_showGraduatedPrices
     */
    public function setShowGraduatedPrices($p_showGraduatedPrices)
    {
        $this->showGraduatedPrices = (bool)$p_showGraduatedPrices;
    }


    /**
     * @return double
     */
    public function getCustomerDiscount()
    {
        return $this->customerDiscount;
    }


    /**
     * @param double $p_customerDiscount
     */
    public function setCustomerDiscount($p_customerDiscount)
    {
        $this->customerDiscount = (double)$p_customerDiscount;
    }


    /**
     * @return boolean
     */
    public function getShowPrice()
    {
        return $this->showPrice;
    }


    /**
     * @param boolean $p_showPrice
     */
    public function setShowPrice($p_showPrice)
    {
        $this->showPrice = (bool)$p_showPrice;
    }


    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }


    /**
     * @param string $p_language
     */
    public function setLanguage($p_language)
    {
        $this->language = basename((string)$p_language);
    }


    /**
     * @return boolean
     */
    public function getFSK18PurchaseAllowed()
    {
        return $this->fsk18PurchaseAllowed;
    }


    /**
     * @param boolean $p_FSK18PurchaseAllowed
     */
    public function setFSK18PurchaseAllowed($p_FSK18PurchaseAllowed)
    {
        $this->fsk18PurchaseAllowed = (bool)$p_FSK18PurchaseAllowed;
    }


    /**
     * @return boolean
     */
    public function getFSK18DisplayAllowed()
    {
        return $this->fsk18DisplayAllowed;
    }


    /**
     * @param boolean $p_FSK18DisplayAllowed
     */
    public function setFSK18DisplayAllowed($p_FSK18DisplayAllowed)
    {
        $this->fsk18DisplayAllowed = (bool)$p_FSK18DisplayAllowed;
    }


    /**
     * @return string
     */
    public function getLastListingSql()
    {
        return $this->lastListingSql;
    }


    /**
     * @param string $p_lastListingSql
     */
    public function setLastListingSql($p_lastListingSql)
    {
        $this->lastListingSql = (string)$p_lastListingSql;
    }


    /**
     * @return int
     */
    public function getCustomerStatusId()
    {
        return $this->customerStatusId;
    }


    /**
     * @param int $p_customerStatusId
     */
    public function setCustomerStatusId($p_customerStatusId)
    {
        $this->customerStatusId = (int)$p_customerStatusId;
    }


    /**
     * @return array
     */
    public function getAdditionalFields()
    {
        return $this->additionalFields;
    }


    /**
     * @param array $p_additionalFields
     */
    public function setAdditionalFields($p_additionalFields)
    {
        $this->additionalFields = $p_additionalFields;
    }
    
    
    /**
     * @return bool
     */
    public function isStockAllowCheckout()
    {
        return (bool)$this->stockAllowCheckout;
    }
    
    
    /**
     * @param bool $stockAllowCheckout
     *
     * @return ProductInfoContentView
     */
    public function setStockAllowCheckout($stockAllowCheckout)
    {
        $this->stockAllowCheckout = (bool)$stockAllowCheckout;
        
        return $this;
    }
    
    
    /**
     * @return bool
     */
    public function isStockCheck()
    {
        return $this->stockCheck;
    }
    
    
    /**
     * @param bool $stockCheck
     *
     * @return ProductInfoContentView
     */
    public function setStockCheck($stockCheck)
    {
        $this->stockCheck = (bool)$stockCheck;
    
        return $this;
    }
    
    
    /**
     * @return bool
     */
    public function isAttributeStockCheck()
    {
        return $this->attributeStockCheck;
    }
    
    
    /**
     * @param bool $attributeStockCheck
     *
     * @return ProductInfoContentView
     */
    public function setAttributeStockCheck($attributeStockCheck)
    {
        $this->attributeStockCheck = (bool)$attributeStockCheck;
    
        return $this;
    }
    
    
    /**
     * @return bool
     */
    public function isAppendPropertiesModel()
    {
        return $this->appendPropertiesModel;
    }
    
    
    /**
     * @param bool $appendPropertiesModel
     *
     * @return ProductInfoContentView
     */
    public function setAppendPropertiesModel($appendPropertiesModel)
    {
        $this->appendPropertiesModel = (bool)$appendPropertiesModel;
    
        return $this;
    }
    
    
    /**
     * @param bool $showPriceTax
     *
     * @return ProductInfoContentView
     */
    public function setShowPriceTax($showPriceTax)
    {
        $this->showPriceTax = (bool)$showPriceTax;
        
        return $this;
    }


    ##### DEPRECATED since GX2.2 #####

    protected function _assignDeprecatedPurchaseData()
    {
        $this->set_content_data('ADD_QTY', xtc_draw_input_field('products_qty', str_replace('.', ',',
                (double)$this->product->data['gm_min_order']),
                'id="gm_attr_calc_qty"') . ' '
            . xtc_draw_hidden_field('products_id', $this->product->data['products_id'],
                'id="gm_products_id"'), 2);

        $this->set_content_data('ADD_CART_BUTTON',
            xtc_image_submit('button_in_cart.gif', IMAGE_BUTTON_IN_CART, 'id="cart_button"'), 2);
    }


    protected function _assignDeprecatedWishlist()
    {
        if (gm_get_conf('GM_SHOW_WISHLIST') == 'true') {
            $this->set_content_data('ADD_WISHLIST_BUTTON',
                '<a href="javascript:submit_to_wishlist()" id="gm_wishlist_link">'
                . xtc_image_button('button_in_wishlist.gif', NC_WISHLIST) . '</a>', 2);
        }
    }


    protected function _assignDeprecatedPrintLink()
    {
        $this->set_content_data('PRODUCTS_PRINT',
            '<img src="templates/' . CURRENT_TEMPLATE . '/buttons/' . $this->language
            . '/print.gif"  style="cursor:hand;" onclick="javascript:window.open(\''
            . xtc_href_link(FILENAME_PRINT_PRODUCT_INFO,
                'products_id=' . $this->product->data['products_id'])
            . '\', \'popup\', \'toolbar=0, width=640, height=600\')" alt="" />');
    }


    protected function _assignDeprecatedFormTagData()
    {
        $this->set_content_data('FORM_ACTION', xtc_draw_form('cart_quantity', xtc_href_link(FILENAME_PRODUCT_INFO,
            xtc_get_all_get_params(array('action'))
            . 'action=add_product',
            'NONSSL', true, true, true),
            'post',
            'name="cart_quantity" onsubmit="gm_qty_check = new GMOrderQuantityChecker(); return gm_qty_check.check();"'),
            2);
        $this->set_content_data('FORM_END', '</form>', 2);
    }


    protected function _assignDeprecatedIdHiddenField()
    {
        $this->set_content_data('GM_PID', xtc_draw_hidden_field('products_id', $this->product->data['products_id'],
            'id="gm_products_id"'), 2);
    }


    protected function _assignDeprecatedDimensionValues()
    {
        if (PRODUCT_IMAGE_INFO_WIDTH < (190 - 16)) {
            $this->set_content_data('MIN_IMAGE_WIDTH', 188, 2);
            $this->set_content_data('MIN_INFO_BOX_WIDTH', 156 - 10, 2);
            $this->set_content_data('MARGIN_LEFT', 188 + 10, 2);
        } else {
            $this->set_content_data('MIN_IMAGE_WIDTH', PRODUCT_IMAGE_INFO_WIDTH + 16, 2);
            $this->set_content_data('MIN_INFO_BOX_WIDTH', PRODUCT_IMAGE_INFO_WIDTH + 16 - 32 - 10, 2);
            $this->set_content_data('MARGIN_LEFT', PRODUCT_IMAGE_INFO_WIDTH + 16 + 10, 2);
        }
    }


    /**
     * @return string
     */
    protected function _getMediaContentHtml()
    {
        /* @var ProductMediaContentView $view */
        $view = MainFactory::create_object('ProductMediaContentView');
        $view->setProductId($this->product->data['products_id']);
        $view->setLanguageId($this->languageId);
        $view->setCustomerStatusId($this->customerStatusId);
        $html = $view->get_html();

        return $html;
    }


    /**
     * @return int
     */
    public function getOfferAggregationLimit()
    {
        return $this->offerAggregationLimit;
    }


    /**
     * @param int $offerAggregationLimit
     */
    public function setOfferAggregationLimit($offerAggregationLimit)
    {
        $this->offerAggregationLimit = $offerAggregationLimit;
    }

    /**
     * Set Paypal EC-Button
     */
    protected function _setPaypalEcButton()
    {
        if (defined('MODULE_PAYMENT_PAYPAL3_ALLOWED') && !empty(MODULE_PAYMENT_PAYPAL3_ALLOWED)) {
            return;
        }
        if (defined('MODULE_PAYMENT_PAYPAL3_ZONE') && (int)MODULE_PAYMENT_PAYPAL3_ZONE > 0) {
            return;
        }
        $paypalEcButtonConfiguration = $this->paypalEcButtonConfiguration();
        if (!empty($paypalEcButtonConfiguration)) {
            $this->set_content_data('PAYPAL_EC_BUTTON', $paypalEcButtonConfiguration);
        }
    }

    /**
     * Create Configuration Array for the Paypal EC-Button
     *
     * @return array
     */
    protected function paypalEcButtonConfiguration()
    {
        $configuration = [];
        $displayButton = (strpos((string)@constant('MODULE_PAYMENT_INSTALLED'), 'paypal3.php') !== false);
        $displayButton = $displayButton && (strtolower((string)@constant('MODULE_PAYMENT_PAYPAL3_STATUS')) === 'true');
        
        if (!$displayButton) {
            return $configuration;
        }
        
        $paypalConfiguration = MainFactory::create('PayPalConfigurationStorage');
        if ((bool)$paypalConfiguration->get('use_ecs_products') === true) {
            /** @var \LanguageHelper $languageHelper */
            $languageHelper = MainFactory::create('LanguageHelper', StaticGXCoreLoader::getDatabaseQueryBuilder());
            $languageCode = $languageHelper->getLanguageCodeById(new IdType($this->languageId))->asString();
            
            $supportedLanguages = ['DE', 'EN', 'ES', 'FR', 'IT', 'NL'];
            
            if (!in_array($languageCode, $supportedLanguages, true)) {
                $languageCode = 'EN';
            }
            
            $buttonStyle = $paypalConfiguration->get('ecs_button_style');
            $buttonImageUrl = GM_HTTP_SERVER . DIR_WS_CATALOG . 'images/icons/paypal/' . $buttonStyle . 'Btn_'
                              . $languageCode . '.png';
            $configuration = [
                'src'          => $buttonImageUrl,
                'widget'       => 'paypal_ec_button',
                'page'         => 'product',
                'redirect'     => isset($_SESSION['paypal_cart_ecs']) ? 'true' : 'false',
                'display_cart' => DISPLAY_CART,
            ];
        }
        
        return $configuration;
    }
    
    
    /**
     * @return GMSEOBoost_ORIGIN
     */
    public function getSeoBoost(): GMSEOBoost_ORIGIN
    {
        return $this->seoBoost;
    }
    
    
    /**
     * @param GMSEOBoost_ORIGIN $seoBoost
     */
    public function setSeoBoost(GMSEOBoost_ORIGIN $seoBoost): void
    {
        $this->seoBoost = $seoBoost;
    }
    
    
    /**
     * @param SellingUnitId $createFromInfoString
     */
    public function set_selling_unit_id(SellingUnitId $createFromInfoString)
    {
    }
}
