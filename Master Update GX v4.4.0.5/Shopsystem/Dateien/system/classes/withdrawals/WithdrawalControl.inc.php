<?php
/* --------------------------------------------------------------
  WithdrawalControl.inc.php 2020-09-01
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
 */

use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalFactory;
use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalReadService;
use Gambio\Admin\Modules\Withdrawal\Services\WithdrawalWriteService;

require_once(DIR_FS_INC . 'html_entity_decode_wrapper.inc.php');

class WithdrawalControl extends DataProcessing
{
    protected $withdrawal_id = 0;
    protected $withdrawal_source;
    protected $withdrawal_contentview;
    protected $order_hash;
    protected $action        = '';
    protected $limit         = 20;
    protected $offset        = 0;
    protected $order_id      = 0;
    protected $page          = 1;
    protected $customer_status_id;
    protected $language_id;
    
    /**
     * @var WithdrawalFactory
     */
    protected $factory;
    
    /**
     * @var WithdrawalReadService
     */
    protected $readService;
    
    /**
     * @var WithdrawalWriteService
     */
    protected $writeService;
    
    
    public function __construct()
    {
        $this->withdrawal_contentview = MainFactory::create_object('WithdrawalContentView');
        $this->withdrawal_source      = MainFactory::create_object('WithdrawalSource');
        
        $this->customer_status_id = $_SESSION['customers_status']['customers_status_id'];
        $this->language_id        = $_SESSION['languages_id'];
    
        $this->factory      = LegacyDependencyContainer::getInstance()->get(WithdrawalFactory::class);
        $this->readService  = LegacyDependencyContainer::getInstance()->get(WithdrawalReadService::class);
        $this->writeService = LegacyDependencyContainer::getInstance()->get(WithdrawalWriteService::class);
    }
    
    
    public function proceed()
    {
        if ((gm_get_conf('WITHDRAWAL_WEBFORM_ACTIVE') != '1'
             && $_SESSION['customers_status']['customers_status_id'] !== '0')
            || ($_SESSION['customers_status']['customers_status_id'] !== '0'
                && isset($this->v_data_array['GET']['order_id'])
                && $this->v_data_array['GET']['order_id'] != '')) {
            $this->set_redirect_url(xtc_href_link('index.php', '', 'SSL'));
            
            return true;
        }
        
        if (isset($_SESSION['customers_status']['customers_status_id'])
            && $_SESSION['customers_status']['customers_status_id'] === '0'
            && isset($this->v_data_array['GET']['order_id'])
            && trim($this->v_data_array['GET']['order_id']) != '') {
            $t_query  = 'SELECT
							orders_hash
						FROM
							orders
						WHERE
							orders_id = "' . xtc_db_input($this->v_data_array['GET']['order_id']) . '"';
            $t_result = xtc_db_query($t_query);
            if (xtc_db_num_rows($t_result)) {
                $t_row = xtc_db_fetch_array($t_result);
                if (trim($t_row['orders_hash']) == '') {
                    $t_order_hash   = md5(time() + mt_rand());
                    $sql_data_array = ['orders_hash' => $t_order_hash];
                    
                    xtc_db_perform(TABLE_ORDERS,
                                   $sql_data_array,
                                   'update',
                                   'orders_id = "' . xtc_db_input($this->v_data_array['GET']['order_id']) . '"');
                    
                    $this->set_order_hash($t_order_hash);
                } else {
                    $this->set_order_hash($t_row['orders_hash']);
                }
            }
        } elseif (isset($this->v_data_array['GET']['order']) && trim($this->v_data_array['GET']['order']) != ''
                  && (gm_get_conf('WITHDRAWAL_WEBFORM_ACTIVE') == '1'
                      || $_SESSION['customers_status']['customers_status_id'] === '0')) {
            $this->set_order_hash($this->v_data_array['GET']['order']);
            $this->set_customer_status_id();
        }
        
        if (isset($_SESSION['customers_status']['customers_status_id'])
            && $_SESSION['customers_status']['customers_status_id'] === '0') {
            $this->set_customer_status_id((int)$_SESSION['customers_status']['customers_status_id']);
        }
        
        $t_withdrawal_data_array = [];
        if (isset($this->v_data_array['POST']['withdrawal_data'])) {
            $t_withdrawal_data_array = $this->v_data_array['POST']['withdrawal_data'];
            $this->save_withdrawal($t_withdrawal_data_array);
        }
        
        $t_main_content = $this->get_template('form', $t_withdrawal_data_array);
        
        $this->v_output_buffer = $t_main_content;
    }
    
    
    public function get_template($template = null, array $p_withdrawal_data = null)
    {
        switch ($template) {
            case 'form':
                $this->set_form_data($p_withdrawal_data);
                break;
        }
        
        return $this->withdrawal_contentview->get_html();
    }
    
    
    protected function set_form_data($p_withdrawal_data = null)
    {
        $themeControl = StaticGXCoreLoader::getThemeControl();
        $this->withdrawal_contentview->set_template_dir(DIR_FS_CATALOG . $themeControl->getThemeHtmlPath());
        $this->withdrawal_contentview->set_withdrawal_web_form_template();
        
        if (isset($this->order_hash)) {
            $coo_order = $this->withdrawal_source->get_order_by_hash($this->order_hash);
            
            if (DEFAULT_CUSTOMERS_STATUS_ID_GUEST == $this->customer_status_id) {
                $coo_order->customer = null;
                $coo_order->delivery = null;
                $coo_order->billing  = null;
            }
            
            $this->withdrawal_contentview->set_content_data('order', $coo_order);
        }
        
        $this->withdrawal_contentview->set_content_data('withdrawal_data', $p_withdrawal_data);
        
        $t_get_params = '';
        $t_order_hash = $this->get_order_hash();
        
        if (empty($t_order_hash) == false) {
            $t_get_params = 'order=' . $this->get_order_hash();
        }
        
        $this->withdrawal_contentview->set_content_data('FORM_ACTION_URL',
                                                        xtc_href_link('withdrawal.php', $t_get_params, 'SSL'));
        
        if ((int)STORE_COUNTRY > 0) {
            $t_query  = 'SELECT countries_iso_code_2 FROM countries WHERE countries_id = "'
                        . xtc_db_input(STORE_COUNTRY) . '"';
            $t_result = xtc_db_query($t_query);
            if (xtc_db_num_rows($t_result) == 1) {
                $t_row                     = xtc_db_fetch_array($t_result);
                $coo_language_text_manager = MainFactory::create_object('LanguageTextManager',
                                                                        ['Countries', $this->language_id]);
                define('STORE_COUNTRY_NAME', $coo_language_text_manager->get_text($t_row['countries_iso_code_2']));
            }
        }
    }
    
    
    public function get_withdrawal_content()
    {
        $group_check = '';
        if (GROUP_CHECK == 'true') {
            $group_check = " and group_ids LIKE '%c_" . (int)$this->customer_status_id . "_group%' ";
        }
        
        $shop_content_query = xtc_db_query("SELECT
							 content_file
							 FROM " . TABLE_CONTENT_MANAGER . "
							 WHERE content_group = " . gm_get_conf('GM_WITHDRAWAL_CONTENT_ID') . "
							 " . $group_check . "
							 AND languages_id = '" . (int)$this->language_id . "'");
        $shop_content_data  = xtc_db_fetch_array($shop_content_query);
        
        if ($shop_content_data['content_file'] != '') {
            if ($shop_content_data['content_file']) {
                ob_start();
                if (strpos($shop_content_data['content_file'], '.txt')) {
                    echo '<pre>';
                }
                include(DIR_FS_CATALOG . 'media/content/' . $shop_content_data['content_file']);
                if (strpos($shop_content_data['content_file'], '.txt')) {
                    echo '</pre>';
                }
                $t_content = ob_get_contents();
                ob_end_clean();
                
                if ($shop_content_data['content_file'] == 'janolaw_widerruf.php') {
                    $t_content = str_replace('<br> ', '<br>', $t_content);
                    $t_content = preg_replace('#<div(.*?)> #is', '<div$1>', $t_content);
                }
                $contents[] = $t_content;
            }
        } else {
            $shop_content_query = xtc_db_query("SELECT
								content_text,
								content_heading,
								content_file
								FROM " . TABLE_CONTENT_MANAGER . " as cm
								LEFT JOIN cm_file_flags AS ff USING (file_flag)
								WHERE file_flag_name = 'withdrawal'
								AND content_status = 1
								" . $group_check . "
								AND languages_id='" . (int)$this->language_id . "'");
            
            while ($t_row = xtc_db_fetch_array($shop_content_query)) {
                if ($t_row['content_file']) {
                    ob_start();
                    if (strpos($t_row['content_file'], '.txt')) {
                        echo '<pre>';
                    }
                    include(DIR_FS_CATALOG . 'media/content/' . $t_row['content_file']);
                    if (strpos($t_row['content_file'], '.txt')) {
                        echo '</pre>';
                    }
                    $t_content = ob_get_contents();
                    ob_end_clean();
                    
                    if ($t_row['content_file'] == 'janolaw_widerruf.php') {
                        $t_content = str_replace('<br> ', '<br>', $t_content);
                        $t_content = preg_replace('#<div(.*?)> #is', '<div$1>', $t_content);
                    }
                    $contents[] = $t_content;
                } else {
                    $contents[] = '<b>' . $t_row['content_heading'] . '</b><br /><br />' . $t_row['content_text'];
                }
            }
            
            if (is_array($contents) && count($contents) > 0) {
                $t_content = implode("<br /><br /><br />", $contents);
            }
        }
        
        return $t_content;
    }
    
    
    public function save_withdrawal(array $formData)
    {
        $orderId    = null;
        $customerId = null;
        
        if (isset($this->order_hash)) {
            $coo_order  = $this->withdrawal_source->get_order_by_hash($this->order_hash);
            $orderId    = (int)$coo_order->info['orders_id'];
            $orderId    = ($orderId === 0) ? null : $orderId;
            $customerId = (int)$coo_order->customer['id'];
            $customerId = ($customerId === 0) ? null : $customerId;
        }
        
        $t_error_array = $this->validate_form($formData);
        
        if (is_array($t_error_array) && empty($t_error_array) == false) {
            $this->withdrawal_contentview->set_content_data('errors', $t_error_array);
        } else {
            $this->withdrawal_contentview->set_content_data('success', true);
            
            $orderDetails    = $this->factory->createOrderDetails($orderId,
                                                                  $formData['order_date'],
                                                                  $formData['delivery_date']);
            
            $customerAddress = $this->factory->createCustomerAddress($formData['customer_street_address'],
                                                                     $formData['customer_postcode'],
                                                                     $formData['customer_city'],
                                                                     $formData['customer_country']);
    
            $customerDetails = $this->factory->createCustomerDetails($formData['customer_email'],
                                                                     $customerAddress,
                                                                     $customerId,
                                                                     $formData['customer_gender'] ?? 'd',
                                                                     $formData['customer_firstname'],
                                                                     $formData['customer_lastname']);
    
            $id         = $this->writeService->createWithdrawal($orderDetails,
                                                                $customerDetails,
                                                                trim($formData['withdrawal_date']),
                                                                $formData['withdrawal_content'],
                                                                $this->get_customer_status_id() == 0);
            $withdrawal = $this->readService->getWithdrawalById($id->value());
            
            $this->send_confirmation_mail($withdrawal, $formData['customer_email']);
        }
    }
    
    
    protected function send_confirmation_mail(\Gambio\Admin\Modules\Withdrawal\Model\Withdrawal $withdrawal, $p_email)
    {
        $t_mail_status = false;
        
        $customerName = $withdrawal->customerFirstName() . ' ' . $withdrawal->customerLastName();
        
        $view = MainFactory::create_object('WithdrawalConfirmationContentView');
        $view->set_customer_gender($withdrawal->customerGender());
        $view->set_customer_name($customerName);
        $view->set_customer_street_address($withdrawal->customerStreet());
        $view->set_customer_postcode($withdrawal->customerPostcode());
        $view->set_customer_city($withdrawal->customerCity());
        $view->set_customer_country($withdrawal->customerCountry());
        $view->set_withdrawal_content($withdrawal->content());
        
        if ($withdrawal->date() !== null) {
            $view->set_withdrawal_date($withdrawal->date());
        }
        if ($withdrawal->orderCreationDate() !== null) {
            $view->set_order_date($withdrawal->orderCreationDate());
        }
        if ($withdrawal->orderDeliveryDate() !== null) {
            $view->set_delivery_date($withdrawal->orderDeliveryDate());
        }
        
        $view->setOutputType('html');
        $t_html = $view->get_html();
        
        $view->setOutputType('txt');
        $t_txt = $view->get_html();
        
        $coo_text_mgr = MainFactory::create_object('LanguageTextManager',
                                                   ['withdrawal', $this->language_id],
                                                   false);
        
        if ($withdrawal->orderId() !== null) {
            $t_subject = $coo_text_mgr->get_text('mail_subject');
            $t_subject = sprintf($t_subject, $withdrawal->orderId());
        } else {
            $t_subject = $coo_text_mgr->get_text('mail_subject_guest');
        }
        
        if (SEND_EMAILS == 'true') {
            // $_POST['message'] is a fake input field that should not be filled with any data
            // it is used as spam bot protection, because bots usually fill all form fields with data
            if (empty($this->v_data_array['POST']['message'])) {
                // send mail to admin
                xtc_php_mail(EMAIL_BILLING_ADDRESS,
                             $customerName,
                             EMAIL_BILLING_ADDRESS,
                             STORE_NAME,
                             EMAIL_BILLING_FORWARDING_STRING,
                             $p_email,
                             $customerName,
                             '',
                             '',
                             $t_subject,
                             $t_html,
                             $t_txt);
                
                // send mail to customer
                $t_mail_status = xtc_php_mail(EMAIL_BILLING_ADDRESS,
                                              EMAIL_BILLING_NAME,
                                              $p_email,
                                              $customerName,
                                              '',
                                              EMAIL_BILLING_REPLY_ADDRESS,
                                              EMAIL_BILLING_REPLY_ADDRESS_NAME,
                                              '',
                                              '',
                                              $t_subject,
                                              $t_html,
                                              $t_txt);
            }
        }
        
        return $t_mail_status;
    }
    
    
    protected function validate_form(array $p_withdrawal_data)
    {
        $t_error_array = [];
        
        if ((isset($p_withdrawal_data['customer_email']) && strlen($p_withdrawal_data['customer_email']) < 5)
            || isset($p_withdrawal_data['customer_email']) === false
            || strpos($p_withdrawal_data['customer_email'], '@') === false) {
            $t_error_array['customer_email'] = '__ERROR__';
        }
        
        return $t_error_array;
    }
    
    
    /**
     * @return mixed
     */
    public function get_order_hash()
    {
        return $this->order_hash;
    }
    
    
    /**
     * @param string $p_order_hash (order belonging to hash has to exist)
     */
    public function set_order_hash($p_order_hash)
    {
        if (is_string($p_order_hash) === false) {
            trigger_error('Order is not a string!');
        }
        
        $t_query        = 'SELECT COUNT(*) AS cnt FROM orders WHERE orders_hash = "' . xtc_db_input($p_order_hash)
                          . '"';
        $t_result       = xtc_db_query($t_query);
        $t_result_array = xtc_db_fetch_array($t_result);
        
        if ((int)$t_result_array['cnt'] > 0) {
            $this->order_hash = $p_order_hash;
        }
    }
    
    
    /**
     * @return int customer_status_id
     */
    public function get_customer_status_id()
    {
        return $this->customer_status_id;
    }
    
    
    /**
     * @param int $p_customer_status_id (optional)
     */
    public function set_customer_status_id($p_customer_status_id = null)
    {
        if ($p_customer_status_id === null) {
            if (isset($this->order_hash) === false) {
                $this->customer_status_id = (int)DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
                
                return;
            }
            
            $t_query  = 'SELECT c.customers_status
						FROM
							orders o,
							customers c
						WHERE
							o.orders_hash = "' . xtc_db_input($this->order_hash) . '" AND
							o.customers_id = c.customers_id';
            $t_result = xtc_db_query($t_query);
            if (xtc_db_num_rows($t_result) == 1) {
                $t_result_array = xtc_db_fetch_array($t_result);
                
                $this->customer_status_id = (int)$t_result_array['customers_status'];
                
                return;
            } else {
                $this->customer_status_id = (int)DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
                
                return;
            }
        }
        
        if (is_int($p_customer_status_id) === false) {
            trigger_error('customer status is not an integer!');
        }
        
        $this->customer_status_id = $p_customer_status_id;
    }
    
    
    public function set_language_id($p_language_id)
    {
        if ((int)$p_language_id > 0) {
            $this->language_id = (int)$p_language_id;
        }
    }
}
