<?php
/* --------------------------------------------------------------
   write_customers_status.php 2021-07-15
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

require_once DIR_FS_INC . 'update_customer_b2b_status.inc.php';

$db = StaticGXCoreLoader::getDatabaseQueryBuilder();

// write customers status in session
if (isset($_SESSION['customer_id']))
{
    $customerStatusQuery1 = $db->select('c.customers_status, 
                                                a.address_book_id, 
                                                a.customer_b2b_status, 
                                                a.entry_country_id, 
                                                a.entry_zone_id')
        ->from('customers c')
        ->join('address_book a', 'a.address_book_id = c.customers_default_address_id')
        ->where('c.customers_id', $_SESSION['customer_id'])
        ->get();
    $customersStatusValue1 = $customerStatusQuery1->row_array();

    if($customerStatusQuery1->num_rows() === 1)
    {
        $customersStatusQuery = $db->get_where(
            'customers_status',
            [
                'customers_status_id' => (int)$customersStatusValue1['customers_status'],
                'language_id' => (int)$_SESSION['languages_id'],
            ]
        );
        $customersStatusValue = $customersStatusQuery->row_array();
        
        $_SESSION['customers_status'] = [
            'customers_status_id'                  => $customersStatusValue1['customers_status'],
            'customers_status_name'                => $customersStatusValue['customers_status_name'],
            'customers_status_image'               => (string)$customersStatusValue['customers_status_image'],
            'customers_status_public'              => $customersStatusValue['customers_status_public'],
            'customers_status_min_order'           => (double)$customersStatusValue['customers_status_min_order'],
            'customers_status_max_order'           => (double)$customersStatusValue['customers_status_max_order'],
            'customers_status_discount'            => $customersStatusValue['customers_status_discount'],
            'customers_status_ot_discount_flag'    => $customersStatusValue['customers_status_ot_discount_flag'],
            'customers_status_ot_discount'         => $customersStatusValue['customers_status_ot_discount'],
            'customers_status_graduated_prices'    => $customersStatusValue['customers_status_graduated_prices'],
            'customers_status_show_price'          => $customersStatusValue['customers_status_show_price'],
            'customers_status_show_price_tax'      => $customersStatusValue['customers_status_show_price_tax'],
            'customers_status_add_tax_ot'          => $customersStatusValue['customers_status_add_tax_ot'],
            'customers_status_payment_unallowed'   => $customersStatusValue['customers_status_payment_unallowed'],
            'customers_status_shipping_unallowed'  => $customersStatusValue['customers_status_shipping_unallowed'],
            'customers_status_discount_attributes' => $customersStatusValue['customers_status_discount_attributes'],
            'customers_fsk18_purchasable'          => $customersStatusValue['customers_fsk18_purchasable'],
            'customers_fsk18_display'              => $customersStatusValue['customers_fsk18_display'],
            'customers_status_write_reviews'       => $customersStatusValue['customers_status_write_reviews'],
            'customers_status_read_reviews'        => $customersStatusValue['customers_status_read_reviews'],
        ];
        
        if(!isset($_SESSION['customer_b2b_status']))
        {
            update_customer_b2b_status($customersStatusValue1['customer_b2b_status']);
        }
    
        $customerCountryId = $customersStatusValue1['entry_country_id'];
        $customerZoneId    = $customersStatusValue1['entry_zone_id'];
    
        $selfpickupSelected = isset($_SESSION['shipping']['id'])
                              && $_SESSION['shipping']['id'] === 'selfpickup_selfpickup';
    
        $calledFromCheckout = strpos(gm_get_env_info('SCRIPT_NAME'), 'checkout') !== false;
        
        // For selfpickup the taxation must be calculated based on the country/zone the shop is located in
        if ($selfpickupSelected) {
            $query             = 'SELECT
                                       g.`value` AS entry_country_id,
                                       z.`zone_id` AS entry_zone_id
                                    FROM `gx_configurations` g
                                    INNER JOIN `zones_to_geo_zones` z ON g.`value` = z.`zone_country_id`
                                    WHERE `key` = "configuration/SHIPPING_ORIGIN_COUNTRY" LIMIT 1';
            $result            = xtc_db_query($query);
            $row               = xtc_db_fetch_array($result);
            $customerCountryId = $row['entry_country_id'];
            $customerZoneId    = $row['entry_zone_id'];
        } elseif (!$calledFromCheckout && isset($_SESSION['cart_shipping_country'])) {
            $customerCountryId = $_SESSION['cart_shipping_country'];
            $customerZoneId    = 0;
        } else {
            $cartContentType = isset($_SESSION['cart'])
                               && method_exists($_SESSION['cart'],
                                                'get_content_type') ? $_SESSION['cart']->get_content_type() : null;
        
            // $cartContentType is null in Gambio Admin
            if ($cartContentType !== null) {
                $addressBookId = null;
                $query         = 'SELECT
                                        ab.`entry_country_id`,
                                        ab.`entry_zone_id`
                                    FROM ' . TABLE_ADDRESS_BOOK . ' ab
                                    LEFT JOIN ' . TABLE_ZONES . ' z ON (ab.`entry_zone_id` = z.`zone_id`)
                                    WHERE
                                        ab.`customers_id` = ' . (int)$_SESSION['customer_id'] . ' AND
                                        ab.`address_book_id` = ';
                if ($cartContentType === 'virtual' && !empty($_SESSION['billto'])
                    && $customersStatusValue1['address_book_id'] !== $_SESSION['billto']) {
                    $addressBookId = (int)$_SESSION['billto'];
                    $query         .= $addressBookId;
                } elseif ($cartContentType !== 'virtual' && !empty($_SESSION['sendto'])
                          && $customersStatusValue1['address_book_id'] !== $_SESSION['sendto']) {
                    $addressBookId = (int)$_SESSION['sendto'];
                    $query         .= $addressBookId;
                }
            
                if ($addressBookId !== null) {
                    $result            = xtc_db_query($query);
                    $row               = xtc_db_fetch_array($result);
                    $customerCountryId = $row['entry_country_id'];
                    $customerZoneId    = $row['entry_zone_id'];
                }
    
                if ($calledFromCheckout) {
                    unset($_SESSION['cart_shipping_country']);
                }
            }
        }
    
        $_SESSION['customer_country_id'] = $customerCountryId;
        $_SESSION['customer_zone_id']    = $customerZoneId;
    }
    else // (int)xtc_db_num_rows($customers_status_query_1) !== 1
    {
        if (!StyleEditServiceFactory::service()->isEditing())
        {
            xtc_session_destroy();
        }
    
        unset(
            $_SESSION['cart_shipping_country'],
            $_SESSION['customer_id'],
            $_SESSION['customer_default_address_id'],
            $_SESSION['customer_first_name'],
            $_SESSION['customer_country_id'],
            $_SESSION['customer_zone_id'],
            $_SESSION['comments'],
            $_SESSION['user_info'],
            $_SESSION['customers_status'],
            $_SESSION['selected_box'],
            $_SESSION['shipping'],
            $_SESSION['payment'],
            $_SESSION['ccard'],
            $_SESSION['gv_id'],
            $_SESSION['cc_id']
        );
        $_SESSION['cart']->reset();
        
        $customersStatusQuery = $db->get_where(
            'customers_status',
            [
                'customers_status_id' => DEFAULT_CUSTOMERS_STATUS_ID_GUEST,
                'language_id' => (int)$_SESSION['languages_id'],
            ]
        );
        $customersStatusValue = $customersStatusQuery->row_array();
    
        $_SESSION['customers_status'] = [
            'customers_status_id'                  => DEFAULT_CUSTOMERS_STATUS_ID_GUEST,
            'customers_status_name'                => $customersStatusValue['customers_status_name'],
            'customers_status_image'               => $customersStatusValue['customers_status_image'],
            'customers_status_discount'            => $customersStatusValue['customers_status_discount'],
            'customers_status_public'              => $customersStatusValue['customers_status_public'],
            'customers_status_min_order'           => (double)$customersStatusValue['customers_status_min_order'],
            'customers_status_max_order'           => (double)$customersStatusValue['customers_status_max_order'],
            'customers_status_ot_discount_flag'    => $customersStatusValue['customers_status_ot_discount_flag'],
            'customers_status_ot_discount'         => $customersStatusValue['customers_status_ot_discount'],
            'customers_status_graduated_prices'    => $customersStatusValue['customers_status_graduated_prices'],
            'customers_status_show_price'          => $customersStatusValue['customers_status_show_price'],
            'customers_status_show_price_tax'      => $customersStatusValue['customers_status_show_price_tax'],
            'customers_status_add_tax_ot'          => $customersStatusValue['customers_status_add_tax_ot'],
            'customers_status_payment_unallowed'   => $customersStatusValue['customers_status_payment_unallowed'],
            'customers_status_shipping_unallowed'  => $customersStatusValue['customers_status_shipping_unallowed'],
            'customers_status_discount_attributes' => $customersStatusValue['customers_status_discount_attributes'],
            'customers_fsk18_purchasable'          => $customersStatusValue['customers_fsk18_purchasable'],
            'customers_fsk18_display'              => $customersStatusValue['customers_fsk18_display'],
            'customers_status_write_reviews'       => $customersStatusValue['customers_status_write_reviews'],
            'customers_status_read_reviews'        => $customersStatusValue['customers_status_read_reviews'],
        ];
        
        update_customer_b2b_status('0');
    
        if (!isset ($_SESSION['customer_country_id'])) {
            $_SESSION['customer_country_id'] = STORE_COUNTRY;
            $_SESSION['customer_zone_id']    = STORE_ZONE;
        }
    }
}
else // isset($_SESSION['customer_id']) === false
{
    $customersStatusQuery = $db->get_where(
        'customers_status',
        [
            'customers_status_id' => DEFAULT_CUSTOMERS_STATUS_ID_GUEST,
            'language_id' => (int)$_SESSION['languages_id'],
        ]
    );
    $customersStatusValue = $customersStatusQuery->row_array();
    
    $_SESSION['customers_status'] = [
        'customers_status_id'                  => DEFAULT_CUSTOMERS_STATUS_ID_GUEST,
        'customers_status_name'                => $customersStatusValue['customers_status_name'],
        'customers_status_image'               => $customersStatusValue['customers_status_image'],
        'customers_status_discount'            => $customersStatusValue['customers_status_discount'],
        'customers_status_public'              => $customersStatusValue['customers_status_public'],
        'customers_status_min_order'           => (double)$customersStatusValue['customers_status_min_order'],
        'customers_status_max_order'           => (double)$customersStatusValue['customers_status_max_order'],
        'customers_status_ot_discount_flag'    => $customersStatusValue['customers_status_ot_discount_flag'],
        'customers_status_ot_discount'         => $customersStatusValue['customers_status_ot_discount'],
        'customers_status_graduated_prices'    => $customersStatusValue['customers_status_graduated_prices'],
        'customers_status_show_price'          => $customersStatusValue['customers_status_show_price'],
        'customers_status_show_price_tax'      => $customersStatusValue['customers_status_show_price_tax'],
        'customers_status_add_tax_ot'          => $customersStatusValue['customers_status_add_tax_ot'],
        'customers_status_payment_unallowed'   => $customersStatusValue['customers_status_payment_unallowed'],
        'customers_status_shipping_unallowed'  => $customersStatusValue['customers_status_shipping_unallowed'],
        'customers_status_discount_attributes' => $customersStatusValue['customers_status_discount_attributes'],
        'customers_fsk18_purchasable'          => $customersStatusValue['customers_fsk18_purchasable'],
        'customers_fsk18_display'              => $customersStatusValue['customers_fsk18_display'],
        'customers_status_write_reviews'       => $customersStatusValue['customers_status_write_reviews'],
        'customers_status_read_reviews'        => $customersStatusValue['customers_status_read_reviews'],
    ];
    
    update_customer_b2b_status('0');
    
    $selfpickupSelected = isset($_SESSION['shipping']['id'])
                          && $_SESSION['shipping']['id'] === 'selfpickup_selfpickup';
    
    // For selfpickup the taxation must be calculated based on the country/zone the shop is located in
    if ($selfpickupSelected) {
        $query             = 'SELECT
                                   g.`value` AS entry_country_id,
                                   z.`zone_id` AS entry_zone_id
                                FROM `gx_configurations` g
                                INNER JOIN `zones_to_geo_zones` z ON g.`value` = z.`zone_country_id`
                                WHERE `key` = "configuration/SHIPPING_ORIGIN_COUNTRY" LIMIT 1';
        $result            = xtc_db_query($query);
        $row               = xtc_db_fetch_array($result);
        $_SESSION['customer_country_id'] = $row['entry_country_id'];
        $_SESSION['customer_zone_id']    = $row['entry_zone_id'];
    }
    
    if (!isset ($_SESSION['customer_country_id'])) {
        $_SESSION['customer_country_id'] = STORE_COUNTRY;
        $_SESSION['customer_zone_id']    = STORE_ZONE;
    }
}

unset($db);
