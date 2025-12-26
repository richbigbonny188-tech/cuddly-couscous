<?php
/* --------------------------------------------------------------
   ProductInfoThemeContentView.inc.php 2021-03-02
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

// include needed functions
use Gambio\ProductImageList\Collections\ImageListsCollection;
use Gambio\ProductImageList\Image\ValueObjects\WebFilePath;
use Gambio\ProductImageList\ImageList\Collections\ImageList;
use Gambio\ProductImageList\ImageList\ValueObjects\ListName;
use Gambio\ProductImageList\Interfaces\ProductImageListReadServiceInterface;
use Gambio\ProductImageList\ReadService\Dtos\AttributeIdDto;
use Gambio\ProductImageList\ReadService\Exceptions\AttributeDoesNotHaveAListException;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Price\Product\Database\ValueObjects\CustomersStatusShowPrice;
use Gambio\Shop\Product\SellingUnitImage\Database\Service\ReadServiceInterface as ImageReadServiceInterface;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Database\GroupRepositoryFactory;
use Gambio\Shop\ProductModifiers\Database\Presentation\Mappers\Exceptions\PresentationMapperNotFoundException;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollection;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifiersCollectionInterface;
use Gambio\Shop\SellingUnit\Unit\Exceptions\InsufficientQuantityException;
use Gambio\Shop\SellingUnit\Unit\Exceptions\InvalidQuantityException;
use Gambio\Shop\SellingUnit\Unit\Exceptions\InvalidQuantityGranularityException;
use Gambio\Shop\SellingUnit\Unit\Exceptions\QuantitySurpassMaximumAllowedQuantityException;
use Gambio\Shop\SellingUnit\Unit\Exceptions\RequestedQuantityBelowMinimumException;
use Gambio\Shop\SellingUnit\Unit\Factories\SellingUnitIdFactory;
use Gambio\Shop\SellingUnit\Unit\SellingUnitInterface;
use Gambio\Shop\SellingUnit\Unit\Services\Interfaces\SellingUnitReadServiceInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use Gambio\Shop\UserNavigationHistory\UserNavigationHistoryService;

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
 * Class ProductInfoThemeContentView
 */
class ProductInfoThemeContentView extends ThemeContentView
{
    protected $getArray  = []; // $_GET
    protected $postArray = []; // $_POST
    
    protected $cheapestCombiArray   = [];
    protected $combiId              = 0;
    protected $currency             = '';
    protected $currentCategoryId    = 0;
    protected $currentCombiArray    = [];
    protected $customerDiscount     = 0.0;
    protected $fsk18DisplayAllowed  = true;
    protected $fsk18PurchaseAllowed = true;
    protected $customerStatusId     = -1;
    protected $hasProperties        = false;
    protected $languageId           = 0;
    protected $language             = '';
    protected $lastListingSql       = '';
    protected $main;
    /**
     * @var product_ORIGIN
     */
    protected $product;
    protected $productPriceArray   = [];
    protected $showGraduatedPrices = false;
    protected $showPrice           = true;
    /**
     * @var xtcPrice_ORIGIN
     */
    protected $xtcPrice;
    /** @var GMSEOBoost_ORIGIN */
    protected $seoBoost;
    protected $maxImageHeight        = 0;
    protected $additionalFields      = [];
    protected $offerAggregationLimit = 100;
    protected $stockAllowCheckout    = true;
    protected $stockCheck            = true;
    protected $attributeStockCheck   = true;
    protected $appendPropertiesModel = true;
    protected $showPriceTax          = true;
    
    /**
     * @var VPEReadServiceInterface
     */
    protected $VPEReadService;
    /**
     * @var ManufacturerReadServiceInterface
     */
    protected $manufacturerReadService;
    
    /**
     * @var ModifiersCollectionInterface
     */
    protected $modifierGroups;
    /**
     * @var ModifierIdentifierCollectionInterface
     */
    protected $modifier_ids;
    /**
     * @var SellingUnitInterface
     */
    protected $selling_unit;
    /**
     * @var SellingUnitId
     */
    protected $selling_unit_id;
    
    /**
     * @var ProductImageListReadServiceInterface
     */
    protected $imageListReadService;
    
    /**
     * @var ImageReadServiceInterface
     */
    protected $imageReadService;
    
    /**
     * @var ImageListsCollection
     */
    protected $imageLists;
    
    /**
     * @var ImageListsCollection
     */
    protected $attributeImageLists;
    
    /**
     * @var string
     */
    protected $collectionType;
    
    
    /**
     * ProductInfoThemeContentView constructor.
     *
     * @param string $p_template
     */
    public function __construct($p_template = 'default')
    {
        parent::__construct();
        $filepath = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getProductInfoTemplatePath();
        
        // get default template
        $c_template = $this->get_default_template($filepath, 'product_info_template_', $p_template);
        
        $this->set_content_template($c_template);
        $this->set_flat_assigns(true);
        
        $this->imageListReadService = StaticGXCoreLoader::getService('ProductImageListRead');
        $this->imageLists           = new ImageListsCollection;
        $this->attributeImageLists  = new ImageListsCollection;
        $this->imageReadService     = LegacyDependencyContainer::getInstance()->get(ImageReadServiceInterface::class);
    }
    
    
    function prepare_data()
    {
        if ($this->product instanceof product && $this->product->isProduct()) {
            $this->_init_combi_data();
            $this->init_selling_unit();
            $this->init_groups();
            
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
        }
    }
    
    
    public function get_html()
    {
        if (($this->product instanceof product) === false || !$this->product->isProduct()) {
            // product not found in database
            $error = TEXT_PRODUCT_NOT_FOUND;
            
            /* @var ErrorMessageThemeContentView $coo_error_message */
            $errorView = MainFactory::create_object('ErrorMessageThemeContentView');
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
        $this->_assignModifiers();
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
        $this->_assignErrorMessages();
        
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
            $output              = '';
            $paypalConfiguration = MainFactory::create('PayPalConfigurationStorage');
            $showSpecific        = (bool)$paypalConfiguration->get('show_installments_presentment_specific_product');
            $hasAttributes       = trim($this->content_array['MODULE_product_options']) !== '';
            $showComputed        = $hasAttributes === false && $this->hasProperties === false
                                   && (bool)$paypalConfiguration->get('show_installments_presentment_specific_computed');
            $productPrice        = $this->productPriceArray['plain'];
            $paypalInstallments  = MainFactory::create('PayPalInstallments');
            if ($showSpecific && $productPrice >= $paypalInstallments->getMinimumAmount()['amount']
                && $productPrice <= $paypalInstallments->getMaximumAmount()['amount']) {
                if ($showComputed == true) {
                    try {
                        $response = $paypalInstallments->getInstallmentInfo($productPrice, $_SESSION['currency'], 'DE');
                        if (!empty($response->financing_options[0]->qualifying_financing_options)) {
                            $representativeOption                                  = $paypalInstallments->getRepresentativeOption($response->financing_options[0]->qualifying_financing_options);
                            $specificThemeContentView                              = MainFactory::create('PayPalInstallmentSpecificUpstreamPresentmentThemeContentView');
                            $specificThemeContentView->qualifyingOptions           = $response->financing_options[0]->qualifying_financing_options;
                            $specificThemeContentView->nonQualifyingOptions        = $response->financing_options[0]->non_qualifying_financing_options;
                            $specificThemeContentView->lender                      = implode(', ',
                                                                                             [
                                                                                                 COMPANY_NAME,
                                                                                                 TRADER_STREET . ' '
                                                                                                 . TRADER_STREET_NUMBER,
                                                                                                 TRADER_ZIPCODE . ' '
                                                                                                 . TRADER_LOCATION
                                                                                             ]);
                            $specificThemeContentView->currency                    = $_SESSION['currency'];
                            $specificThemeContentView->cashPurchasePrice           = $productPrice;
                            $specificThemeContentView->numberOfInstallments        = $representativeOption->credit_financing->term;
                            $specificThemeContentView->borrowingRate               = $representativeOption->credit_financing->nominal_rate;
                            $specificThemeContentView->annualPercentageRate        = $representativeOption->credit_financing->apr;
                            $specificThemeContentView->installmentAmount           = $representativeOption->monthly_payment->value;
                            $specificThemeContentView->totalAmount                 = $representativeOption->total_cost->value;
                            $specificThemeContentView->representativeFinancingCode = $representativeOption->credit_financing->financing_code;
                            $output                                                = $specificThemeContentView->get_html();
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
                    $ThemeContentView = MainFactory::create('ThemeContentView');
                    $ThemeContentView->set_content_template('paypal_installment_specific_out_of_bounds.html');
                    $ThemeContentView->set_flat_assigns(true);
                    $ThemeContentView->set_caching_enabled(false);
                    $ThemeContentView->set_content_data('amount', $amount);
                    $output = $ThemeContentView->get_html();
                }
            } else {
                $ThemeContentView = MainFactory::create('ThemeContentView');
                $ThemeContentView->set_content_template('paypal_installment_specific_out_of_bounds.html');
                $ThemeContentView->set_flat_assigns(true);
                $ThemeContentView->set_caching_enabled(false);
                $output = $ThemeContentView->get_html();
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
            /* @var ProductNavigatorThemeContentView $view */
            $view = MainFactory::create_object('ProductNavigatorThemeContentView');
            $view->setProduct($this->product);
            
            $categoryId = $this->getProductNavigatorCategoryId();
            
            if ($categoryId !== null) {
                
                $navigatorListingQuery = new ProductNavigatorListingQuery($categoryId, (int)$_SESSION['languages_id']);
                
                if ($this->productIsInCategory($navigatorListingQuery, (int)$this->product->pID)) {
                    
                    $view->setCategoryId($categoryId);
                    $view->setDisableCPathUsageInProductQuery(true);
                    $view->setLastListingSql((string)$navigatorListingQuery);
                } else {
                    $categoryId = null;
                }
            }
            
            if ($categoryId === null) {
                $view->setCategoryId($this->currentCategoryId);
                $view->setLastListingSql($this->lastListingSql);
            }
            
            $view->setFSK18DisplayAllowed((int)$this->fsk18DisplayAllowed);
            $view->setCustomerStatusId($this->customerStatusId);
            $view->setLanguageId($this->languageId);
            $html = $view->get_html();
            $this->set_content_data('PRODUCT_NAVIGATOR', $html);
        }
    }
    
    
    /**
     * @param ProductNavigatorListingQuery $query
     * @param int                          $productId
     *
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function productIsInCategory(ProductNavigatorListingQuery $query, int $productId): bool
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = LegacyDependencyContainer::getInstance()->get(\Doctrine\DBAL\Connection::class);
        $result     = $connection->query((string)$query);
        $result     = $result->fetchAll();
        $result     = array_map(static function (array $productArray) use ($productId) {
            return (int)$productArray['products_id'] === $productId;
        },
            $result);
        
        return in_array(true, $result, true);
    }
    
    /**
     * @return int|null
     */
    protected function getProductNavigatorCategoryId(): ?int
    {
        /** @var UserNavigationHistoryService $service */
        $service = LegacyDependencyContainer::getInstance()->get(UserNavigationHistoryService::class);
        
        return $service->getLastBrowsedCategoryId((int)$_SESSION['languages_id']);
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
    
    protected function  _init_combi_data(){
        $query  = 'SELECT products_properties_combis_id
					FROM products_properties_combis
					WHERE products_id = ' . (int)$this->product->data['products_id'].
                  " order by products_properties_combis_id";
        $result = xtc_db_query($query);
        
        if (xtc_db_num_rows($result) >= 1) {
            if (xtc_db_num_rows($result) == 1) {
                $row           = xtc_db_fetch_array($result);
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
                $this->cheapestCombiArray            = $coo_properties_control->get_cheapest_combi($this->product->data['products_id'],
                                                                                                   $this->languageId);
                $this->xtcPrice->showFrom_Attributes = true;
            }
        }
    }
    
    protected function _assignAlsoPurchased()
    {
        /* @var AlsoPurchasedThemeContentView $view */
        $view = MainFactory::create_object('AlsoPurchasedThemeContentView');
        $view->set_coo_product($this->product);
        $html = $view->get_html();
        $this->set_content_data('MODULE_also_purchased', $html);
    }
    
    
    protected function _assignYoochoose()
    {
        if (defined('YOOCHOOSE_ACTIVE') && YOOCHOOSE_ACTIVE) {
            include_once(DIR_WS_INCLUDES . 'yoochoose/recommendations.php');
            include_once(DIR_WS_INCLUDES . 'yoochoose/functions.php');
            
            /* @var YoochooseAlsoInterestingThemeContentView $view */
            $view = MainFactory::create_object('YoochooseAlsoInterestingThemeContentView');
            $view->setProduct($this->product);
            $html = $view->get_html();
            $this->set_content_data('MODULE_yoochoose_also_interesting', $html);
        }
    }
    
    
    protected function _assignModifiers()
    {
        
        // CREATE ProductAttributesThemeContentView OBJECT
        $showPriceStatus = LegacyDependencyContainer::getInstance()->get(CustomersStatusShowPrice::class);
        $view            = $this->createModifierGroupsThemeContentView($this->selling_unit, $showPriceStatus);
        
        // SET TEMPLATE
        $filepath   = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getProductOptionsTemplatePath();
        $c_template = $view->get_default_template($filepath, 'product_modifiers_template_', 'group');
        $view->set_content_template($c_template);
        $view->set_modifiers_groups($this->modifierGroups);
        $view->set_selected_modifier_ids($this->selling_unit->id()->modifiers());
        
        
        // GET HTML
        $html = $view->get_html();
        $this->set_content_data('MODULE_modifier_groups', $html);
    }
    
    
    protected function _assignCrossSelling()
    {
        /* @var CrossSellingThemeContentView $view */
        $view = MainFactory::create_object('CrossSellingThemeContentView');
        $view->set_type('cross_selling');
        $view->set_coo_product($this->product);
        $html = $view->get_html();
        $this->set_content_data('MODULE_cross_selling', $html);
    }
    
    
    protected function _assignReverseCrossSelling()
    {
        if (ACTIVATE_REVERSE_CROSS_SELLING == 'true') {
            /* @var CrossSellingThemeContentView $view */
            $view = MainFactory::create_object('CrossSellingThemeContentView');
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
        
        $productImage = $this->getProductImage($this->product);
        $linkedData   = [
            '@context'      => 'http://schema.org',
            '@type'         => 'Product',
            'name'          => $this->product->data['products_name'],
            'description'   => $description,
            'image'         => $productImage,
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
            $priceValidUntil  = '2100-01-01 00:00:00';
            $specialPriceData = $db->get_where('specials',
                                               [
                                                   'products_id' => (int)$this->product->data['products_id'],
                                                   'status'      => 1
                                               ])->row_array();
            $priceValidUntil  = '2100-01-01 00:00:00';
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
            $brand               = [
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
                            '@type'  => 'ProductModel',
                            'name'   => $this->product->data['products_name'],
                            //'description' => $description,
                            'model'       => $baseModel . ($this->appendPropertiesModel ? '-'
                                                                                          . $combi['combi_model'] : ''),
                            'sku'         => $baseModel . ($this->appendPropertiesModel ? '-'
                                                                                          . $combi['combi_model'] : ''),
                            'offers' => [
                                '@type'              => 'Offer',
                                'price'              => number_format($combiPrice, 2, '.', ''),
                                'priceCurrency'      => $this->getCurrency(),
                                'priceSpecification' => [
                                    '@type'                 => 'http://schema.org/PriceSpecification',
                                    'price'                 => number_format($combiPrice, 2, '.', ''),
                                    'priceCurrency'         => $this->getCurrency(),
                                    'valueAddedTaxIncluded' => $taxIncluded,
                                ],
                                'url'                => $variantUrl,
                            ],
                            'url'    => $variantUrl,
                        ];
                        
                        $combinationImages = $this->getCombinationImages($combi);
                        
                        if (!empty($combinationImages)) {
                            $model['image'] = $combinationImages;
                            
                            if (!empty($productImage) && $this->getCollectionType() === 'DISPLAY_PROPERTY_PLUS_ARTICLE_IMAGES') {
                                
                                $model['image'] = array_merge($combinationImages, $productImage);
                            }
                            
                        } elseif (!empty($productImage)) {
                            $model['image'] = $productImage;
                        }
                        
                        if (!empty($combi['products_attributes_ids'])) {
                            $images = $this->getImagesForAttributeIds($combi['products_attributes_ids']);
                            
                            if (!empty($images)) {
                                
                                if (isset($model['image']) && !is_array($model['image'])) {
                                    $model['image'] = [$model['image']];
                                }
                                
                                $model['image'] = isset($model['image']) ? array_merge($model['image'], $images) : $images;
                            }
                        }
                        
                        if (is_array($model['image'])) {
                            $model['image'] = array_values(array_unique($model['image']));
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
     *
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
     * @param $id
     *
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
     * @param $productsId
     *
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
        
        $attribsCounts   = $db->select('`options_id`, COUNT(`options_id`) AS num_values')
            ->where('products_id',
                    (int)$productsId)
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
     *
     * @return array
     */
    protected function getProductPropertiesCombis($productsId)
    {
        /* properties */
        $db     = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $combis = $db->select('products_properties_combis.*, product_image_list_combi.product_image_list_id')
            ->from('products_properties_combis')
            ->where('products_id', (int)$productsId)
            ->join('product_image_list_combi', 'products_properties_combis.products_properties_combis_id=product_image_list_combi.products_properties_combis_id', 'LEFT')
            ->order_by('sort_order', 'ASC')
            ->get()
            ->result_array();
        
        if (!empty($combis)) {
            
            return $combis;
        }
        
        /* attributes */
        $attributes = $db->where('products_id', (int)$productsId)
            ->order_by('options_values_id', 'ASC')
            ->order_by('sortorder',
                       'ASC')
            ->get('products_attributes')
            ->result_array();
        if (!empty($attributes)) {
            $attibuteMap     =
            $attributesLists = [];
            $attributeModels = [];
            $attributePrices = [];
            $attributeStocks = [];
            $attributeEans   = [];
            $optionsWithEans = [];
            $combiVpeId      = 0;
            $combiVpeValue   = 0.0;
            foreach ($attributes as $attribute) {
                $optionsId                                         = $attribute['options_id'];
                $valuesId                                          = $attribute['options_values_id'];
                $attibuteMap[$attribute['products_attributes_id']] = [$optionsId, $valuesId];
                if (!array_key_exists($optionsId, $attributesLists)) {
                    $attributesLists[$optionsId] = [];
                }
                $attributesLists[$optionsId][]                 = $valuesId;
                $attributeModels[$optionsId . '-' . $valuesId] = $attribute['attributes_model'];
                $priceFactor                                   = $attribute['price_prefix'] === '-' ? -1 : 1;
                $attributePrices[$optionsId . '-' . $valuesId] = $priceFactor * $attribute['options_values_price'];
                $attributeStocks[$optionsId . '-' . $valuesId] = $attribute['attributes_stock'];
                $gmEan                                         = trim($attribute['gm_ean']);
                if (!empty($gmEan)) {
                    $optionsWithEans[$optionsId]                 = true;
                    $attributeEans[$optionsId . '-' . $valuesId] = $gmEan;
                }
                if ($combiVpeId === 0 && (int)$attribute['products_vpe_id'] !== 0) {
                    $combiVpeId    = (int)$attribute['products_vpe_id'];
                    $combiVpeValue = (float)$attribute['gm_vpe_value'];
                }
            }
            if (count($optionsWithEans) > 1) {
                $attributeEans = [];
            }
            $options = array_keys($attributesLists);
            $counts  = [];
            foreach ($options as $optionId) {
                $counts[] = count($attributesLists[$optionId]);
            }
            
            $attribSet       = array_fill(0, count($attributesLists), 0);
            $attributeCombis = [];
            do {
                $optionsToValues = [];
                foreach ($options as $oIndex => $optionId) {
                    $valuesId                   = $attributesLists[$options[$oIndex]][$attribSet[$oIndex]];
                    $optionsToValues[$optionId] = $valuesId;
                }
                $uPID          = (string)$productsId;
                $combiModel    = '';
                $combiPrice    = 0.0;
                $combiQuantity = 999999;
                $combiEan      = '';
                foreach ($optionsToValues as $optionsId => $valuesId) {
                    $uPID          .= '{' . $optionsId . '}' . $valuesId;
                    $combiModel    .= empty($combiModel) ? '' : '-';
                    $combiModel    .= $attributeModels[$optionsId . '-' . $valuesId];
                    $combiPrice    += $attributePrices[$optionsId . '-' . $valuesId];
                    $combiQuantity = min($combiQuantity, (float)$attributeStocks[$optionsId . '-' . $valuesId]);
                    $combiEan      = !empty($attributeEans[$optionsId . '-' . $valuesId]) ? $attributeEans[$optionsId
                                                                                                           . '-'
                                                                                                           . $valuesId] : $combiEan;
                }
                $attributeCombis[] = [
                    'products_id'             => $productsId,
                    'unique_products_id'      => $uPID,
                    'combi_model'             => $combiModel,
                    'combi_ean'               => $combiEan,
                    'combi_quantity'          => $combiQuantity,
                    'combi_price_type'        => 'calc',
                    'combi_price'             => $combiPrice,
                    'products_vpe_id'         => $combiVpeId,
                    'vpe_value'               => $combiVpeValue,
                    'products_attributes_ids' => $this->getProductAttributesIds($uPID, $attibuteMap)
                ];
            } while ($this->incrementArray($attribSet, $counts) !== false);
            
            return $attributeCombis;
        }
        
        return [];
    }
    
    
    /**
     * @param string $uPID
     * @param array  $attributeMap
     *
     * @return array
     */
    protected function getProductAttributesIds(string $uPID, array $attributeMap): array
    {
        $uPID    = preg_replace('#^\d+#', '', $uPID); // removing the product id
        $pattern = '#\{(\d)\}(\d)#';    //  e.g {5}9
        $result  = [];
        preg_match_all($pattern, $uPID, $matches, PREG_OFFSET_CAPTURE);
        
        for ($i = 0, $size = count(current($matches)); $i < $size; $i++) {  // each regex group has it's own array in matches with all results for that group
            
            $selectedOptionsId = $matches[1][$i][0]; // e.g 5
            $selectedValuesId  = $matches[2][$i][0]; // e.g 9
            
            foreach ($attributeMap as $productsAttributesId => [$optionsId, $valuesId]) {
                
                if ($selectedOptionsId === $optionsId && $selectedValuesId === $valuesId) {
                    
                    $result[] = $productsAttributesId;
                }
            }
        }
        
        return $result;
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
        
        /* @var ProductReviewsThemeContentView $view */
        $view = MainFactory::create_object('ProductReviewsThemeContentView');
        $view->setProduct($this->product);
        $html = $view->get_html();
        if (trim($html) !== '') {
            $this->set_content_data('MODULE_products_reviews', $html);
        }
    }
    
    
    protected function _assignProductUrl()
    {
        if (xtc_not_null($this->product->data['products_url'])) {
            $this->set_content_data('PRODUCTS_URL',
                                    sprintf(TEXT_MORE_INFORMATION,
                                            xtc_href_link(FILENAME_REDIRECT,
                                                          'action=product&id=' . $this->selling_unit->id()
                                                              ->productId()
                                                              ->value(),
                                                          'NONSSL',
                                                          true)));
        }
    }
    
    
    protected function _assignDate()
    {
        if(($availabilityDate = $this->selling_unit->productInfo()->availabilityDate()) &&
            $availabilityDate->value() > date('Y-m-d H:i:s'))
        {
            $this->set_content_data(
                'PRODUCTS_DATE_AVIABLE',
                sprintf(TEXT_DATE_AVAILABLE,xtc_date_long($availabilityDate->value()))
            );
        } elseif (($releaseDate = $this->selling_unit->productInfo()->releaseDate()) && $releaseDate->show()) {
            $this->set_content_data(
                'PRODUCTS_ADDED',
                sprintf(TEXT_DATE_ADDED,xtc_date_long($releaseDate->value()))
            );
        }
    }
    
    
    protected function _assignGraduatedPrices()
    {
        /* @var GraduatedPricesThemeContentView $view */
        $view = MainFactory::create_object('GraduatedPricesThemeContentView');
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
            $_SESSION['tracking']['products_history']    = array_unique($_SESSION['tracking']['products_history']);
        } else {
            $_SESSION['tracking']['products_history'][$i] = $this->product->data['products_id'];
            $_SESSION['tracking']['products_history']     = array_unique($_SESSION['tracking']['products_history']);
        }
    }
    
    
    protected function _assignPrintLink()
    {
        if (gm_get_conf('SHOW_PRINT') == 'true') {
            $this->set_content_data('SHOW_PRINT', 1);
        }
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
        /* @var ProductImagesThemeContentView $view */
        $view = MainFactory::create_object('ProductImagesContentView');
        
        $filepath   = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getProductOptionsTemplatePath();
        $c_template = $view->get_default_template($filepath, 'product_info_', 'gallery.html');
        $view->set_content_template($c_template);
        $view->setImages($this->selling_unit->images());
        $view->setProductId($this->selling_unit->id()->productId()->value());
        $view->setProductName($this->selling_unit->productInfo()->name()->value());
        
        $html = $view->get_html();
        $this->set_content_data('IMAGE_GALLERY', $html);
        
        $c_template = $view->get_default_template($filepath, 'product_info_', 'gallery_modal.html');
        $view->set_content_template($c_template);
        $html = $view->get_html();
        $this->set_content_data('IMAGE_GALLERY_MODAL', $html);
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
        $this->set_content_data(
            'PRODUCTS_PRICE',
            $this->selling_unit->price()->formattedPrice()->value()
        );
        
        if ($this->selling_unit->price()->status()->equalsInt(1)) {
            $seoBoost     = MainFactory::create_object('GMSEOBoost', [], true);
            $sefParameter = '';
            
            $query  = "SELECT
							content_id,
							content_title
						FROM " . TABLE_CONTENT_MANAGER . "
						WHERE
							languages_id = '" . (int)$this->languageId . "' AND
							content_group = '7'";
            $result = xtc_db_query($query);
            if (xtc_db_num_rows($result)) {
                $row                 = xtc_db_fetch_array($result);
                $contactContentId    = $row['content_id'];
                $contactContentTitle = $row['content_title'];
                
                if (defined('SEARCH_ENGINE_FRIENDLY_URLS') && SEARCH_ENGINE_FRIENDLY_URLS === 'false') {
                    $sefParameter = '&content=' . xtc_cleanName($contactContentTitle);
                }
            }
            if ($seoBoost->boost_content) {
                $contactUrl = xtc_href_link($seoBoost->get_boosted_content_url($contactContentId, $this->languageId)
                                            . '?subject=' . rawurlencode(GM_SHOW_PRICE_ON_REQUEST . ': '
                                                                         . $this->selling_unit->productInfo()->name()->value()));
            } else {
                $contactUrl = xtc_href_link(FILENAME_CONTENT,
                                            'coID=7&subject=' . rawurlencode(GM_SHOW_PRICE_ON_REQUEST . ': '
                                                                             .$this->selling_unit->productInfo()->name()->value())
                                            . $sefParameter);
            }
            
            $this->set_content_data('PRODUCTS_PRICE_CONTACT_URL', $contactUrl);
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
        
        $description = $this->selling_unit->productInfo()->description()->value();
        
        /* @var GMTabTokenizer $tabTokenizer */
        $tabTokenizer = MainFactory::create_object('GMTabTokenizer', [stripslashes($description)]);
        $description  = $tabTokenizer->get_prepared_output();
        
        $this->set_content_data('PRODUCTS_DESCRIPTION', $description);
        $this->set_content_data('description', $tabTokenizer->head_content);
        
        $tabs = [];
        foreach ($tabTokenizer->tab_content as $key => $value) {
            $tabs[] = ['title' => strip_tags($value), 'content' => $tabTokenizer->panel_content[$key]];
        }
        
        $mediaContent = $this->_getMediaContentHtml();
        if (trim($mediaContent) !== '') {
            $languageTextManager = MainFactory::create_object('LanguageTextManager',
                                                              ['products_media', $this->languageId]);
            $tabs[]              = [
                'title'   => $languageTextManager->get_text('text_media_content_tab'),
                'content' => $mediaContent
            ];
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
    
    
    protected function _assignInputFieldQuantity()
    {
        $this->set_content_data('QUANTITY', gm_convert_qty($this->selling_unit->selectedQuantity()->value(), true));
        
        $this->set_content_data('DISABLED_QUANTITY', 0);
        
        if ((double)$this->product->data['gm_min_order'] != 1) {
            $this->set_content_data('GM_MIN_ORDER', gm_convert_qty($this->product->data['gm_min_order'], false));
        }
        
        if ($this->selling_unit->quantityGraduation() !== 1) {
            $this->set_content_data('GM_GRADUATED_QTY',
                                    gm_convert_qty($this->selling_unit->quantityGraduation()->value(), false));
        }
        $this->set_content_data('QTY_STEPPING', $this->selling_unit->quantityGraduation()->value());
    }
    
    
    protected function _assignWishlist()
    {
        if (gm_get_conf('GM_SHOW_WISHLIST') == 'true') {
            $this->set_content_data('SHOW_WISHLIST', 1);
        } else {
            $this->set_content_data('SHOW_WISHLIST', 0);
        }
    }
    
    
    protected function _assignLegalAgeFlag()
    {
        if ($this->selling_unit->productInfo()->legalAgeFlag()->value()) {
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
        $this->set_content_data('PRODUCTS_SHIPPING_LINK', $this->selling_unit->shipping()->link());
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
        $this->set_content_data('PRODUCTS_ORDERED', $this->selling_unit->productInfo()->numberOfOrders()->value());
    }
    
    
    protected function _assignFormTagData()
    {
        $this->set_content_data('FORM_ACTION_URL',
                                xtc_href_link(FILENAME_PRODUCT_INFO,
                                              xtc_get_all_get_params(['action']) . 'action=add_product',
                                              'NONSSL',
                                              true,
                                              true,
                                              true));
        $this->set_content_data('FORM_ID', 'cart_quantity');
        $this->set_content_data('FORM_NAME', 'cart_quantity');
        $this->set_content_data('FORM_METHOD', 'post');
    }
    
    protected function _assignModelNumber()
    {
        $modelNumber = $this->selling_unit->model()->value();
        $this->set_content_data('SHOW_PRODUCTS_MODEL',
                                gm_get_conf('SHOW_PRODUCTS_MODEL_IN_PRODUCT_DETAILS') === 'true');
        $this->set_content_data('PRODUCTS_MODEL', $modelNumber);
    }
    
    
    protected function _assignQuantity()
    {
        $quantity    = '';
        $measureUnit = $this->selling_unit->stock()->availableQuantity()->measureUnit();
        
        // get main quantity unit from the product in case selling unit is not set yet (workaround)
        if(!$measureUnit) {
            $measureUnit = $this->product->data['unit_name'] ?? $measureUnit;
        }
        
        if (1 === (int)$this->product->data['gm_show_qty_info']) {
            if ($this->selling_unit->stock()->availableQuantity()->mainQuantity()) {
                $quantity = $this->selling_unit->stock()->availableQuantity()->mainQuantity()->value();
            }
            if (!$this->selling_unit->stock()->availableQuantity()->isValid()) {
                //hack to force the first load of the page to include the html block if the quantity is lower than or equals 0
                $quantity = '-';
            }
        }
        
        $this->set_content_data('PRODUCTS_QUANTITY', $quantity);
        $this->set_content_data('PRODUCTS_QUANTITY_UNIT', $measureUnit);
    }
    
    
    protected function _assignDeactivatedButtonFlag()
    {
        $deactivateButton = false;
        if (!$this->selling_unit->stock()->availableQuantity()->isValid()) {
            $deactivateButton = true;
        }
    
        if ($this->selling_unit->selectedQuantity()
            ->hasException(QuantitySurpassMaximumAllowedQuantityException::class)) {
            $deactivateButton = true;
        }
        if ($this->selling_unit->selectedQuantity()->hasException(RequestedQuantityBelowMinimumException::class)) {
            $deactivateButton = true;
        }
        if ($this->selling_unit->selectedQuantity()->hasException(InvalidQuantityGranularityException::class)) {
            $deactivateButton = true;
        }
    
        if ($this->selling_unit->selectedQuantity()->hasException(InvalidQuantityException::class)) {
            $deactivateButton = true;
        }
    
        if (STOCK_CHECK === 'true' && CHECK_STOCK_BEFORE_SHOPPING_CART === 'true' && STOCK_ALLOW_CHECKOUT !== 'true'
            && $this->selling_unit->selectedQuantity()->hasException(InsufficientQuantityException::class)) {
            $deactivateButton = true;
        }
        
        $this->set_content_data('DEACTIVATE_BUTTON', $deactivateButton);
    }
    
    
    protected function _assignWeight()
    {
        $showWeight = $this->selling_unit->weight() && $this->selling_unit->weight()->show();
        $weight = '';
        if ($this->selling_unit->weight()) {
            $weight = $this->selling_unit->weight()->value() ? : '-';
        }
    
        $this->set_content_data('SHOW_PRODUCTS_WEIGHT', $showWeight ? 1 : 0);
        $this->set_content_data('PRODUCTS_WEIGHT', $weight);
    }


    protected function _assignVpe()
    {
        $vpeHtml = '';
        if ($this->selling_unit->vpe() && $this->_showPrice()) {
            $vpeHtml = $this->_buildVpeHtml(
                $this->selling_unit->price()->pricePlain()->value(),
                $this->selling_unit->vpe()->id(),
                $this->selling_unit->vpe()->value());
        }
        $this->set_content_data('PRODUCTS_VPE', $vpeHtml);
    }
    
    
    /**
     * @param $price
     * @param $vpeId
     * @param $vpeValue
     *
     * @return string
     */
    protected function _buildVpeHtml($price, $vpeId, $vpeValue)
    {
        $vpePrice       = $price * (1 / $vpeValue);
        $priceFormatted = $this->xtcPrice->xtcFormat($vpePrice, true);
        $vpeName        = xtc_get_vpe_name($vpeId);
        
        return $priceFormatted . TXT_PER . $vpeName;
    }
    
    
    protected function _assignShippingTime()
    {
        $languageTextManager = MainFactory::create('LanguageTextManager', 'product_info');
        
        $name             = '';
        $image            = '';
        $imageAlt         = '';
        $abroadLinkActive = false;
        $abroadLink       = '';
        
        if (ACTIVATE_SHIPPING_STATUS == 'true') {
            $name             = $this->selling_unit->shipping()->description() . '&nbsp;';
            $image            = $this->selling_unit->shipping()->image();
            $imageAlt = ($this->selling_unit->shipping()->description() !== '') ? $this->selling_unit->shipping()->description() : $languageTextManager->get_text('unknown_shippingtime');
            $abroadLinkActive = $this->selling_unit->shipping()->abroadLinkActive();
            $abroadLink       = $this->selling_unit->shipping()->abroadLink() . '&nbsp;';
        }
        
        $this->set_content_data('SHIPPING_NAME', $name);
        $this->set_content_data('SHIPPING_IMAGE', $image);
        $this->set_content_data('SHIPPING_IMAGE_ALT', $imageAlt);
        $this->set_content_data('ABROAD_SHIPPING_INFO_LINK_ACTIVE', $abroadLinkActive);
        $this->set_content_data('ABROAD_SHIPPING_INFO_LINK', $abroadLink);
    }
    
    
    protected function _assignEan()
    {
        $this->set_content_data('PRODUCTS_EAN', $this->selling_unit->ean()->value());
    }
    
    
    protected function _assignId()
    {
        $this->set_content_data('PRODUCTS_ID', $this->selling_unit->id()->productId()->value());
    }
    
    
    protected function _assignName()
    {
        $this->set_content_data('PRODUCTS_NAME', $this->selling_unit->productInfo()->name()->value());
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
            $view = MainFactory::create_object('AdditionalFieldThemeContentView');
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
     * @return ProductInfoThemeContentView
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
     * @return ProductInfoThemeContentView
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
     * @return ProductInfoThemeContentView
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
     * @return ProductInfoThemeContentView
     */
    public function setAppendPropertiesModel($appendPropertiesModel)
    {
        $this->appendPropertiesModel = (bool)$appendPropertiesModel;
        
        return $this;
    }
    
    
    /**
     * @param bool $showPriceTax
     *
     * @return ProductInfoThemeContentView
     */
    public function setShowPriceTax($showPriceTax)
    {
        $this->showPriceTax = (bool)$showPriceTax;
        
        return $this;
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
    
    ##### DEPRECATED since GX2.2 #####
    
    
    /**
     * @deprecated
     */
    protected function _assignDeprecatedPurchaseData()
    {
        // deprecated
    }
    
    
    /**
     * @deprecated
     */
    protected function _assignDeprecatedWishlist()
    {
        // deprecated
    }
    
    
    /**
     * @deprecated
     */
    protected function _assignDeprecatedPrintLink()
    {
        // deprecated
    }
    
    
    /**
     * @deprecated
     */
    protected function _assignDeprecatedFormTagData()
    {
        // deprecated
    }
    
    
    /**
     * @deprecated
     */
    protected function _assignDeprecatedIdHiddenField()
    {
        // deprecated
    }
    
    
    /**
     * @deprecated
     */
    protected function _assignDeprecatedDimensionValues()
    {
        // deprecated
    }
    
    
    /**
     * @return string
     */
    protected function _getMediaContentHtml()
    {
        /* @var ProductMediaThemeContentView $view */
        $view = MainFactory::create_object('ProductMediaThemeContentView');
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
            $languageCode   = $languageHelper->getLanguageCodeById(new IdType($this->languageId))->asString();
            
            $supportedLanguages = ['DE', 'EN', 'ES', 'FR', 'IT', 'NL'];
            
            if (!in_array($languageCode, $supportedLanguages, true)) {
                $languageCode = 'EN';
            }
            
            $buttonStyle    = $paypalConfiguration->get('ecs_button_style');
            $buttonImageUrl = GM_HTTP_SERVER . DIR_WS_CATALOG . 'images/icons/paypal/' . $buttonStyle . 'Btn_'
                              . $languageCode . '.png';
            $configuration  = [
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
     * @throws PresentationMapperNotFoundException
     */
    protected function init_groups(): void
    {
        $repository           = $this->createGroupRepositoryFactory()->createRepository();
        $this->modifierGroups = $repository->getGroupsBySellingUnit($this->selling_unit_id);
        $this->modifier_ids   = $this->createModifierIdentifierCollection();

    }
    
    
    /**
     *
     */
    protected function init_selling_unit(): void
    {
        
        if ($this->selling_unit_id === null) {
            
            $language = new LanguageId($this->languageId);
            if ($this->combiId > 0) {
                $this->selling_unit_id = SellingUnitIdFactory::instance()->createFromCustom('combi',
                                                                                            $this->combiId,
                                                                                            $language);
            } else {
                $this->selling_unit_id = SellingUnitIdFactory::instance()->createFromProductString($this->product->data['products_id'],
                                              $language);
            }
        }
        
        if ($this->selling_unit_id) {
            /**
             * @var SellingUnitReadServiceInterface $service
             */
            $service            = LegacyDependencyContainer::getInstance()->get(SellingUnitReadServiceInterface::class);
            $this->selling_unit = $service->getSellingUnitBy($this->selling_unit_id, $this->product, $this->xtcPrice);
        }
    }
    
    
    /**
     * @param SellingUnitId $id
     */
    public function set_selling_unit_id(SellingUnitId $id): void
    {
        $this->selling_unit_id = $id;
    }
    
    
    /**
     * @param SellingUnitInterface     $sellingUnit
     * @param CustomersStatusShowPrice $showPriceStatus
     *
     * @return ModifierGroupsThemeContentView
     */
    protected function createModifierGroupsThemeContentView(
        SellingUnitInterface $sellingUnit,
        CustomersStatusShowPrice $showPriceStatus
    ): ModifierGroupsThemeContentView {
        return MainFactory::create('ModifierGroupsThemeContentView', $sellingUnit, $showPriceStatus);
    }
    
    
    /**
     * @return ModifierIdentifierCollection
     */
    protected function createModifierIdentifierCollection(): ModifierIdentifierCollection
    {
        return new ModifierIdentifierCollection();
    }
    
    
    /**
     * @return GroupRepositoryFactory
     */
    protected function createGroupRepositoryFactory(): GroupRepositoryFactory
    {
        return new GroupRepositoryFactory();
    }
    
    
    /**
     * @param $combi
     *
     * @return array
     */
    protected function getCombinationImages(array $combi): array
    {
        $result = [];
        
        if (!empty($combi['product_image_list_id'])) {
            
            $imageList = $this->getProductImageList((int)$combi['product_image_list_id']);
            
            if ($imageList->count()) {
                
                foreach ($imageList as $image) {
                    
                    $result[] = $this->infoImagesPathFromOriginalImages($image->webFilePath());
                }
            }
        }
        
        return $result;
    }
    
    
    /**
     * @param int $imageListId
     *
     * @return ImageList
     */
    protected function getProductImageList(int $imageListId): ImageList
    {
        if (!isset($this->imageLists[$imageListId])) {
            
            $this->imageLists[$imageListId] = $this->imageListReadService->getImageListById($imageListId);
        }
        
        return $this->imageLists[$imageListId];
    }
    
    
    /**
     * @param array $attributeIds
     *
     * @return array
     */
    protected function getImagesForAttributeIds(array $attributeIds): array
    {
        $result = [];
        
        foreach ($attributeIds as $attributeId) {
            
            $imageList = $this->getImageListByAttributesId((int)$attributeId);
            
            if ($imageList->count()) {
                
                foreach ($imageList as $image) {
                    
                    $result[] = $this->infoImagesPathFromOriginalImages($image->webFilePath());
                }
            }
        }
        
        return $result;
    }
    
    
    /**
     * @param int $attributesId
     *
     * @return ImageList
     */
    protected function getImageListByAttributesId(int $attributesId): ImageList
    {
        if (!isset($this->attributeImageLists[$attributesId])) {
            
            try {
                $this->attributeImageLists[$attributesId] = $this->imageListReadService->getImageListByAttributeId(new AttributeIdDto($attributesId));
            } catch (AttributeDoesNotHaveAListException $exception) {
                unset($exception);
                $this->attributeImageLists[$attributesId] = new ImageList(new ListName('empty list'));
            }
        }
        
        return $this->attributeImageLists[$attributesId];
    }
    
    
    /**
     * @return string
     */
    protected function getCollectionType(): string
    {
        if ($this->collectionType === null) {
            
            /** @var GmConfigurationServiceInterface $gxConfigurationService */
            $gxConfigurationService = StaticGXCoreLoader::getService('GmConfiguration');
            $configuration          = $gxConfigurationService->getConfigurationByKey('DISPLAY_OF_PROPERTY_COMBINATION_SELECTION');
            
            $this->collectionType = $configuration->value();
        }
        
        return $this->collectionType;
    }
    
    
    /**
     * @param product_ORIGIN $product
     *
     * @return string[]|string
     * @throws Exception
     */
    protected function getProductImage($product)
    {
        $result           = [];
        $productId        = new ProductId($product->pID);
        $languageIdObject = new LanguageId((int)$_SESSION['languages_id']);
        $mainProductImage = $this->imageReadService->mainProductImage($productId, $languageIdObject);
        
        if ($mainProductImage === null) {
            return [];
        }
        
        $result[] = (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . $mainProductImage->infoUrl()->value();
        
        $imageCollection = $this->imageReadService->getProductImages($productId, $languageIdObject);
        
        if ($imageCollection !== null && $imageCollection->count()) {
            
            foreach ($imageCollection as $image) {
                
                $result[] = (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . $image->infoUrl()->value();
            }
        }
        
        $result = array_values(array_unique($result));
        
        return $result;
    }
    
    
    /**
     * @param WebFilePath $path
     *
     * @return string
     */
    protected function infoImagesPathFromOriginalImages(WebFilePath $path): string
    {
        $originalImagesPath = (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG
                              . 'images/product_images/original_images';
        $infoImagesPath     = (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG
                              . 'images/product_images/info_images';
        
        return str_replace($originalImagesPath, $infoImagesPath, $path->value());
    }
    
    
    /**
     *  get all the error messages from the sellingUnit
     */
    protected function _assignErrorMessages()
    {
        $errorMessages = [];
        
        if ($this->selling_unit->selectedQuantity()
            ->hasException(QuantitySurpassMaximumAllowedQuantityException::class)) {
            foreach ($this->selling_unit->selectedQuantity()
                         ->exceptions(QuantitySurpassMaximumAllowedQuantityException::class) as $exception) {
                /** @var QuantitySurpassMaximumAllowedQuantityException $exception */
                $errorMessages[] = $exception->getMessage() ? : GM_ORDER_QUANTITY_CHECKER_MAX_ERROR_1
                                                                . $exception->maxQuantity()
                                                                . GM_ORDER_QUANTITY_CHECKER_MAX_ERROR_2;
            }
        }
        if ($this->selling_unit->selectedQuantity()->hasException(RequestedQuantityBelowMinimumException::class)) {
            foreach ($this->selling_unit->selectedQuantity()
                         ->exceptions(RequestedQuantityBelowMinimumException::class) as $exception) {
                /** @var RequestedQuantityBelowMinimumException $exception */
                $errorMessages[] = $exception->getMessage() ? : GM_ORDER_QUANTITY_CHECKER_MIN_ERROR_1
                                                                . $exception->minOrder()
                                                                . GM_ORDER_QUANTITY_CHECKER_MIN_ERROR_2;
            }
        }
        if ($this->selling_unit->selectedQuantity()->hasException(InvalidQuantityGranularityException::class)) {
            foreach ($this->selling_unit->selectedQuantity()
                         ->exceptions(InvalidQuantityGranularityException::class) as $exception) {
                
                /** @var InvalidQuantityGranularityException $exception */
                $errorMessages[] = $exception->getMessage() ? : GM_ORDER_QUANTITY_CHECKER_GRADUATED_ERROR_1
                                                                . $exception->granularity()
                                                                . GM_ORDER_QUANTITY_CHECKER_GRADUATED_ERROR_2;
            }
        }
        
        if ($this->selling_unit->selectedQuantity()->hasException(InvalidQuantityException::class)) {
            foreach ($this->selling_unit->selectedQuantity()
                         ->exceptions(InvalidQuantityException::class) as $exception) {
                /** @var InvalidQuantityException $exception */
                $errorMessages[] = $exception->getMessage() ? : InvalidQuantityException::class;
            }
        }
        

        if (CHECK_STOCK_BEFORE_SHOPPING_CART === 'true' && $this->selling_unit->selectedQuantity()->hasException(InsufficientQuantityException::class)) {
            foreach ($this->selling_unit->selectedQuantity()
                         ->exceptions(InsufficientQuantityException::class) as $exception) {
                /** @var InsufficientQuantityException $exception */
                $errorMessages[] = $exception->getMessage() ? : InsufficientQuantityException::class;
            }
        }
        
        if (!empty($errorMessages)) {
            $this->set_content_data('ERROR_MESSAGES', implode('<br>', array_unique($errorMessages)));
        }
    }
}
