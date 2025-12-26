<?php
/*--------------------------------------------------------------
 product_info.php 2021-01-22
 Gambio GmbH
 http://www.gambio.de
  Copyright (c) 2021 Gambio GmbH
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
  --------------------------------------------------------------------------------------- */

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\SellingUnit\Unit\Factories\SellingUnitIdFactory;

require_once('includes/application_top.php');

if(isset($_GET['action']) && $_GET['action'] == 'get_download')
{
	xtc_get_download($_GET['cID']);
}

if(isset($_GET['products_id']) && $_GET['products_id'])
{
	$cat = xtc_db_query("SELECT categories_id FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE products_id = '" . (int)$_GET['products_id'] . "'");
	$catData = xtc_db_fetch_array($cat);
	require_once(DIR_FS_INC . 'xtc_get_path.inc.php');
	if($catData['categories_id'])
	{
		$cPath = xtc_input_validation(xtc_get_path($catData['categories_id']), 'cPath', '');
	}
}

// Instantiiate AdditionalFieldControl
$coo_additional_field_control = MainFactory::create_object('AdditionalFieldControl');

// Get all additional fields for the product to pass it to the content view.
$additionalFields = $coo_additional_field_control->get_fields_by_item_id_and_item_type((int)$GLOBALS['product']->pID, 'product');

/* @var ProductInfoContentView $coo_product_info_view */
$coo_product_info_view = MainFactory::create_object('ProductInfoContentView', array($product->data['product_template']));
$coo_product_info_view->setGetArray($_GET);
$coo_product_info_view->setPostArray($_POST);
$coo_product_info_view->setProduct($product);
$coo_product_info_view->setCurrentCategoryId($current_category_id);
$coo_product_info_view->setAdditionalFields($additionalFields);

// new xtcPrice-object needed - do not use global $xtPrice
$productInfoXtcPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);
$productInfoXtcPrice->showFrom_Attributes = false;
$coo_product_info_view->setXtcPrice($productInfoXtcPrice);
$coo_product_info_view->setSeoBoost($GLOBALS['gmSEOBoost']);

$coo_product_info_view->setMain(new main());
$language_id     = new LanguageId((int)$_SESSION['languages_id']);
$coo_product_info_view->setLanguageId($language_id->value());

$t_combi_id      = 0;
$selling_unit_id = null;
$configs = [];
//combination

if (isset($_GET['combi_id']) && (int)$_GET['combi_id'] > 0) {
    $t_combi_id      = (int)$_GET['combi_id'];
//product info, can contain combination and attributes
} elseif (isset($_GET['info'])) {
    preg_match("/p[\d\{\}]+x(\d+)_/", $_GET['info'], $t_extract);
    if (isset($t_extract[1])) {
        $t_combi_id = (int)$t_extract[1];
    }
//productId can contain combination and attributes
} elseif (isset($_GET['products_id'])) {
    preg_match("/[\d\{\}]+x(\d+)/", $_GET['products_id'], $t_extract);
    if (isset($t_extract[1])) {
        $t_combi_id = (int)$t_extract[1];
    }
}

if ($t_combi_id) {
    $configs['combi'] = $t_combi_id;
}
if (isset($_GET['info'])) {
    $configs['info'] = $_GET['info'];
}

if (!empty($_GET['products_id'])) {
    $configs['product'] = $_GET['products_id'];
}

if (count($configs) && isset($GLOBALS['product']) && $GLOBALS['product']->isProduct) {
    $selling_unit_id = SellingUnitIdFactory::instance()->createFromCustom(array_keys($configs),
                                                                          array_values($configs),
                                                                          $language_id);

    $coo_product_info_view->set_selling_unit_id($selling_unit_id);
}

$coo_product_info_view->setCombiId($t_combi_id);
$coo_product_info_view->setCurrency($_SESSION['currency']);
$coo_product_info_view->setCustomerStatusId($_SESSION['customers_status']['customers_status_id']);
$coo_product_info_view->setLastListingSql($_SESSION['last_listing_sql']);
$coo_product_info_view->setLanguage($_SESSION['language']);
$coo_product_info_view->setCustomerDiscount($_SESSION['customers_status']['customers_status_discount']);
$coo_product_info_view->setShowGraduatedPrices($_SESSION['customers_status']['customers_status_graduated_prices'] == 1);
$coo_product_info_view->setFSK18PurchaseAllowed($_SESSION['customers_status']['customers_fsk18_purchasable'] != 0); // '1' => purchase forbidden
$coo_product_info_view->setFSK18DisplayAllowed($_SESSION['customers_status']['customers_fsk18_display'] == 1); // '1' => display allowed
$coo_product_info_view->setShowPrice($_SESSION['customers_status']['customers_status_show_price'] == 1);
$coo_product_info_view->setStockAllowCheckout(STOCK_ALLOW_CHECKOUT === 'true');
$coo_product_info_view->setStockCheck(STOCK_CHECK === 'true');
$coo_product_info_view->setAttributeStockCheck(ATTRIBUTE_STOCK_CHECK === 'true');
$coo_product_info_view->setAppendPropertiesModel(APPEND_PROPERTIES_MODEL === 'true');
$coo_product_info_view->setShowPriceTax((int)$_SESSION['customers_status']['customers_status_show_price_tax'] === 1);
$t_main_content = $coo_product_info_view->get_html();

$coo_layout_control = MainFactory::create_object('LayoutContentControl');
$coo_layout_control->set_data('GET', $_GET);
$coo_layout_control->set_data('POST', $_POST);
$coo_layout_control->set_('coo_breadcrumb', $GLOBALS['breadcrumb']);
$coo_layout_control->set_('coo_product', $GLOBALS['product']);
$coo_layout_control->set_('coo_xtc_price', $GLOBALS['xtPrice']);
$coo_layout_control->set_('c_path', $GLOBALS['cPath']);
$coo_layout_control->set_('main_content', $t_main_content);
$coo_layout_control->set_('request_type', $GLOBALS['request_type']);
$coo_layout_control->proceed();

$t_redirect_url = $coo_layout_control->get_redirect_url();
if(empty($t_redirect_url) === false)
{
	xtc_redirect($t_redirect_url);
}
else
{
	echo $coo_layout_control->get_response();
}
