<?php
/* --------------------------------------------------------------
   CustomerDeleter.inc.php 2020-06-08
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

MainFactory::load_class('CustomerDeleterInterface');

/**
 * Class CustomerDeleter
 *
 * This class is used for deleting customer data.
 *
 * @category   System
 * @package    Customer
 * @implements CustomerDeleterInterface
 */
class CustomerDeleter implements CustomerDeleterInterface
{
    /**
     * Query builder.
     * @var CI_DB_query_builder
     */
    protected $db;
    
    
    /**
     * Constructor of the class CustomerDeleter
     *
     * @param CI_DB_query_builder $dbQueryBuilder Query builder.
     */
    public function __construct(CI_DB_query_builder $dbQueryBuilder)
    {
        $this->db = $dbQueryBuilder;
    }
    
    
    /**
     * Deletes all data of a specific customer.
     *
     * @param CustomerInterface $customer Customer.
     *
     * @return CustomerDeleter Same instance for method chaining.
     */
    public function delete(CustomerInterface $customer)
    {
        $customerId = (int)(string)$customer->getId();
        $this->db->delete('admin_access_users', ['customer_id' => $customerId]);
        $this->db->delete('customers', ['customers_id' => $customerId]);
        $this->db->delete('customers_basket', ['customers_id' => $customerId]);
        $this->db->delete('customers_basket_attributes', ['customers_id' => $customerId]);
        $this->db->delete('customers_info', ['customers_info_id' => $customerId]);
        $this->db->delete('customers_ip', ['customers_id' => $customerId]);
        $this->db->delete('customers_status_history', ['customers_id' => $customerId]);
        $this->db->delete('customers_wishlist', ['customers_id' => $customerId]);
        $this->db->delete('customers_wishlist_attributes', ['customers_id' => $customerId]);
        $this->db->delete('coupon_gv_customer', ['customer_id' => $customerId]);
        $this->db->delete('gm_gprint_cart_elements', ['customers_id' => $customerId]);
        $this->db->delete('gm_gprint_wishlist_elements', ['customers_id' => $customerId]);
        $this->db->delete('products_notifications', ['customers_id' => $customerId]);
        $this->db->delete('whos_online', ['customer_id' => $customerId]);
        
        $this->db->update('coupon_redeem_track', ['customer_id' => 0], ['customer_id' => $customerId]);
        $this->db->update('coupon_gv_queue', ['customer_id' => 0], ['customer_id' => $customerId]);
        $this->db->update('coupon_redeem_track', ['customer_id' => 0], ['customer_id' => $customerId]);
        $this->db->update('gm_gprint_uploads', ['customers_id' => 0], ['customers_id' => $customerId]);
        $this->db->update('newsletter_recipients', ['customers_id' => 0], ['customers_id' => $customerId]);
        $this->db->update('orders', ['customers_id' => 0], ['customers_id' => $customerId]);
        $this->db->update('withdrawals', ['customer_id' => null], ['customer_id' => $customerId]);
        
        return $this;
    }
}
