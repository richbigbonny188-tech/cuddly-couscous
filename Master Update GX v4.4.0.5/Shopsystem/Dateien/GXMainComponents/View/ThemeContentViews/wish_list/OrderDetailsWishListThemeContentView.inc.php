<?php
/* --------------------------------------------------------------
  OrderDetailsWishListThemeContentView.inc.php 2021-04-08
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2021 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
 */

// include needed functions
use Gambio\Core\Application\Application;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\SellingUnit\Unit\Factories\Interfaces\SellingUnitIdFactoryInterface;
use Gambio\Shop\SellingUnit\Unit\SellingUnitInterface;
use Gambio\Shop\SellingUnit\Unit\Services\Interfaces\SellingUnitReadServiceInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SelectedQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

require_once(DIR_FS_INC . 'xtc_check_stock.inc.php');
require_once(DIR_FS_INC . 'xtc_get_products_stock.inc.php');
require_once(DIR_FS_INC . 'xtc_remove_non_numeric.inc.php');
require_once(DIR_FS_INC . 'xtc_get_short_description.inc.php');
require_once(DIR_FS_INC . 'xtc_format_price.inc.php');
require_once(DIR_FS_INC . 'xtc_recalculate_price.inc.php');

require_once(DIR_FS_INC . 'get_products_vpe_array.inc.php');
require_once(DIR_FS_CATALOG . 'gm/inc/gm_prepare_number.inc.php');

MainFactory::load_class('OrderDetailsWishListContentViewInterface');

/**
 * Class OrderDetailsWishListThemeContentView
 */
class OrderDetailsWishListThemeContentView extends ThemeContentView implements OrderDetailsWishListContentViewInterface
{
    
    protected $products_array = [];
    
    /** @var PropertiesControl $coo_properties_control */
    protected $coo_properties_control;
    /** @var PropertiesView $coo_properties_view */
    protected $coo_properties_view;
    /** @var GMSEOBoost $gmSEOBoost */
    protected $gmSEOBoost;
    
    
    protected $currency;
    protected $customersStatus;
    protected $customersStatusId;
    
    /**
     * @var SellingUnitInterface[]
     */
    protected $sellingUnits = [];
    
    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->set_content_template('wish_list_order_details.html');
    }
    
    
    public function prepare_data()
    {
        $p_products_array = $this->products_array;
        
        $coo_properties_control = MainFactory::create_object('PropertiesControl');
        $coo_properties_view    = MainFactory::create_object('PropertiesView');
        $gmSEOBoost             = MainFactory::create_object('GMSEOBoost', [], true);
        
        $coo_xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);
        $coo_main    = new main();
        
        $module_content        = [];
        $any_out_of_stock      = '';
        $mark_stock            = '';
        $p_products_array_copy = $p_products_array;
        
        for ($i = 0; $i < count($p_products_array); $i++) {
            $t_combis_id = $coo_properties_control->extract_combis_id($p_products_array[$i]['id']);
            
            // check if combis_id is empty
            if ($t_combis_id == '') {
                // combis_id is empty = article without properties
                if (STOCK_CHECK == 'true') {
                    $mark_stock = xtc_check_stock($p_products_array[$i]['id'], $p_products_array[$i]['quantity']);
                    if ($mark_stock) {
                        $_SESSION['any_out_of_stock'] = 1;
                    }
                }
            }
    
            $sellingUnit          = $this->getSellingUnitByProductArray($p_products_array[$i]);
            $sellingUnitPresenter = $sellingUnit->presenter();
            
            $image = $this->getMainProductImage($p_products_array[$i]);
            
            $gm_products_id = $p_products_array[$i]['id'];
            $gm_products_id = str_replace('{', '_', $gm_products_id);
            $gm_products_id = str_replace('}', '_', $gm_products_id);
            
            $t_gm_tax_shipping_info = ' ';
            
            if ($_SESSION['customers_status']['customers_status_show_price'] != 0
                && ($coo_xtPrice->gm_check_price_status($p_products_array[$i]['id']) == 0
                    || ($coo_xtPrice->gm_check_price_status($p_products_array[$i]['id']) == 2
                        && $p_products_array[$i]['price'] > 0))) {
                $t_gm_tax_rate          = $coo_xtPrice->TAX[$p_products_array[$i]['tax_class_id']];
                $t_gm_tax_shipping_info .= $coo_main->getTaxInfo($t_gm_tax_rate);
                
                if ($coo_xtPrice->gm_check_price_status($p_products_array[$i]['id']) == 0) {
                    $t_gm_tax_shipping_info .= $coo_main->getShippingLink(true, $p_products_array[$i]['id']);
                }
            }
            
            $gm_product_link = xtc_href_link(FILENAME_PRODUCT_INFO,
                                             'products_id=' . $p_products_array[$i]['id'] . '&no_boost=1');

            $weight = 0;
            if ($sellingUnit->weight() && $sellingUnit->weight()->show()) {
                $weight = $sellingUnit->weight()->value();
            }
    
            $gm_query = xtc_db_query("SELECT gm_show_weight FROM products WHERE products_id='"
                                     . $p_products_array[$i]['id'] . "'");
            $gm_array = xtc_db_fetch_array($gm_query);
            
            $this->set_content_data('out_of_stock_mark', STOCK_MARK_PRODUCT_OUT_OF_STOCK);
            
            $module_content[$i] = [
                'PRODUCTS_NAME'                   => $sellingUnit->productInfo()->name()->value(),
                'STOCK_MARK'                      => strip_tags($mark_stock),
                'IS_OUT_OF_STOCK'                 => $mark_stock !== '',
                'PRODUCTS_QTY'                    => xtc_draw_input_field('cart_quantity[]',
                                                                          gm_convert_qty($p_products_array[$i]['quantity'],
                                                                                         false),
                                                                          ' size="2" onblur="gm_qty_is_changed('
                                                                          . $p_products_array[$i]['quantity']
                                                                          . ', this.value, \'' . GM_QTY_CHANGED_MESSAGE
                                                                          . '\')"',
                                                                          'text',
                                                                          true,
                                                                          "gm_cart_data gm_class_input")
                                                     . xtc_draw_hidden_field('products_id[]',
                                                                             $p_products_array[$i]['id'],
                                                                             'class="gm_cart_data"')
                                                     . xtc_draw_hidden_field('old_qty[]',
                                                                             $p_products_array[$i]['quantity']),
                'TAX_SHIPPING_INFO'               => $t_gm_tax_shipping_info,
                'PRODUCTS_OLDQTY_INPUT_NAME'      => 'old_qty[]',
                'PRODUCTS_QTY_INPUT_NAME'         => 'cart_quantity[]',
                'PRODUCTS_CART_DELETE_INPUT_NAME' => 'cart_delete[]',
                'PRODUCTS_QTY_VALUE'              => $sellingUnit->selectedQuantity()->value(),
                'PRODUCTS_ID_INPUT_NAME'          => 'products_id[]',
                'PRODUCTS_ID_EXTENDED'            => $p_products_array[$i]['id'],
                
                'PRODUCTS_MODEL'             => $sellingUnit->model()->value(),
                'SHOW_PRODUCTS_MODEL'        => gm_get_conf('SHOW_PRODUCTS_MODEL_IN_SHOPPING_CART_AND_WISHLIST'),
                'PRODUCTS_SHIPPING_TIME'     => $coo_main->getShippingStatusName($p_products_array[$i]['shipping_time']),
                'PRODUCTS_TAX'               => (double)($p_products_array[$i]['tax'] ?? 0),
                'PRODUCTS_IMAGE'             => $image,
                'IMAGE_ALT'                  => $p_products_array[$i]['name'],
                'PRODUCTS_LINK'              => $sellingUnitPresenter->getProductLink()->value(),
                'PRODUCTS_PRICE'             => $coo_xtPrice->xtcFormat($p_products_array[$i]['price']
                                                                        * $p_products_array[$i]['quantity'],
                                                                        true),
                'PRODUCTS_SINGLE_PRICE'      => $coo_xtPrice->xtcFormat($p_products_array[$i]['price'], true),
                'PRODUCTS_SHORT_DESCRIPTION' => strip_tags(xtc_get_short_description($p_products_array[$i]['id'])),
                'ATTRIBUTES'                 => [],
                'PROPERTIES'                 => '',
                'GM_WEIGHT'                  => $weight,
                'PRODUCTS_ID'                => $gm_products_id,
                'UNIT'                       => $p_products_array[$i]['unit_name']
            ];
            
            #properties
            if ($t_combis_id != '') {
                $module_content[$i]['PROPERTIES'] = $coo_properties_view->get_order_details_by_combis_id($t_combis_id,
                                                                                                         'cart');
                
                $coo_products                   = MainFactory::create_object('GMDataObject',
                                                                             [
                                                                                 'products',
                                                                                 ['products_id' => $p_products_array[$i]['id']]
                                                                             ]);
                $use_properties_combis_quantity = $coo_products->get_data_value('use_properties_combis_quantity');
                
                $properties_mark_stock = '';
                
                if ($use_properties_combis_quantity == 1) {
                    // check article quantity
                    $properties_mark_stock = xtc_check_stock($p_products_array[$i]['id'],
                                                             $p_products_array[$i]['quantity']);
                    if ($properties_mark_stock) {
                        $_SESSION['any_out_of_stock'] = 1;
                    }
                } else {
                    if (($use_properties_combis_quantity == 0 && ATTRIBUTE_STOCK_CHECK == 'true'
                         && STOCK_CHECK == 'true')
                        || $use_properties_combis_quantity == 2) {
                        // check combis quantity
                        $t_properties_stock = $coo_properties_control->get_properties_combis_quantity($t_combis_id);
                        if ($t_properties_stock < $p_products_array[$i]['quantity']) {
                            $_SESSION['any_out_of_stock'] = 1;
                            $properties_mark_stock        = STOCK_MARK_PRODUCT_OUT_OF_STOCK;
                        }
                    }
                }
                
                $module_content[$i]['PRODUCTS_NAME'] = $p_products_array[$i]['name'];
                $module_content[$i]['STOCK_MARK']    = strip_tags($properties_mark_stock);
                
                if ($coo_products->get_data_value('use_properties_combis_shipping_time') == 1) {
                    $module_content[$i]['PRODUCTS_SHIPPING_TIME'] = $coo_properties_control->get_properties_combis_shipping_time($t_combis_id);
                } else {
                    $coo_main                                     = new main();
                    $module_content[$i]['PRODUCTS_SHIPPING_TIME'] = $coo_main->getShippingStatusName($coo_products->get_data_value('products_shippingtime'));
                }
            }
            
            if (!isset($module_content[$i]['tpl_modifiers'])) {
    
                $module_content[$i]['tpl_modifiers'] = $module_content[$i]['PROPERTIES'];
            }
            
            // Product options names
            $attributes_exist = ((isset($p_products_array[$i]['attributes'])) ? 1 : 0);
            
            if ($attributes_exist == 1) {
                reset($p_products_array[$i]['attributes']);
                
                foreach ($p_products_array[$i]['attributes'] as $option => $value) {
                    if (ATTRIBUTE_STOCK_CHECK == 'true' && STOCK_CHECK == 'true' && $value != 0) {
                        $attribute_stock_check = xtc_check_stock_attributes($p_products_array[$i][$option]['products_attributes_id'],
                                                                            $p_products_array[$i]['quantity']);
                        if ($attribute_stock_check) {
                            $_SESSION['any_out_of_stock'] = 1;
                        }
                    } // combine all customizer products for checking stock
                    elseif (STOCK_CHECK == 'true' && $value == 0 && $mark_stock == '') {
                        preg_match('/(.*)\{[\d]+\}0$/', $p_products_array[$i]['id'], $t_matches_array);
                        
                        if (isset($t_matches_array[1])) {
                            $t_product_identifier = $t_matches_array[1];
                        }
                        
                        $t_quantities = 0;
                        
                        foreach ($p_products_array_copy as $t_product_data_array) {
                            preg_match('/(.*)\{[\d]+\}0$/', $t_product_data_array['id'], $t_matches_array);
                            
                            if (isset($t_matches_array[1]) && $t_matches_array[1] == $t_product_identifier) {
                                $t_quantities += $t_product_data_array['quantity'];
                            }
                        }
                        
                        $t_mark_stock = xtc_check_stock($p_products_array[$i]['id'], $t_quantities);
                        
                        if ($t_mark_stock !== '') {
                            $_SESSION['any_out_of_stock']     = 1;
                            $module_content[$i]['STOCK_MARK'] = strip_tags($t_mark_stock);
                        }
                    }
                    
                    $module_content[$i]['ATTRIBUTES'][] = [
                        'ID'         => $p_products_array[$i][$option]['products_attributes_id'],
                        'MODEL'      => $p_products_array[$i][$option]['products_options_model'],
                        'NAME'       => $p_products_array[$i][$option]['products_options_name'],
                        'VALUE_NAME' => $p_products_array[$i][$option]['products_options_values_name']
                                        . strip_tags($attribute_stock_check)
                    ];
                    
                    // BOF GM_MOD GX-Customizer:
                    require(DIR_FS_CATALOG . 'gm/modules/gm_gprint_order_details_wishlist.php');
                }
            }
            
            $module_content[$i]['PRODUCTS_VPE_ARRAY'] = get_products_vpe_array($p_products_array[$i]['id'],
                                                                               $p_products_array[$i]['price'],
                                                                               [],
                                                                               $t_combis_id);
        }
        
        if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == '1'
            && $_SESSION['customers_status']['customers_status_ot_discount'] != '0.00') {
            $discount = xtc_recalculate_price($_SESSION['cart']->show_total(),
                                              $_SESSION['customers_status']['customers_status_ot_discount']);
        }
        
        $this->set_content_data('GM_THUMBNAIL_WIDTH', PRODUCT_IMAGE_THUMBNAIL_WIDTH);
        
        $this->set_content_data('module_content', $module_content);
        
        $t_html_output = $this->build_html();
        
        return $t_html_output;
    }
    
    
    /**
     * Add attribute option value model numbers to product's model number.
     *
     * @deprecated since GX4.1
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
    
    
    /**
     * @return array
     */
    public function getProductsArray()
    {
        return $this->products_array;
    }
    
    
    /**
     * @param array $products_array
     */
    public function setProductsArray(array $products_array)
    {
        $this->products_array = $products_array;
    }
    
    
    /**
     * @param $productId
     *
     * @return string
     */
    protected function _fixProductId($productId)
    {
        if (isset($_SESSION['coo_gprint_wishlist'])) {
            $elementKeys = array_keys($_SESSION['coo_gprint_wishlist']->v_elements);
            
            foreach ($elementKeys as $key) {
                $pattern = '/.+(\{[\d]+\})0$/';
                
                if (preg_replace($pattern, '$1', $productId) === preg_replace($pattern, '$1', $key)) {
                    $productId = $key;
                }
            }
        }
        
        return $productId;
    }
    
    
    public function setOrderItemTemplate()
    {
        $this->set_content_template('cart_order_preview_item.html');
    }
    
    
    /**
     *
     * @param array $product
     *
     * @return string
     */
    protected function getMainProductImage(array $product): string
    {
        $image = '';
        
        try {
    
            $sellingUnit       = $this->getSellingUnitByProductArray($product);
            $sellingUnitImages = $sellingUnit->images();
            
            if (count($sellingUnitImages)) {
                
                $image = $sellingUnitImages[0]->thumbNail()->value();
            }
        } catch (Throwable $throwable) {
            unset($throwable);
        }
        
        return $image;
    }
    
    
    /**
     * @param array $product
     *
     * @return SellingUnitInterface
     */
    protected function getSellingUnitByProductArray(array $product): SellingUnitInterface
    {
        global $xtPrice;
    
        $quantity   = $product['quantity'];
        $productId  = $product['id'];
        $identifier = $quantity . '|' . $productId;
        
        if (!isset($this->sellingUnits[$identifier])) {
    
            $sellingUnitId                   = $this->getSellingUnitIdByProductArray($product);
            $productOrigin                   = $this->createProduct($sellingUnitId);
            $selectedQuantity                = new SelectedQuantity((float)$quantity);
            $this->sellingUnits[$identifier] = $this->sellingUnitReadService()
                ->getSellingUnitBy($sellingUnitId, $productOrigin, $xtPrice, $selectedQuantity);
        }
        
        return $this->sellingUnits[$identifier];
    }
    
    /**
     * @param array $product
     *
     * @return SellingUnitId
     */
    protected function getSellingUnitIdByProductArray(array $product): SellingUnitId
    {
        $productId  = $product['id'];
        $languageId = new LanguageId((int)$_SESSION['languages_id']);
        
        if (is_string($productId)) {
            
            return $this->sellingUnitIdFactory()->createFromProductString($productId, $languageId);
        }
        
        return $this->sellingUnitIdFactory()->createFromCustom('product_id', (string)$productId, $languageId);
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
