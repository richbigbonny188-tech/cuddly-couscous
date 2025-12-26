<?php
/* --------------------------------------------------------------
   ot_shipping.php 2020-10-08
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------


   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_shipping.php,v 1.15 2003/02/07); www.oscommerce.com 
   (c) 2003	 nextcommerce (ot_shipping.php,v 1.13 2003/08/24); www.nextcommerce.org
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: ot_shipping.php 1002 2005-07-10 16:11:37Z mz $)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

class ot_shipping_ORIGIN
{
    public $code;
    public $title;
    public $description;
    public $enabled;
    public $sort_order;
    public $output;
    
    
    public function __construct()
    {
        $this->code        = 'ot_shipping';
        $this->title       = defined('MODULE_ORDER_TOTAL_SHIPPING_TITLE') ? MODULE_ORDER_TOTAL_SHIPPING_TITLE : '';
        $this->description = defined(
            'MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION'
        ) ? MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION : '';
        $this->enabled     = defined('MODULE_ORDER_TOTAL_SHIPPING_STATUS')
                             && MODULE_ORDER_TOTAL_SHIPPING_STATUS === 'true';
        $this->sort_order  = defined(
            'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER'
        ) ? MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER : '0';
        
        $this->output = [];
    }
    
    
    public function process()
    {
        /** @var \order_ORIGIN $order */
        $order   = $GLOBALS['order'];
        /** @var \xtcPrice_ORIGIN $xtPrice */
        $xtPrice = $GLOBALS['xtPrice'];
        
        if (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING === 'true'
            && $order->info['shipping_class'] !== 'selfpickup_selfpickup') {
            $pass = false;
            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                case 'national':
                    if ((int)$order->delivery['country_id'] === (int)STORE_COUNTRY) {
                        $pass = true;
                    }
                    break;
                case 'international':
                    if ((int)$order->delivery['country_id'] !== (int)STORE_COUNTRY) {
                        $pass = true;
                    }
                    break;
                case 'both':
                    $pass = true;
                    break;
                default:
                    $pass = false;
                    break;
            }
            
            $t_shipping_free_over = (double)MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER;
            if ((int)$_SESSION['customers_status']['customers_status_show_price_tax'] === 0
                && (int)MODULE_ORDER_TOTAL_SHIPPING_TAX_CLASS > 0) {
                $t_shipping_free_over /= (1 + $xtPrice->TAX[MODULE_ORDER_TOTAL_SHIPPING_TAX_CLASS] / 100);
            }
            
            if (($pass === true)
                && (($order->info['total'] - $order->info['shipping_cost']) >= $xtPrice->xtcFormat(
                        $t_shipping_free_over,
                        false,
                        0,
                        true
                    ))) {
                $order->info['shipping_method'] = $this->title;
                $order->info['total']           -= $order->info['shipping_cost'];
                $order->info['shipping_cost']   = 0;
            }
        }
        
        $module = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));
        
        // BOF GM_MOD
        if (!isset($GLOBALS[$module])
            && file_exists(
                DIR_FS_CATALOG . 'includes/modules/shipping/' . basename($module) . '.php'
            )) {
            include_once DIR_FS_CATALOG . 'includes/modules/shipping/' . basename($module) . '.php';
            $GLOBALS[$module] = new $module;
        }
        // EOF GM_MOD
        if (xtc_not_null($order->info['shipping_method']) && $_SESSION['cart']->get_content_type() !== 'virtual') {
            $deliveryCountryId = $order->delivery['country']['id'];
            $deliveryZoneId = $order->delivery['zone_id'];
            
            $calledFromCart = strpos(gm_get_env_info('SCRIPT_NAME'), 'shopping_cart.php') !== false;
            if ($calledFromCart && isset($_SESSION['cart_shipping_country'])) {
                $deliveryCountryId = $_SESSION['cart_shipping_country'];
                $deliveryZoneId = 0;
            }
            
            $shippingTaxRate        = xtc_get_tax_rate($GLOBALS[$module]->tax_class, $deliveryCountryId,
                                                       $deliveryZoneId);
            $shippingTaxDescription = xtc_get_tax_description($GLOBALS[$module]->tax_class, $deliveryCountryId,
                                                              $deliveryZoneId);
            
            if ((int)$_SESSION['customers_status']['customers_status_show_price_tax'] === 1) {
                $shippingCostInclTax = xtc_add_tax($order->info['shipping_cost'], $shippingTaxRate);
                $tax                 = $xtPrice->xtcCalculateCurr($shippingCostInclTax)
                                       - $xtPrice->xtcCalculateCurr($order->info['shipping_cost']);
                
                $order->info['total'] -= round($xtPrice->xtcCalculateCurr($order->info['shipping_cost']), 2);
                $order->info['total'] += $xtPrice->xtcCalculateCurr($shippingCostInclTax);
                
                if ($GLOBALS[$module]->tax_class > 0 || $tax > 0) {
                    $order->info['shipping_cost']                                     = $shippingCostInclTax;
                    $order->info['tax']                                               += $tax;
                    $order->info['tax_groups'][TAX_ADD_TAX . $shippingTaxDescription] += $tax;
                }
            } elseif ((int)$_SESSION['customers_status']['customers_status_show_price_tax'] === 0
                      && (int)$_SESSION['customers_status']['customers_status_add_tax_ot'] === 1) {
                
                $tax = xtc_add_tax($order->info['shipping_cost'], $shippingTaxRate) - $order->info['shipping_cost'];
                $tax = $xtPrice->xtcCalculateCurr($tax);
                
                $order->info['tax']                                              += $tax;
                $order->info['tax_groups'][TAX_NO_TAX . $shippingTaxDescription] += $tax;
            }
            
            $title = $order->info['shipping_method'];
            if ($order->info['shipping_class'] !== 'selfpickup_selfpickup') {
                $isCheckout = strpos(basename(gm_get_env_info('SCRIPT_NAME')), 'checkout') === 0;
                $showShippingModuleTitle = defined('MODULE_ORDER_TOTAL_SHIPPING_SHOW_TITLE') && MODULE_ORDER_TOTAL_SHIPPING_SHOW_TITLE === 'true';
                if (!$isCheckout && isset($_SESSION['customer_country_id'], $_SESSION['customer_zone_id'])) {
                    /** @var \CountryService $countryService */
                    $countryService = StaticGXCoreLoader::getService('Country');
                    $customerCountry = $countryService->getCountryById(new IdType((int)$_SESSION['customer_country_id']));
                    $shippingCostsLang = MainFactory::create('LanguageTextManager', 'cart_shipping_costs');
                    if ($showShippingModuleTitle === true) {
                        $title .= sprintf(
                            ' %s %s',
                            $shippingCostsLang->get_text('to'),
                            (string)$customerCountry->getIso2()
                        );
                    } else {
                        $title = sprintf(
                            '%s %s %s',
                            $shippingCostsLang->get_text('delivery'),
                            $shippingCostsLang->get_text('to'),
                            (string)$customerCountry->getIso2()
                        );
                    }
                }
            }
            
            $this->output[] = [
                'title' => $title . ':',
                'text'  => $xtPrice->xtcFormat($order->info['shipping_cost'], true, 0, true),
                'value' => $xtPrice->xtcFormat($order->info['shipping_cost'], false, 0, true),
            ];
        }
    }
    
    
    public function check()
    {
        if (!isset($this->_check)) {
            $check_query  = xtc_db_query(
                "select `value` from `gx_configurations` where `key` = 'configuration/MODULE_ORDER_TOTAL_SHIPPING_STATUS'"
            );
            $this->_check = xtc_db_num_rows($check_query);
        }
        
        return $this->_check;
    }
    
    
    public function keys()
    {
        return [
            'configuration/MODULE_ORDER_TOTAL_SHIPPING_STATUS',
            'configuration/MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER',
            'configuration/MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING',
            'configuration/MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER',
            'configuration/MODULE_ORDER_TOTAL_SHIPPING_DESTINATION',
            'configuration/MODULE_ORDER_TOTAL_SHIPPING_TAX_CLASS',
            'configuration/MODULE_ORDER_TOTAL_SHIPPING_SHOW_TITLE',
        ];
    }
    
    
    public function install()
    {
        xtc_db_query(
            "insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`, `type`) values ('configuration/MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true','6', '1','switcher')"
        );
        xtc_db_query(
            "insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`) values ('configuration/MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '30','6', '2')"
        );
        xtc_db_query(
            "insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`, `type`) values ('configuration/MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false','6', '3', 'switcher')"
        );
        // Todo: $currencies->format use_function validation
        xtc_db_query(
            "insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`, `type`) values ('configuration/MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '50', '6', '4', 'currencies->format')"
        );
        xtc_db_query(
            "insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`, `type`) values ('configuration/MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national','6', '5', 'shipping-destination')"
        );
        xtc_db_query(
            "insert into `gx_configurations` (`key`, `value`, `legacy_group_id`, `sort_order`, `type`) values ('configuration/MODULE_ORDER_TOTAL_SHIPPING_TAX_CLASS', '0','6', '7', 'tax-class')"
        );
    
        xtc_db_query(
            'INSERT INTO `gx_configurations` ' .
            '(`key`, `value`, `legacy_group_id`, `sort_order`, `type`) VALUES ' .
            "('configuration/MODULE_ORDER_TOTAL_SHIPPING_SHOW_TITLE', 'false', '6', '8', 'switcher')"
        );


    }
    
    
    public function remove()
    {
        xtc_db_query(
            'delete from ' . TABLE_CONFIGURATION . " where `key` in ('" . implode("', '", $this->keys())
            . "')"
        );
    }
}

MainFactory::load_origin_class('ot_shipping');
