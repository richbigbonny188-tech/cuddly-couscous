<?php
/* --------------------------------------------------------------
  OrderHistoryBoxThemeContentView.inc.php 2020-10-01
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------


  based on:
  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
  (c) 2002-2003 osCommerce(order_history.php,v 1.4 2003/02/10); www.oscommerce.com
  (c) 2003	 nextcommerce (order_history.php,v 1.9 2003/08/17); www.nextcommerce.org
  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: order_history.php 1262 2005-09-30 10:00:32Z mz $)

  Released under the GNU General Public License
  --------------------------------------------------------------------------------------- */

// include needed functions
require_once(DIR_FS_INC . 'xtc_get_all_get_params.inc.php');

class OrderHistoryBoxThemeContentView extends ThemeContentView
{
    protected $coo_seo_boost;
    protected $customer_id;
    protected $language_id       = 2;
    protected $product_ids_array = [];
    
    /**
     * @var CI_DB_query_builder
     */
    protected $db;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->set_content_template('box_order_history.html');
        $this->set_caching_enabled(false);
        
        $this->db = StaticGXCoreLoader::getDatabaseQueryBuilder();
    }
    
    
    protected function set_validation_rules()
    {
        // SET VALIDATION RULES
        $this->validation_rules_array['coo_seo_boost']     = [
            'type'        => 'object',
            'object_type' => 'GMSEOBoost'
        ];
        $this->validation_rules_array['customer_id']       = ['type' => 'int'];
        $this->validation_rules_array['language_id']       = ['type' => 'int'];
        $this->validation_rules_array['product_ids_array'] = ['type' => 'array'];
    }
    
    
    public function prepare_data()
    {
        $this->coo_seo_boost                         = MainFactory::create_object('GMSEOBoost', [], true);
        $this->content_array['orderHistoryProducts'] = [];
        
        if (isset($this->customer_id)) {
            $this->get_product_ids_array();
            if (empty($this->product_ids_array) == false) {
                $this->add_product_data();
            }
        }
    }
    
    
    protected function get_product_ids_array()
    {
        $result = $this->db->select("op.products_id,o.date_purchased")
            ->from(TABLE_ORDERS . " o")
            ->from(TABLE_ORDERS_PRODUCTS . " op")
            ->from(TABLE_PRODUCTS . " p")
            ->where("o.customers_id = '" . $this->customer_id . "'")
            ->where("o.orders_id = op.orders_id")
            ->where("op.products_id = p.products_id")
            ->where("p.products_status = '1'")
            ->group_by("products_id")
            ->group_by("date_purchased")
            ->order_by("o.date_purchased DESC")
            ->limit(MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX)
            ->distinct(true)
            ->get()
            ->result_array();
        
        foreach ($result as $row) {
            $this->product_ids_array[] = $row['products_id'];
        }
    }
    
    
    protected function add_product_data()
    {
        $result = $this->db->reset_query()
            ->select("products_id, products_name, products_meta_description")
            ->from(TABLE_PRODUCTS_DESCRIPTION)
            ->where_in("products_id", $this->product_ids_array)
            ->where("language_id = '{$this->language_id }'")
            ->order_by("products_name")
            ->get()
            ->result_array();
        
        foreach ($result as $row) {
            if ($this->coo_seo_boost->boost_products) {
                $product_link = xtc_href_link($this->coo_seo_boost->get_boosted_product_url($row['products_id'],
                                                                                            $row['products_name']));
            } else {
                $product_link = xtc_href_link(FILENAME_PRODUCT_INFO,
                                              xtc_product_link($row['products_id'], $row['products_name']));
            }
            $t_title = '';
            
            if ($row['products_meta_description'] !== '') {
                if (strlen_wrapper($row['products_meta_description']) > 80) {
                    $t_title = htmlspecialchars_wrapper(substr_wrapper($row['products_meta_description'], 0, 80));
                } else {
                    $t_title = htmlspecialchars_wrapper($row['products_meta_description']);
                }
            }
            
            $this->content_array['orderHistoryProducts'][] = [
                'url'   => $product_link,
                'title' => $t_title,
                'text'  => $this->truncate($row['products_name'], gm_get_conf('TRUNCATE_PRODUCTS_HISTORY')),
            ];
        }
    }
    
    
    protected function truncate($p_string, $t_limit = 24)
    {
        if (strlen_wrapper($p_string) <= $t_limit) {
            return $p_string;
        } else {
            return substr_wrapper($p_string, 0, $t_limit) . '...';
        }
    }
}
