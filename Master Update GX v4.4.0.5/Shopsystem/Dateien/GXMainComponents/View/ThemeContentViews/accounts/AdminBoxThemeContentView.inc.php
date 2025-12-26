<?php
/* --------------------------------------------------------------
   AdminBoxThemeContentView.inc.php 2021-01-06
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------


   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercebased on original files FROM OSCommerce CVS 2.2 2002/08/28 02:14:35 www.oscommerce.com
   (c) 2003	 nextcommerce (admin.php,v 1.12 2003/08/13); www.nextcommerce.org
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: admin.php 1262 2005-09-30 10:00:32Z mz $)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class AdminBoxThemeContentView extends ThemeContentView
{
    protected $contentsArray = [];
    
    
    /**
     * @deprecated
     */
    protected $cPath = '';
    
    /**
     * @deprecated
     */
    protected $product;
    
    /**
     * @deprecated
     */
    protected $contents = '';
    
    /**
     * @deprecated
     */
    protected $deprecatedBoxEntryCustomers = '';
    
    /**
     * @deprecated
     */
    protected $deprecatedBoxEntryProducts = '';
    
    /**
     * @deprecated
     */
    protected $deprecatedBoxEntryReviews = '';
    
    
    public function __construct()
    {
        parent::__construct();
        $this->set_content_template('box_admin.html');
        $this->set_caching_enabled(false);
    }
    
    
    public function prepare_data()
    {
        $this->_getOrdersStatusValidating();
        
        $this->_getOrdersStatus();
        
        $this->_setCountCustomers();
        $this->_setCountProducts();
        $this->_setCountReviews();
        
        $this->_setAdminUrl();
        $this->_setAdminLinkInfo();
        $this->_setOrdersContents();
    }
    
    
    protected function _setOrdersContents()
    {
        $this->set_content_data('CONTENT_BOX_TITLE_STATISTICS', BOX_TITLE_STATISTICS);
        
        $this->set_content_data('CONTENT_BOX_ORDERS_CONTENTS_ARRAY', $this->contentsArray);
    }
    
    
    protected function _setAdminLinkInfo()
    {
        if (StyleEditServiceFactory::service()->isEditing()) {
            $this->set_content_data('ADMIN_LINK_INFO', ADMIN_LINK_INFO_TEXT);
        }
    }
    
    
    protected function _setButtonEditProductUrl()
    {
        if ($this->product->isProduct()) {
            $this->set_content_data('BUTTON_EDIT_PRODUCT_URL',
                                    'admin/categories.php?cPath=' . $GLOBALS['cPath'] . '&pID='
                                    . $GLOBALS['actual_products_id'] . '&action=new_product');
        }
    }
    
    
    protected function _setAdminUrl()
    {
        $gx_version = '';
        include DIR_FS_CATALOG . 'release_info.php';
        $this->set_content_data('BUTTON_ADMIN_URL', 'admin/');
    }
    
    
    protected function _setCountReviews()
    {
        $t_result        = xtc_db_query("SELECT count(*) AS count FROM " . TABLE_REVIEWS);
        $t_reviews_array = xtc_db_fetch_array($t_result);
        
        $boxEntryReviewsCount = $t_reviews_array['count'];
        $boxEntryReviewsUrl   = null;
        if (!StyleEditServiceFactory::service()->isEditing()) {
            $boxEntryReviewsUrl = xtc_href_link_admin('admin/reviews.php', '', 'SSL');
        }
        
        $this->set_content_data('CONTENT_BOX_ENTRY_REVIEWS_COUNT', $boxEntryReviewsCount);
        $this->set_content_data('CONTENT_BOX_ENTRY_REVIEWS_URL', $boxEntryReviewsUrl);
    }
    
    
    protected function _setCountProducts()
    {
        $t_result         = xtc_db_query("SELECT count(*) AS count FROM " . TABLE_PRODUCTS
                                         . " where products_status = '1'");
        $t_products_array = xtc_db_fetch_array($t_result);
        
        $boxEntryReviewsCount = $t_products_array['count'];
        $boxEntryReviewsUrl   = null;
        if (!StyleEditServiceFactory::service()->isEditing()) {
            $boxEntryReviewsUrl = xtc_href_link_admin('admin/categories.php', '', 'SSL');
        }
        
        $this->set_content_data('CONTENT_BOX_ENTRY_PRODUCTS_COUNT', $boxEntryReviewsCount);
        $this->set_content_data('CONTENT_BOX_ENTRY_PRODUCTS_URL', $boxEntryReviewsUrl);
    }
    
    
    protected function _setCountCustomers()
    {
        $t_result          = xtc_db_query("SELECT count(*) AS count FROM " . TABLE_CUSTOMERS);
        $t_customers_array = xtc_db_fetch_array($t_result);
        
        $boxEntryReviewsCount = $t_customers_array['count'];
        $boxEntryReviewsUrl   = null;
        if (!StyleEditServiceFactory::service()->isEditing()) {
            $boxEntryReviewsUrl = xtc_href_link_admin('admin/customers.php', '', 'SSL');
        }
        
        $this->set_content_data('CONTENT_BOX_ENTRY_CUSTOMERS_COUNT', $boxEntryReviewsCount);
        $this->set_content_data('CONTENT_BOX_ENTRY_CUSTOMERS_URL', $boxEntryReviewsUrl);
    }
    
    
    /**
     * @deprecated
     */
    public function setProduct(product $p_coo_product)
    {
        // deprecated
    }
    
    
    /**
     * @deprecated
     */
    public function setCPath($p_cPath)
    {
        // deprecated
    }
    
    
    protected function _getOrdersStatus()
    {
        $this->contentsArray = [];
        
        $t_result = xtc_db_query("SELECT
										orders_status_name,
										orders_status_id,
										count(orders_id) as count
									FROM " . TABLE_ORDERS_STATUS . ", " . TABLE_ORDERS . "
									WHERE orders_status_id = orders.orders_status
									AND language_id = '" . (int)$_SESSION['languages_id'] . "' GROUP BY orders_status_id
									");
        while ($t_orders_status_array = xtc_db_fetch_array($t_result)) {
            $t_url                 = "'" . xtc_href_link_admin('admin/admin.php',
                                                               'do=OrdersOverview&filter[status][]='
                                                               . $t_orders_status_array['orders_status_id'],
                                                               'NONSSL') . "'";
            $this->contentsArray[] = [
                'label'   => $t_orders_status_array['orders_status_name'],
                'text'    => $t_orders_status_array['count'],
                'url'     => $t_url,
                'confirm' => StyleEditServiceFactory::service()->isEditing(),
            ];
        }
    }
    
    
    /**
     * @deprecated
     */
    protected function _getOrdersStatusDeprecated()
    {
        // deprecated
    }
    
    
    protected function _getOrdersStatusValidating()
    {
        $t_orders_status_validating = xtc_db_num_rows(xtc_db_query("SELECT orders_status FROM " . TABLE_ORDERS
                                                                   . " where orders_status ='0'"));
        $t_url                      = "'" . xtc_href_link_admin('admin/admin.php',
                                                                'do=OrdersOverview&filter[status][]=0',
                                                                'NONSSL') . "'";
        $this->contentsArray[]      = [
            'label'   => TEXT_VALIDATING,
            'text'    => $t_orders_status_validating,
            'url'     => $t_url,
            'confirm' => StyleEditServiceFactory::service()->isEditing(),
        ];
    }
    
    
    /**
     * @deprecated
     */
    protected function _getOrdersStatusValidatingDeprecated()
    {
        // deprecated
    }
}
