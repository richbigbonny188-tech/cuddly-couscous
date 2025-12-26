<?php
/* --------------------------------------------------------------
  PrintOrderThemeContentView.inc.php 2018-11-13
  http://www.gambio.de
  Copyright (c) 2018 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------


  based on:
  (c) 2003	 nextcommerce (print_order.php,v 1.5 2003/08/24); www.nextcommerce.org
  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: print_order.php 1185 2005-08-26 15:16:31Z mz $)

  Released under the GNU General Public License
  --------------------------------------------------------------------------------------- */

// include needed functions
require_once DIR_FS_INC . 'xtc_get_attributes_model.inc.php';

/**
 * Class PrintOrderThemeContentView
 */
class PrintOrderThemeContentView extends ThemeContentView
{
    /**
     * @var \LanguageTextManager
     */
    protected $coo_language_text_manager;
    
    /**
     * @var int
     */
    protected $order_id = 0;
    
    /**
     * @var int
     */
    protected $customer_id = 0;
    
    /**
     * @var string
     */
    protected $language = 'german';
    
    /**
     * @var order
     */
    protected $coo_order;
    
    
    /**
     * PrintOrderThemeContentView constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->set_flat_assigns(true);
        $this->coo_language_text_manager = MainFactory::create_object('LanguageTextManager', [], true);
        $this->set_content_template('checkout_print_order.html');
        $this->set_caching_enabled(false);
    }
    
    
    protected function set_validation_rules()
    {
        // SET VALIDATION RULES
        $this->validation_rules_array['coo_language_text_manager'] = [
            'type'        => 'object',
            'object_type' => 'LanguageTextManager'
        ];
        $this->validation_rules_array['order_id']                  = ['type' => 'int'];
        $this->validation_rules_array['customer_id']               = ['type' => 'int'];
        $this->validation_rules_array['language']                  = ['type' => 'string'];
        $this->validation_rules_array['coo_order']                 = [
            'type'        => 'object',
            'object_type' => 'order'
        ];
    }
    
    
    public function prepare_data()
    {
        $query  = 'SELECT `customers_id` FROM `orders` WHERE `orders_id` = ' . (int)$this->order_id;
        $result = xtc_db_query($query);
        if (xtc_db_num_rows($result) > 0) {
            $row = xtc_db_fetch_array($result);
            if (empty($this->customer_id) || (int)$this->customer_id !== (int)$row['customers_id']) {
                // NO PERMISSION TO VIEW THE ORDER
                $this->content_array['ERROR'] = 'Access denied!';
                
                return;
            }
            
            include_once DIR_WS_CLASSES . 'order.php';
            $this->coo_order = new order($this->order_id);
            
            $this->add_data();
        } else {
            // NO ORDER FOUND
            $this->content_array['ERROR'] = 'Access denied!';
        }
    }
    
    
    protected function add_data()
    {
        $this->add_address_data();
        $this->add_order_data();
        $this->add_logo();
    }
    
    
    protected function add_address_data()
    {
        // ADDRESS DATA
        $this->set_content_data('address_label_customer',
                                xtc_address_format($this->coo_order->customer['format_id'],
                                                   $this->coo_order->customer,
                                                   1,
                                                   '',
                                                   '<br />'));
        $this->set_content_data('address_label_shipping',
                                xtc_address_format($this->coo_order->delivery['format_id'],
                                                   $this->coo_order->delivery,
                                                   1,
                                                   '',
                                                   '<br />'));
        $this->set_content_data('address_label_payment',
                                xtc_address_format($this->coo_order->billing['format_id'],
                                                   $this->coo_order->billing,
                                                   1,
                                                   '',
                                                   '<br />'));
    }
    
    
    protected function add_order_data()
    {
        $this->content_array['oID']         = $this->order_id;
        $this->content_array['csID']        = $this->coo_order->customer['csID'];
        $this->content_array['COMMENT']     = $this->coo_order->info['comments'];
        $this->content_array['DATE']        = xtc_date_long($this->coo_order->info['date_purchased']);
        $this->content_array['order_data']  = $this->coo_order->getOrderData($this->order_id);
        $this->content_array['order_total'] = $this->coo_order->getTotalData($this->order_id)['data'];
        
        if (!empty($this->coo_order->info['payment_method'])
            && $this->coo_order->info['payment_method'] !== 'no_payment') {
            if (file_exists(DIR_FS_INC . 'get_payment_title.inc.php')) {
                include_once DIR_FS_INC . 'get_payment_title.inc.php';
                
                $this->content_array['PAYMENT_METHOD'] = get_payment_title($this->coo_order->info['payment_method']);
            } else {
                $this->coo_language_text_manager->init_from_lang_file('lang/' . $this->language . '/modules/payment/'
                                                                      . $this->coo_order->info['payment_method']
                                                                      . '.php');
                $t_payment_method                      = constant(strtoupper('MODULE_PAYMENT_'
                                                                             . $this->coo_order->info['payment_method']
                                                                             . '_TEXT_TITLE'));
                $this->content_array['PAYMENT_METHOD'] = $t_payment_method;
            }
        }
    }
    
    
    protected function add_logo()
    {
        $logoManager = MainFactory::create_object('GMLogoManager', ['gm_logo_mail']);
        
        if ($logoManager->logo_use === '1') {
            $this->content_array['gm_logo_mail'] = $logoManager->get_logo();
        }
    }
}
