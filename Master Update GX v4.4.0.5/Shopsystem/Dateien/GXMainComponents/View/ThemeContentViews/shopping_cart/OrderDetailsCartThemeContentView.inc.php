<?php
/*--------------------------------------------------------------------------------------------------
    OrderDetailsCartThemeContentView.inc.php 2020-12-09
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

// include needed functions
use Gambio\Core\Application\Application;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\SellingUnit\Unit\Exceptions\InsufficientQuantityException;
use Gambio\Shop\SellingUnit\Unit\Factories\Interfaces\SellingUnitIdFactoryInterface;
use Gambio\Shop\SellingUnit\Unit\SellingUnit;
use Gambio\Shop\SellingUnit\Unit\SellingUnitInterface;
use Gambio\Shop\SellingUnit\Unit\Services\Interfaces\SellingUnitReadServiceInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ReserveScope;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ScopedQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

require_once(DIR_FS_INC . 'xtc_check_stock.inc.php');
require_once(DIR_FS_INC . 'xtc_get_products_stock.inc.php');
require_once(DIR_FS_INC . 'xtc_remove_non_numeric.inc.php');
require_once(DIR_FS_INC . 'xtc_get_short_description.inc.php');
require_once(DIR_FS_INC . 'xtc_format_price.inc.php');
require_once(DIR_FS_INC . 'xtc_get_attributes_model.inc.php');

require_once(DIR_FS_INC . 'get_products_vpe_array.inc.php');
require_once(DIR_FS_CATALOG . 'gm/inc/gm_prepare_number.inc.php');

MainFactory::load_class('OrderDetailsCartContentViewInterface');

/**
 * Class OrderDetailsCartThemeContentView
 */
class OrderDetailsCartThemeContentView extends ThemeContentView implements OrderDetailsCartContentViewInterface
{
    protected $langFileMaster;
    protected $propertiesControl;
    protected $propertiesView;
    protected $giftCartThemeContentView;
    protected $cartShippingCostControl;
    protected $cartShippingCostsThemeContentView;
    protected $gprintContentManager;
    protected $products;
    protected $productsCopy;
    protected $main;
    /** @var \xtcPrice_ORIGIN */
    protected $xtcPrice;
    protected $moduleContent         = [];
    protected $total                 = 0;
    protected $discount              = 0;
    protected $productsQuantityArray = [];
    protected $coo_products;
    protected $sessionAnyOutOfStock  = 0;
    protected $cart;
    protected $language;
    protected $gprintCart;
    protected $customerStatus;
    protected $shippingNumBoxes;
    protected $shippingWeight;
    protected $subtotal              = 0;
    protected $buttonBackUrl;

    /**
     * @var SellingUnit[]
     */
    protected $sellingUnits = [];


    public function __construct()
    {
        parent::__construct();
        $this->set_content_template('cart_order_preview.html');
    }


    public function setXtcPrice(xtcPrice $p_xtPrice)
    {
        $this->xtcPrice = $p_xtPrice;
    }


    public function setMain(main $p_main)
    {
        $this->main = $p_main;
    }


    public function setProducts(array $p_products)
    {
        $this->products = $p_products;
    }


    public function setLangFileMaster($p_langFileMaster)
    {
        $this->langFileMaster = $p_langFileMaster;
    }


    public function setPropertiesControl($p_propertiesControl)
    {
        $this->propertiesControl = $p_propertiesControl;
    }


    public function setPropertiesView($p_propertiesView)
    {
        $this->propertiesView = $p_propertiesView;
    }


    public function setGiftCartThemeContentView($p_giftCart)
    {
        $this->giftCartThemeContentView = $p_giftCart;
    }


    public function setCartShippingCostsControl($p_cartShippingCostsControl)
    {
        $this->cartShippingCostControl = $p_cartShippingCostsControl;
    }


    public function setCartShippingCostsThemeContentView($p_cartShippingCostsThemeContentView)
    {
        $this->cartShippingCostsThemeContentView = $p_cartShippingCostsThemeContentView;
    }


    public function setGprintContentManager($p_gprintContentManager)
    {
        $this->gprintContentManager = $p_gprintContentManager;
    }


    public function prepare_data()
    {
        $this->_getSessionVariablesGlobals();


        $this->set_content_data('customer_status_allow_checkout',
                                $_SESSION['customers_status']['customers_status_show_price']);

        $this->productsCopy = $this->products;

        for ($i = 0, $n = count($this->products); $i < $n; $i++) {
            $t_price_num        = $this->products[$i]['price'] * $this->products[$i]['quantity'];
            $t_price            = $this->xtcPrice->xtcFormat($t_price_num, true);
            $t_price_single_num = $this->products[$i]['price'];
            $t_price_single     = $this->xtcPrice->xtcFormat($t_price_single_num, true);
            if ($_SESSION['customers_status']['customers_status_show_price'] != '1') {
                $t_price        = '--';
                $t_price_single = '--';
                $this->set_content_data('customer_status_allow_checkout', 0);
            }

            $combisId = $this->_getCombisId($this->products[$i]);

            $propertiesControl = MainFactory::create_object('PropertiesControl');

            if ($combisId !== '' && $propertiesControl->combi_exists($this->products[$i]['id'], $combisId) === false) {
                $this->products[$i]['id'] = explode('x', $this->products[$i]['id'])[0];
                $combisId                 = '';
            }

            $sellingUnit    = $this->getSellingUnitByProductArray($this->products, $i);
            $markStock      = strip_tags($this->_getCheckMarkStock($sellingUnit));
            $shippingTime   = $this->_getShippingTime($this->products[$i], $combisId);

            $this->set_content_data('out_of_stock_mark', STOCK_MARK_PRODUCT_OUT_OF_STOCK);


            $sellingUnitPresenter = $sellingUnit->presenter();
            $weight = 0;
            if($sellingUnit->weight() && $sellingUnit->weight()->show() ){
                $weight = $sellingUnit->weight()->value();
            }

            $this->moduleContent[$i]                       = [
                'PRODUCTS_NAME'              => $sellingUnit->productInfo()->name()->value(),
                'STOCK_MARK'                 => $markStock,
                'PRODUCTS_OLDQTY_INPUT_NAME' => 'old_qty[]',
                'PRODUCTS_QTY_INPUT_NAME'    => 'cart_quantity[]',
                'PRODUCTS_QTY_VALUE'         => $sellingUnit->selectedQuantity()->value(),
                'PRODUCTS_ID_INPUT_NAME'     => 'products_id[]',
                'PRODUCTS_ID_EXTENDED'       => (string)$sellingUnitPresenter->getPresentationIdCollection(),
                'PRODUCTS_MODEL'             => $sellingUnit->model()->value(),
                'SHOW_PRODUCTS_MODEL'        => gm_get_conf('SHOW_PRODUCTS_MODEL_IN_SHOPPING_CART_AND_WISHLIST'),
                'PRODUCTS_SHIPPING_TIME'     => $shippingTime,
                'PRODUCTS_TAX'               => $sellingUnit->taxInfo()->asFloat(),
                'PRODUCTS_IMAGE'             => $this->mainProductImage($this->products, $i),
                'IMAGE_ALT'                  => $this->getMainProductImageAltText($this->products, $i),
                'PRODUCTS_LINK'              => $sellingUnitPresenter->getProductLink()->value(),
                'PRODUCTS_PRICE'             => $t_price,
                'PRODUCTS_SINGLE_PRICE'      => $t_price_single,
                'PRODUCTS_SHORT_DESCRIPTION' => $sellingUnitPresenter->getShortDescription()->value(),
                'MODIFIERS'                  => $sellingUnitPresenter->getModifierHtml(),
                'GM_WEIGHT'                  => $weight,
                'PRODUCTS_ID'                => $sellingUnit->id()->productId()->value(),
                'UNIT'                       => $this->products[$i]['unit_name'],
                'PRODUCTS_VPE_ARRAY'         => ''
            ];

            $this->moduleContent[$i]['PRODUCTS_VPE_ARRAY'] = $this->_getProductAttributes($this->products[$i],
                                                                                          $combisId,
                                                                                          $markStock,
                                                                                          $this->moduleContent[$i]);
        }
        $this->total    = $this->cart->show_total();
        $this->subtotal = $this->total;
	
	    $this->_checkSellingUnitQuantity();
        
        $this->_setCustomerDiscount();

        $this->_setTaxText();
        $this->_setTaxData();

        $this->_setContentDataSubTotal();
        $this->_setContentDataTotal();
        $this->_setContentDataLanguage();
        $this->_setContentDataModuleContent();

        $this->_setShippingInfo();
        $this->_setShippingWeightInfo();
        $this->_setContentDataGiftCartContentView();
	
        $this->_setSessionVariables();

        $this->_setButtonBackUrl();
        $this->_setContentDataButtonsBackUrl();
        $this->_setOrderTotals();
        $this->_setContentGiftVoucher();
    }


    protected function _setOrderTotals()
    {
        if (!empty($this->products)) {
            $cartShippingCostsControl = CartShippingCostsControl::get_instance();
            /** @var array $selectedShippingModuleArray */
            $selectedShippingModuleArray = $cartShippingCostsControl->get_selected_shipping_module();
            [$selectedShippingModule, $selectedShippingMethod] = explode('_', key($selectedShippingModuleArray));
            $selectedShippingModuleLabel = current($selectedShippingModuleArray);
            /** @var \shipping_ORIGIN $shipping */
            $shipping             = MainFactory::create('shipping');
            $selectedModuleQuote  = $shipping->quote($selectedShippingMethod, $selectedShippingModule);
            $_SESSION['shipping'] = [
                'id'    => $selectedShippingModule . '_' . $selectedShippingMethod,
                'title' => $selectedShippingModuleLabel,
            ];
            if (!empty($selectedModuleQuote)) {
                foreach ($selectedModuleQuote[0]['methods'] as $methodArray) {
                    if ($methodArray['id'] === $selectedShippingMethod) {
                        $_SESSION['shipping']['cost'] = $methodArray['cost'];
                    }
                }
            }
            /** @var \order_ORIGIN $t_order */
            $t_order          = new order();
            $GLOBALS['order'] = $t_order;
            /** @var \order_total_ORIGIN $order_total_modules */
            $order_total_modules = new order_total();
            $order_total_modules->collect_posts();
            $order_total_modules->pre_confirmation_check();
            $totals      = $order_total_modules->process();
            $sumEntryIdx = false;
            foreach ($totals as $totalsIdx => $totalsEntry) {
                $totalsEntry['title'] = trim($totalsEntry['title']);
                if (substr($totalsEntry['title'], -1, 1) === ':') {
                    $totals[$totalsIdx]['title'] = substr($totalsEntry['title'], 0, -1);
                }
                if ($totalsEntry['code'] === 'ot_total') {
                    $sumEntryIdx = $totalsIdx;
                }
            }
            if ($sumEntryIdx !== null) {
                $this->_setContentDataTotal($totals[$sumEntryIdx]['text']);
                unset($totals[$sumEntryIdx]);
            }
            $this->set_content_data('ordertotals', $totals);

            $showCouponInfo = strtolower((string)@constant('MODULE_ORDER_TOTAL_COUPON_SHOW_INFO')) === 'true';
            $this->set_content_data('show_coupon_info', $showCouponInfo);

            if (defined('MODULE_ORDER_TOTAL_GV_SHOW_INFO') && strtolower(MODULE_ORDER_TOTAL_GV_SHOW_INFO) === 'true') {
                $text        = MainFactory::create('LanguageTextManager', 'ot_gv');
                $voucherInfo = [];
                if (!isset ($_SESSION['cot_gv']) || (bool)$_SESSION['cot_gv'] === true) {
                    $balance = $this->getCustomerGVBalance(new IdType((int)$_SESSION['customer_id']));
                    if ($balance > 0) {
                        $balance       = $this->xtcPrice->xtcCalculateCurr($balance);
                        $voucherInfo[] = [
                            'coupon_code'   => $text->get_text('CUSTOMER_BALANCE'),
                            'coupon_amount' => $this->xtcPrice->xtcFormat($balance, true),
                            'remove_url'    => '',
                        ];
                    }
                }

                if (isset ($_SESSION['gift_vouchers'])) {
                    foreach ($_SESSION['gift_vouchers'] as $voucher) {
                        $couponData    = $this->getCouponDetails(new IdType((int)$voucher['coupon_id']));
                        $couponAmount  = $this->xtcPrice->xtcCalculateCurr($couponData['coupon_amount']);
                        $voucherInfo[] = [
                            'coupon_code'   => $text->get_text('VOUCHER') . ' ' . $couponData['coupon_code'],
                            'coupon_amount' => $this->xtcPrice->xtcFormat($couponAmount, true),
                            'remove_url'    => xtc_href_link('shop.php',
                                                             'do=Cart/RemoveVoucherByCode&couponcode='
                                                             . $couponData['coupon_code']),
                        ];
                    }
                }

                $this->set_content_data('voucher_info', $voucherInfo);
            }
        }
        else {
            // set order totals with empty array anyway
            $this->set_content_data('ordertotals', []);
        }
    }


    /**
     * Returns a customer’s current balance from coupons/vouchers.
     *
     * @param \IdType $customerID
     *
     * @return float
     */
    protected function getCustomerGVBalance(IdType $customerID)
    {
        $balance = 0.0;
        if ($customerID->asInt() !== 0) {
            $db            = StaticGXCoreLoader::getDatabaseQueryBuilder();
            $customerGvRow = $db->get_where('coupon_gv_customer', ['customer_id' => $customerID->asInt()])->row_array();
            if (!empty($customerGvRow)) {
                $balance = (float)$customerGvRow['amount'];
            }
        }

        return $balance;
    }


    /**
     * Retrieves details about a coupon.
     *
     * @param \IdType $couponID
     *
     * @return array|null
     */
    protected function getCouponDetails(IdType $couponID)
    {
        $db        = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $couponRow = $db->get_where('coupons', ['coupon_id' => $couponID->asInt()])->row_array();

        return $couponRow;
    }


    /*
     * Method for collect all session-variables an globals
     */

    protected function _getSessionVariablesGlobals()
    {
        $this->cart             = $_SESSION['cart'];
        $this->language         = $_SESSION['language'];
        $this->gprintCart       = $_SESSION['coo_gprint_cart'];
        $this->customerStatus   = $_SESSION['customers_status'];
        $this->shippingNumBoxes = $GLOBALS['shipping_num_boxes'] ?? null;
        $this->shippingWeight   = $GLOBALS['shipping_weight'] ?? null;
    }


    /*
     * Method for setting all session-variables
     */

    protected function _setSessionVariables()
    {
        $_SESSION['any_out_of_stock'] = $this->sessionAnyOutOfStock;
    }


    protected function _setDiscount($p_price)
    {
        $this->discount      = round($this->xtcPrice->xtcGetDC($p_price,
                                                               $this->customerStatus['customers_status_ot_discount']),
                                     2);
        $priceSpecial        = 1;
        $calculateCurrencies = false;
        $this->_setContentDataDiscountValue();
        $this->_setContentDataDiscountText();
        if ($this->customerStatus['customers_status_show_price'] == '1') {
            if ($this->customerStatus['customers_status_show_price_tax'] == 0
                && $this->customerStatus['customers_status_add_tax_ot'] == 0) {
                $this->total -= $this->discount;
            }
            if ($this->customerStatus['customers_status_show_price_tax'] == 0
                && $this->customerStatus['customers_status_add_tax_ot'] == 1) {
                $this->total -= $this->discount;
            }
            if ($this->customerStatus['customers_status_show_price_tax'] == 1) {
                $this->total -= $this->discount;
            }
            $this->subtotal = $this->total + $this->discount;
        }
    }


    protected function _setTaxText()
    {
        if ($this->customerStatus['customers_status_show_price'] == '1') {
            if (gm_get_conf('TAX_INFO_TAX_FREE') == 'true') {
                $this->_setContentDataTaxFreeText();
            } else {
                $cartTaxInfo = $this->cart->show_tax();
                if (!empty($cartTaxInfo) && $this->customerStatus['customers_status_show_price_tax'] == '0'
                    && $this->customerStatus['customers_status_add_tax_ot'] == '1') {
                    if (!defined('MODULE_ORDER_TOTAL_SUBTOTAL_TITLE_NO_TAX')) {
                        $this->langFileMaster->init_from_lang_file('lang/' . $this->language
                                                                   . '/modules/order_total/ot_subtotal.php');
                    }

                    $tax = 0;
                    foreach ($this->cart->tax as $keyTax => $valueTax) {
                        $tax += $valueTax['value'];
                    }

                    $this->subtotal = (double)$this->total - (double)$tax + $this->discount;
                }
            }
        }
    }


    protected function _setShippingWeightInfo()
    {
        if (SHOW_CART_SHIPPING_WEIGHT == 'true' && SHOW_CART_SHIPPING_COSTS == 'false') {
            if (isset($this->shippingNumBoxes) === false && isset($this->shippingWeight) === false) {
                $this->cartShippingCostControl->get_shipping_modules();
                $this->shippingNumBoxes = $GLOBALS['shipping_num_boxes'];
                $this->shippingWeight   = $GLOBALS['shipping_weight'];
            }
            $this->_setContentDataShowShippingWeight(1);
            $this->_setContentDataShippingWeight(gm_prepare_number($this->shippingNumBoxes * $this->shippingWeight,
                                                                   $this->xtcPrice->currencies[$this->xtcPrice->actualCurr]['decimal_point']));

            $showShippingWeightInfo = 0;
            if ((double)SHIPPING_BOX_WEIGHT > 0 || (double)SHIPPING_BOX_PADDING > 0) {
                $showShippingWeightInfo = 1;
            }
            $this->_setContentDataShowShippingWeightInfo($showShippingWeightInfo);
        } else {
            $this->_setContentDataShowShippingWeight(0);
        }
    }


    protected function _setTaxData()
    {
        $taxesDataArray = explode('<br />', $this->cart->show_tax(true));
        $taxArray       = [];
        for ($i = 0; $i < count($taxesDataArray); $i++) {
            if (!empty($taxesDataArray[$i])) {
                $taxDataArray = explode(':', $taxesDataArray[$i]);
                $taxArray[]   = [
                    'TEXT'  => $taxDataArray[0],
                    'VALUE' => $taxDataArray[1]
                ];
            }
        }
        $this->_setContentDataTaxData($taxArray);
    }


    protected function _setShippingInfo()
    {
        $this->set_content_data('OT_SHIPPING_INSTALLED', defined('MODULE_ORDER_TOTAL_SHIPPING_DESTINATION'));

        if (SHOW_CART_SHIPPING_COSTS == 'true') {
            $this->_setContentDataShippingInfoExcl(SHIPPING_EXCL);
            $this->_setContentDataShippingInfoShippingLink($this->main->gm_get_shipping_link(true));
            $this->_setContentDataShippingInfoShippingCosts(SHIPPING_COSTS);

            $this->_setContentDataShippingCostsSelection($this->cartShippingCostsThemeContentView->get_html());

            $forceAddingTax = false;
            if ($_SESSION['customers_status']['customers_status_add_tax_ot'] === '1'
                && $_SESSION['customers_status']['customers_status_show_price_tax'] === '0') {
                $forceAddingTax = true;
            }

            $cartShippingCostsValue = $this->cartShippingCostControl->get_shipping_costs(false,
                                                                                         false,
                                                                                         '',
                                                                                         false,
                                                                                         $forceAddingTax);

            if ($this->cartShippingCostControl->is_shipping_free() === true) {
                $cartShippingCostsValue = $this->xtcPrice->xtcFormat(0, true);
            }
            $this->_setContentDataShippingInfoGabmioultra($this->cartShippingCostControl->get_ot_gambioultra_costs(false,
                                                                                                                   $forceAddingTax));
            $this->_setContentDataShippingInfoShippingCostsValue($cartShippingCostsValue);
        } elseif (SHOW_SHIPPING == 'true') {
            $this->_setContentDataShippingInfoExcl(SHIPPING_EXCL);
            $this->_setContentDataShippingInfoShippingLink($this->main->gm_get_shipping_link(true));
            $this->_setContentDataShippingInfoShippingCosts(SHIPPING_COSTS);
        }
    }


    protected function _setCustomerDiscount()
    {
        if ($this->customerStatus['customers_status_ot_discount_flag'] == '1'
            && $this->customerStatus['customers_status_ot_discount'] != '0.00') {
            $this->_setDiscount($this->_getPrice());
        }
    }


    protected function _deleteEmptyAttributes(&$p_moduleContent, $p_product, $p_value)
    {
        // delete empty attributes (random id)
        if (isset($p_moduleContent['ATTRIBUTES'])) {
            foreach ($p_moduleContent['ATTRIBUTES'] as $keyAttribute => $valAttribute) {
                if (empty($p_moduleContent['ATTRIBUTES'][$keyAttribute]['NAME'])) {
                    unset($p_moduleContent['ATTRIBUTES'][$keyAttribute]);
                }
            }
        }

        $p_product['id'] = $this->_fixProductId($p_product['id']);

        if (isset($this->gprintCart->v_elements[$p_product['id']]) && $p_value == 0) {
            $gprintData = $this->gprintContentManager->get_content($p_product['id'], 'cart');
            for ($j = 0; $j < count($gprintData); $j++) {
                $p_moduleContent['ATTRIBUTES'][] = [
                    'ID'         => 0,
                    'MODEL'      => '',
                    'NAME'       => $gprintData[$j]['NAME'],
                    'VALUE_NAME' => $gprintData[$j]['VALUE']
                ];
            }
        }
    }

    protected function _checkSellingUnitQuantity()
    {
        foreach ($this->sellingUnits as $sellingUnit) {
            if ($sellingUnit->selectedQuantity()->hasException(InsufficientQuantityException::class)) {
                $this->sessionAnyOutOfStock = 1;
                $this->productsQuantityArray[$sellingUnit->id()->productId()->value()] = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';


            }
        }
    }


    /*
     * helper-Methods with return-values
     */

    protected function _getCheckMarkStock(SellingUnitInterface $sellingUnit)
    {
        $result = '';
        if ($sellingUnit->selectedQuantity()->hasException(InsufficientQuantityException::class)) {
            $result = STOCK_MARK_PRODUCT_OUT_OF_STOCK;
        }
        return $result;
    }


    protected function _getProductId($p_productId)
    {
        $productId = $p_productId['id'];
        $productId = str_replace('{', '_', $productId);
        $productId = str_replace('}', '_', $productId);

        return $productId;
    }


    protected function _getImageThumbnailPath(array $p_product)
    {
        $image = '';
        if (isset($p_product['image']) && $p_product['image'] != '') {
            $image = DIR_WS_THUMBNAIL_IMAGES . $p_product['image'];
        }

        return $image;
    }


    protected function _getCombisId(array $p_product)
    {
        $combisId = $this->propertiesControl->extract_combis_id($p_product['id']);

        return $combisId;
    }


    protected function _getShippingTime($p_product, $p_combisId)
    {
        if($this->coo_products === null) {
            $this->coo_products = MainFactory::create_object('GMDataObject',
                [
                    'products',
                    ['products_id' => $p_product['id']]
                ]);
        }


        $shippingTime = $p_product['shipping_time'];

        if ($p_combisId != '') {
            $propertiesControl = MainFactory::create_object('PropertiesControl');
            $productId         = explode('x', $p_product['id'])[0]; // with combis the product id will look like 1x8

            if ($this->coo_products->get_data_value('use_properties_combis_shipping_time') == 1
                && $propertiesControl->combi_exists($productId, $p_combisId)) {
                $shippingTime = $this->propertiesControl->get_properties_combis_shipping_time($p_combisId);
            }
        }
        if (ACTIVATE_SHIPPING_STATUS == "false") {
            $shippingTime = '';
        }

        return $shippingTime;
    }


    protected function _getProductsModel($p_product, $p_combisId)
    {
        $this->_addAttributesModelToProductModel($p_product);

        $productsModel = $p_product['model'];
        if ($p_combisId != '') {
            $productsModel = $this->_getProductModelAddCombi($p_combisId, $productsModel, (int)$p_product['id']);
        }

        return $productsModel;
    }


    protected function _getPropertiesHtml($p_combisId)
    {
        $propertiesHtml = '';
        if ($p_combisId != '') {
            $propertiesHtml = $this->propertiesView->get_order_details_by_combis_id($p_combisId, 'cart');
        }

        return $propertiesHtml;
    }


    protected function _getProductLink($p_product)
    {
        $productLink = xtc_href_link(FILENAME_PRODUCT_INFO,
                                     xtc_product_link($p_product['id'], $p_product['name']) . '&no_boost=1');
        // todo: bedingung: war vorher $products statt $this->products - dürfte nie gelaufen sein
        if (strpos($p_product['id'], '{') !== false) {
            //$productLink .= '?info=' . $p_product['id'];
        }

        return $productLink;
    }


    protected function _getProductsWeight($p_product, $p_combisId)
    {
        $productsWeight = $p_product['weight'];
        if ($p_combisId != '') {
            if ($this->coo_products->get_data_value('use_properties_combis_weight') == 1) {
                $productsWeight = $this->propertiesControl->get_properties_combis_weight($p_combisId);
            }
        }
        if (isset($p_product['attributes'])) {
            foreach ($p_product['attributes'] as $option => $value) {
                if ($p_product[$option]['weight_prefix'] == '+') {
                    $productsWeight += $p_product[$option]['options_values_weight'];
                } else {
                    if ($p_product[$option]['weight_prefix'] == '-') {
                        $productsWeight -= $p_product[$option]['options_values_weight'];
                    }
                }
            }
        }

        $productsWeight = gm_prepare_number($productsWeight,
                                            $this->xtcPrice->currencies[$this->xtcPrice->actualCurr]['decimal_point']);

        $query       = xtc_db_query("SELECT gm_show_weight FROM products WHERE products_id='" . (int)$p_product['id']
                                    . "'");
        $weightArray = xtc_db_fetch_array($query);

        if (empty($weightArray['gm_show_weight'])) {
            $productsWeight = 0;
        }

        return $productsWeight;
    }


    protected function _getPrice()
    {
        $price = $this->total;
        if ($this->customerStatus['customers_status_show_price_tax'] == 0
            && $this->customerStatus['customers_status_add_tax_ot'] == 1) {
            $price = $this->total - $this->cart->show_tax(false);
        }

        return $price;
    }


    protected function _getProductModelAddCombi($p_combisId, $p_productsModel, $productId)
    {
        $combisId      = (string)$p_combisId;
        $productsModel = (string)$p_productsModel;

        $propertiesControl = MainFactory::create_object('PropertiesControl');

        if ($propertiesControl->combi_exists($productId, $combisId)) {
            $combiModel = $propertiesControl->get_properties_combis_model($combisId);

            if (APPEND_PROPERTIES_MODEL === 'true') {
                // Artikelnummer (Kombi) an Artikelnummer (Artikel) anhängen
                if ($productsModel !== '' && $combiModel !== '') {
                    $productsModel .= '-' . $combiModel;
                } elseif ($combiModel !== '') {
                    $productsModel = $combiModel;
                }
            } elseif ($combiModel !== '') { // Artikelnummer (Artikel) durch Artikelnummer (Kombi) ersetzen
                $productsModel = $combiModel;
            }
        }

        return $productsModel;
    }


    /**
     * Add attribute option value model numbers to product's model number.
     *
     * @param array $productArray
     */
    protected function _addAttributesModelToProductModel(array &$productArray)
    {
        if (isset($productArray['attributes'])) {
            $modelArray = [];

            foreach ($productArray['attributes'] as $optionId => $valueId) {
                $query  = "SELECT
										attributes_model
									FROM
										products_attributes
									WHERE
										products_id				= '" . (int)$productArray['id'] . "' AND
										options_id				= '" . (int)$optionId . "' AND
										options_values_id		= '" . (int)$valueId . "'
									LIMIT 1";
                $result = xtc_db_query($query);

                if (xtc_db_num_rows($result) === 1) {
                    $row = xtc_db_fetch_array($result);

                    if (trim($row['attributes_model']) !== '') {
                        $modelArray[] = $row['attributes_model'];
                    }
                }
            }

            if ($productArray['model'] !== '' && count($modelArray)) {
                $productArray['model'] .= '-' . implode('-', $modelArray);
            } else {
                $productArray['model'] .= implode('-', $modelArray);
            }
        }
    }


    protected function _getProductAttributes($p_product, $p_combisId, $p_markStock, &$p_moduleContent)
    {
        $attributesExist = ((isset($p_product['attributes'])) ? true : false);

        if ($attributesExist === true) {
            foreach ($p_product['attributes'] as $option => $value) {
                if (ATTRIBUTE_STOCK_CHECK == 'true' && STOCK_CHECK == 'true' && $value != 0) {
                    $attributeStockCheck = xtc_check_stock_attributes($p_product[$option]['products_attributes_id'],
                                                                      $p_product['quantity']);
                    if ($attributeStockCheck) {
                        $this->sessionAnyOutOfStock = 1;
                    }
                } // combine all customizer products for checking stock
                elseif (STOCK_CHECK == 'true' && $value == 0 && $p_markStock == '') {
                    preg_match('/(.*)\{[\d]+\}0$/', $p_product['id'], $matchesArray);

                    if (isset($matchesArray[1])) {
                        $productIdentifier = $matchesArray[1];
                    }

                    $quantities = 0;

                    foreach ($this->productsCopy as $productDataArray) {
                        preg_match('/(.*)\{[\d]+\}0$/', $productDataArray['id'], $matchesArray);

                        if (isset($matchesArray[1]) && $matchesArray[1] == $productIdentifier) {
                            $quantities += $productDataArray['quantity'];
                        }
                    }

                    $markStock = xtc_check_stock($p_product['id'], $quantities);

                    if ($markStock !== '') {
                        $this->sessionAnyOutOfStock       = 1;
                        $p_moduleContent['PRODUCTS_NAME'] .= $markStock;
                    }
                }

                // BOF GM_MOD GX-Customizer:
                $this->_deleteEmptyAttributes($p_moduleContent, $p_product, $value);
            }
        }

        return get_products_vpe_array($p_product['id'], $p_product['price'], [], $p_combisId);
    }


    protected function _setButtonBackUrl()
    {
        $this->buttonBackUrl = xtc_href_link(FILENAME_DEFAULT);
        if (isset($_SESSION['gm_history']) && is_array($_SESSION['gm_history'])) {
            $historyKeys  = array_keys($_SESSION['gm_history']);
            $lastEntryKey = array_pop($historyKeys);
            if (!empty($_SESSION['gm_history'][$lastEntryKey]['CLOSE'])) {
                $this->buttonBackUrl = GM_HTTP_SERVER . $_SESSION['gm_history'][$lastEntryKey]['CLOSE'];
            }
        }
    }


    /*
     * Methods for setting smarty-variables
     */

    protected function _setContentDataTaxData(array $p_taxArray)
    {
        $this->set_content_data('tax_data', $p_taxArray);
    }


    protected function _setContentDataTaxFreeText()
    {
        $this->set_content_data('TAX_FREE_TEXT', GM_TAX_FREE);
    }


    protected function _setContentDataShowShippingWeight($p_show)
    {
        $this->set_content_data('SHOW_SHIPPING_WEIGHT', $p_show);
    }


    protected function _setContentDataShowShippingWeightInfo($p_showShippingWeightInfo)
    {
        $this->set_content_data('SHOW_SHIPPING_WEIGHT_INFO', $p_showShippingWeightInfo);
    }


    protected function _setContentDataShippingWeight($p_shippingWeight)
    {
        $this->set_content_data('SHIPPING_WEIGHT', $p_shippingWeight);
    }


    protected function _setContentDataShippingCostsSelection($p_html)
    {
        $this->set_content_data('cart_shipping_costs_selection', $p_html);
    }


    protected function _setContentDataShippingInfoGabmioultra($p_shippingGambioultra)
    {
        $this->set_content_data('SHIPPING_INFO_GAMBIOULTRA', $p_shippingGambioultra);
    }


    protected function _setContentDataShippingInfoShippingCostsValue($p_shippingCostsValue)
    {
        $this->set_content_data('SHIPPING_INFO_SHIPPING_COSTS_VALUE', $p_shippingCostsValue);
    }


    protected function _setContentDataShippingInfoExcl($p_shippingExcl)
    {
        $this->set_content_data('SHIPPING_INFO_EXCL', $p_shippingExcl);
    }


    protected function _setContentDataShippingInfoShippingLink($p_shippingLink)
    {
        $this->set_content_data('SHIPPING_INFO_SHIPPING_LINK', $p_shippingLink);
    }


    protected function _setContentDataShippingInfoShippingCosts($p_shippingCosts)
    {
        $this->set_content_data('SHIPPING_INFO_SHIPPING_COSTS', $p_shippingCosts);
    }


    protected function _setContentDataDiscountText()
    {
        $this->set_content_data('DISCOUNT_TEXT',
                                round((double)$this->customerStatus['customers_status_ot_discount'], 2) . '% '
                                . SUB_TITLE_OT_DISCOUNT);
    }


    protected function _setContentDataSubTotal()
    {
        $this->set_content_data('SUBTOTAL', $this->xtcPrice->xtcFormat($this->subtotal, true));
    }


    protected function _setContentDataTotal($totalValueText = null)
    {
        if ($totalValueText !== null) {
            $this->set_content_data('TOTAL', $totalValueText);
        } else {
            $this->set_content_data('TOTAL', $this->xtcPrice->xtcFormat($this->total, true));
        }
    }


    protected function _setContentDataLanguage()
    {
        $this->set_content_data('language', $this->language);
    }


    protected function _setContentDataModuleContent()
    {
        $this->set_content_data('module_content', $this->moduleContent);
    }


    protected function _setContentDataDiscountValue()
    {
        $priceSpecial        = 1;
        $calculateCurrencies = false;
        $this->set_content_data('DISCOUNT_VALUE',
                                '-' . xtc_format_price($this->discount, $priceSpecial, $calculateCurrencies));
    }


    protected function _setContentDataGiftCartContentView()
    {
        $viewHtml = $this->giftCartThemeContentView->get_html();
        $this->set_content_data('MODULE_gift_cart', $viewHtml);
    }


    protected function _setContentDataButtonsBackUrl()
    {
        $this->set_content_data('BUTTON_BACK_URL', $this->buttonBackUrl);
    }


    protected function _setContentGiftVoucher()
    {
        if (@constant('ACTIVATE_GIFT_SYSTEM') === 'true') {
            require_once DIR_FS_INC . 'xtc_get_currencies_values.inc.php';

            $this->set_content_data('GIFT_SYSTEM_ACTIVE', true);
            $this->set_content_data('GV_LINK_DONOTUSEBALANCE',
                                    xtc_href_link('shop.php', 'do=Cart/DoNotUseBalance', 'SSL'));
            $this->set_content_data('GV_LINK_USEBALANCE', xtc_href_link('shop.php', 'do=Cart/UseBalance', 'SSL'));

            $db       = StaticGXCoreLoader::getDatabaseQueryBuilder();
            $gvRow    = $db->from('coupon_gv_customer')
                ->select('amount')
                ->where('customer_id',
                        (int)$_SESSION['customer_id'])
                ->get()
                ->row_array();
            $gvAmount = (float)$gvRow['amount'];
            if ($gvAmount > 0) {
                $this->set_content_data('GV_AMOUNT', $this->xtcPrice->xtcFormat($gvAmount, true, 0, true));
                $this->set_content_data('GV_SEND_TO_FRIEND_LINK', xtc_href_link(FILENAME_GV_SEND, '', 'SSL'));
            }

            $this->_setContentCouponInfo();
        }
    }


    protected function _setContentCouponInfo()
    {
        if (empty($_SESSION['cc_id'])) {
            return;
        }

        $text = MainFactory::create('LanguageTextManager', 'ot_coupon');

        $db         = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $couponInfo = $db->from('coupons')
            ->join('coupons_description',
                   'coupons_description.coupon_id = coupons.coupon_id AND coupons_description.language_id = '
                   . $_SESSION['languages_id'],
                   'LEFT')
            ->where('coupons.coupon_id', (int)$_SESSION['cc_id'])
            ->get()
            ->row_array();

        $currency       = xtc_get_currencies_values($_SESSION['currency']);
        $currencyFactor = !empty($currency['value']) ? (float)$currency['value'] : 1;
        if ($couponInfo['coupon_type'] === 'F') {
            $couponAmount                          = $this->xtcPrice->xtcFormat(round((float)$couponInfo['coupon_amount']
                                                                                      * $currencyFactor,
                                                                                      2),
                                                                                true);
            $couponInfo['coupon_amount_formatted'] = $couponAmount;
        } elseif ($couponInfo['coupon_type'] === 'P') {
            $couponInfo['coupon_amount_formatted'] = number_format((float)$couponInfo['coupon_amount'], 2) . '%';
            $couponInfo['coupon_amount_formatted'] .= ' ' . $text->get_text('rebate');
        } elseif ($couponInfo['coupon_type'] === 'S') {
            $couponInfo['coupon_amount_formatted'] = $text->get_text('shipping_cost');
        }

        if ($couponInfo['coupon_minimum_order'] > 0) {
            $couponInfo['minimum_order_formatted'] = $this->xtcPrice->xtcFormat(round((float)$couponInfo['coupon_minimum_order']
                                                                                      * $currencyFactor,
                                                                                      2),
                                                                                true);
        }

        if (!empty($couponInfo['restrict_to_products'])) {
            $productIds   = explode(',', $couponInfo['restrict_to_products']);
            $productIds   = array_map(static function ($e) {
                return (int)$e;
            },
                $productIds);
            $productIds   = array_unique($productIds);
            $productIds   = array_filter($productIds);
            $productNames = [];
            foreach ($productIds as $productId) {
                $productDescription = $db->from('products_description')
                    ->select('products_name')
                    ->where('products_id',
                            (int)$productId)
                    ->where('language_id', (int)$_SESSION['languages_id'])
                    ->get()
                    ->row_array();
                $productNames[]     = $productDescription['products_name'];
            }
            $couponInfo['product_names'] = $productNames;
        }
        if (!empty($couponInfo['restrict_to_categories'])) {
            $categoriesIds   = explode(',', $couponInfo['restrict_to_categories']);
            $categoriesIds   = array_map(static function ($e) {
                return (int)$e;
            },
                $categoriesIds);
            $categoriesIds   = array_unique($categoriesIds);
            $categoriesIds   = array_filter($categoriesIds);
            $categoriesNames = [];
            foreach ($categoriesIds as $categoryId) {
                $categoryDescription = $db->from('categories_description')
                    ->select('categories_name')
                    ->where('categories_id', (int)$categoryId)
                    ->where('language_id', (int)$_SESSION['languages_id'])
                    ->get()
                    ->row_array();
                $categoriesNames[]   = $categoryDescription['categories_name'];
            }
            $couponInfo['categories_names'] = $categoriesNames;
        }
        $this->set_content_data('COUPON_INFO', $couponInfo);
    }


    /**
     * @param $productId
     *
     * @return string
     */
    protected function _fixProductId($productId)
    {
        if ($this->gprintCart !== null) {
            $elementKeys = array_keys($this->gprintCart->v_elements);

            foreach ($elementKeys as $key) {
                $pattern = '/.+(\{[\d]+\})0$/';

                if (preg_replace($pattern, '$1', $productId) === preg_replace($pattern, '$1', $key)) {
                    $productId = $key;
                }
            }
        }

        return $productId;
    }


    public function set_order_item_template()
    {
        $this->set_content_template('cart_order_preview_item.html');
    }


    public function set_order_total_template()
    {
        $this->set_content_template('cart_order_preview_total.html');
    }


    /**
     * @param array $products
     *
     * @param $index
     * @return string
     */
    protected function mainProductImage(array $products, $index): string
    {
        $image = '';

        try {
            $sellingUnit       = $this->getSellingUnitByProductArray($products, $index);
            $sellingUnitImages = $sellingUnit->images();
            $sellingUnitImage  = $sellingUnitImages[0];
            $image             = $sellingUnitImage->thumbNail()->value();
        } catch (Throwable $throwable) {
            unset($throwable);
        }

        return $image;
    }


    /**
     * @param array $products
     *
     * @param $index
     * @return string
     */
    protected function getMainProductImageAltText(array $products, $index) : string
    {
        $altText = '';

        try {
            $sellingUnit       = $this->getSellingUnitByProductArray($products,$index);
            // default value is the product name
            $altText           = $sellingUnit->productInfo()->name()->value();
            $sellingUnitImages = $sellingUnit->images();
            $sellingUnitImage  = $sellingUnitImages[0];
            $altText           = $sellingUnitImage->alternateText()->value();
        } catch (Throwable $throwable) {
            unset($throwable);
        }

        return $altText;
    }


    /**
     * @param array $products
     * @param string $index
     * @return SellingUnit
     */
    protected function getSellingUnitByProductArray(array $products, $index): SellingUnit
    {
        global $xtPrice;
        /**
         * @todo remove once the selling unit has a cache
         */
        if (isset($this->sellingUnits[$index])) {

            return $this->sellingUnits[$index];
        }

        $sellingUnitId    = $this->sellingUnitIdByProductArray($products[$index]);
        $productInstance  = $this->createProduct($sellingUnitId);
        $quantityFloat    = gm_convert_qty($products[$index]['quantity'], true);
        $scope = new ReserveScope();
        for($i = 0; $i < $index; $i++) {
            $scope->addIdentifier($this->sellingUnits[$i]->id(), $products[$i]['quantity']);
        }
        $selectedQuantity = new ScopedQuantity($quantityFloat,'', $scope);

        return $this->sellingUnits[$index] = $this->sellingUnitReadService()->getSellingUnitBy($sellingUnitId, $productInstance, $xtPrice, $selectedQuantity);
    }


    /**
     * @param array $product
     *
     * @return SellingUnitId
     */
    protected function sellingUnitIdByProductArray(array $product): SellingUnitId
    {
        $languageId = new LanguageId($_SESSION['languages_id']);
        $combisId   = $this->_getCombisId($product);
        $productId  = $product['id'];

        if ($combisId === '') {

            return $this->sellingUnitIdFactory()->createFromProductString((string)$product['id'], $languageId);
        }

        return $this->sellingUnitIdFactory()->createFromProductString($productId, $languageId);
    }

    /**
     * @return Application
     */
    protected function application(): Application
    {
        return LegacyDependencyContainer::getInstance();
    }


    /**
     * @return SellingUnitReadServiceInterface
     */
    protected function sellingUnitReadService(): SellingUnitReadServiceInterface
    {
        return $this->application()->get(SellingUnitReadServiceInterface::class);
    }


    /**
     * @return SellingUnitIdFactoryInterface
     */
    protected function sellingUnitIdFactory(): SellingUnitIdFactoryInterface
    {
	    return $this->application()->get(SellingUnitIdFactoryInterface::class);
    }


    /**
     * @param SellingUnitId $sellingUnitId
     *
     * @return product_ORIGIN
     */
    protected function createProduct(SellingUnitId $sellingUnitId): ProductDataInterface
    {
        MainFactory::load_origin_class('Product');

        $product = new Product($sellingUnitId->productId()->value());

        /** @var ProductDataInterface $product */
        return $product;
    }
}
