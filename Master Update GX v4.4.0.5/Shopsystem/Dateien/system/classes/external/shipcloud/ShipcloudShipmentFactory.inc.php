<?php
/* --------------------------------------------------------------
	ShipcloudShipmentFactory.inc.php 2020-08-12
	Gambio GmbH
	http://www.gambio.de
	Copyright (c) 2020 Gambio GmbH
	Released under the GNU General Public License (Version 2)
	[http://www.gnu.org/licenses/gpl-2.0.html]
	--------------------------------------------------------------
*/

class ShipcloudShipmentFactory
{
    /**
     * @var CI_DB_query_builder
     */
    protected $db;

    /**
     * @var GXCoreLoaderSettingsInterface
     */
    private $settings;

    /**
     * @var GXCoreLoaderInterface
     */
    private $loader;

    /**
     * wrapper for text phrases
     * @var ShipcloudText
     */
    protected $shipcloudText;
    /**
     * configuration storage
     * @var ShipcloudConfigurationStorage
     */
    protected $shipcloudConfigurationStorage;
    /**
     * logging
     * @var ShipcloudLogger
     */
    protected $shipcloudLogger;

    /**
     * initializes the factory
     */
    public function __construct()
    {
        $this->shipcloudText = MainFactory::create('ShipcloudText');
        $this->shipcloudConfigurationStorage = MainFactory::create('ShipcloudConfigurationStorage');
        $this->shipcloudLogger = MainFactory::create('ShipcloudLogger');
        $this->settings = MainFactory::create('GXCoreLoaderSettings');
        $this->loader = MainFactory::create('GXCoreLoader', $this->settings);
        $this->db = $this->loader->getDatabaseQueryBuilder();
    }

    /**
     * retrieves a shipment quote via the web service
     * @param KeyValueCollection POST data representing a shipment
     * @return string ready to be displayed (i.e. including currency)
     */
    public function getShipmentQuote(KeyValueCollection $postData)
    {
        $makeShipmentRequest = MainFactory::create('ShipcloudRestRequest', 'POST', '/v1/shipment_quotes');
        $makeShipmentData = $postData->getArray();
        $makeShipmentRequest->setData($makeShipmentData);

        $restService = MainFactory::create('ShipcloudRestService');
        $result = $restService->performRequest($makeShipmentRequest);
        $responseObject = $result->getResponseObject();
        if ($result->getResponseCode() != '200') {
            if (is_array($responseObject->errors)) {
                $errorMessage = implode('; ', $responseObject->errors);
            } else {
                $errorMessage = 'unspecified error';
            }
            throw new Exception($errorMessage);
        }
        $price = number_format((double)$responseObject->shipment_quote->price, 2, ',', '');
        $price .= '&nbsp;EUR';
        return $price;
    }
    
    
    /**
     * Creates a new shipment at Shipcloud for a given order
     *
     * @param int $orders_id
     * @param KeyValueCollection POST data from the label form
     *
     * @return string shipment id
     * @throws Exception
     */
    public function createShipment($orders_id, KeyValueCollection $postData)
    {
        $makeShipmentRequest                       = MainFactory::create('ShipcloudRestRequest',
                                                                         'POST',
                                                                         '/v1/shipments');
        $makeShipmentData                          = $postData->getArray();
        $makeShipmentData['create_shipping_label'] = true;
        $makeShipmentData['reference_number']      = $orders_id;
        if ($makeShipmentData['carrier'] !== 'dhl') {
            unset($makeShipmentData['package']['declared_value']);
        }
        $makeShipmentRequest->setData($makeShipmentData);
        
        $restService    = MainFactory::create('ShipcloudRestService');
        $result         = $restService->performRequest($makeShipmentRequest);
        $responseObject = $result->getResponseObject();
        if ($result->getResponseCode() != '200') {
            if (is_array($responseObject->errors)) {
                $errorMessage = implode('; ', $responseObject->errors);
            } else {
                $errorMessage = 'unspecified error';
            }
            throw new Exception($errorMessage);
        }
    
        $carriersCache      = MainFactory::create('ShipcloudCarriersCache');
        $carrier            = $carriersCache->getCarrier($makeShipmentData['carrier']);
        $parcelServiceName = '';
        if ($carrier !== null) {
            $parcelServiceName = $carrier->display_name;
        }
    
        $parcelServiceId = $this->shipcloudConfigurationStorage->get('parcel_service_id');
        if ($parcelServiceId > 0) {
            $parcelServiceReader      = MainFactory::create('ParcelServiceReader');
            $parcelTrackingCodeWriter = MainFactory::create('ParcelTrackingCodeWriter');
            $parcelTrackingCodeWriter->insertTrackingUrl($orders_id,
                                                         (string)$responseObject->tracking_url,
                                                         $parcelServiceId,
                                                         $parcelServiceReader,
                                                         (string)$responseObject->carrier_tracking_no,
                                                         $parcelServiceName);
        }
        
        $order_status_after_label = $this->shipcloudConfigurationStorage->get('order_status_after_label');
        if ($order_status_after_label >= 0) {
            $orderStatusComment = sprintf(" % s\n % s",
                                          $this->shipcloudText->get_text('shipcloud_label_created'),
                                          (string)$responseObject->tracking_url);
            $notifyCustomer     = (bool)$this->shipcloudConfigurationStorage->get('notify_customer') === true;
            $this->setOrderStatus($orders_id, $order_status_after_label, $orderStatusComment, $notifyCustomer);
        }
        
        return $responseObject->id;
    }
    
    
    /**
     * set order status and (optionally) notify customer by email
     * @param int orders_id
     * @param int orders_status_id
     * @param string $order_status_comment
     * @param boolean $notifyCustomer
     */
    protected function setOrderStatus($orders_id, $order_status_id, $order_status_comment = '', $notifyCustomer = false)
    {
        $this->shipcloudLogger->notice(sprintf('changing orders status of order %s to %s', $orders_id,
            $order_status_id));
        $orderWriteService = StaticGXCoreLoader::getService('OrderWrite');
        $orderWriteService->updateOrderStatus(
            new IdType((int)$orders_id),
            new IntType((int)$order_status_id),
            new StringType($order_status_comment),
            new BoolType($notifyCustomer)
        );
        if ($notifyCustomer === true) {
            $this->shipcloudLogger->notice(sprintf('sending email notification regarding status change of order %s',
                $orders_id));
            $this->notifyCustomer($orders_id, $order_status_id, $order_status_comment);
        }
    }

    /**
     * notify customer of a change in order status
     *
     * This is mostly copypasted from orders.php and MUST be refactored ASAP!
     */
    protected function notifyCustomer($orders_id, $orders_status_id, $order_status_comment)
    {
        require_once DIR_FS_INC . 'xtc_php_mail.inc.php';
        require_once DIR_WS_CLASSES . 'order.php';
        $order = new order((int)$orders_id);
        $lang_query = sprintf('select languages_id from %s where directory = \'%s\'', TABLE_LANGUAGES,
            $order->info['language']);
        $lang_result = xtc_db_query($lang_query);
        while ($lang_row = xtc_db_fetch_array($lang_result)) {
            $lang = empty($lang_row['languages_id']) ? $_SESSION['languages_id'] : $lang_row['languages_id'];
        }
        $orders_status_array = array();
        $orders_status_query = sprintf('select orders_status_id, orders_status_name from %s where language_id = \'%s\'',
            TABLE_ORDERS_STATUS, $lang);
        $orders_status_result = xtc_db_query($orders_status_query);
        while ($orders_status_row = xtc_db_fetch_array($orders_status_result)) {
            $orders_status_array[$orders_status_row['orders_status_id']] = $orders_status_row['orders_status_name'];
        }

        $smarty = new Smarty;
        // assign language to template for caching
        $smarty->assign('language', $_SESSION['language']);
        $smarty->caching = false;
        $smarty->template_dir = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeHtmlPath();
        $smarty->config_dir = DIR_FS_CATALOG . 'lang';
        $smarty->assign('tpl_path', DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeHtmlPath());
        $smarty->assign('logo_path',
            HTTP_SERVER . DIR_WS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeImagePath());
        $smarty->assign('NAME', $order->customer['name']);
        $smarty->assign('GENDER', $order->customer['gender']);
        $smarty->assign('ORDER_NR', $orders_id);
        $smarty->assign('ORDER_LINK',
            xtc_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders_id, 'SSL'));
        $smarty->assign('ORDER_DATE', xtc_date_long($order->info['date_purchased'], (int)$order->info['languages_id']));
        $smarty->assign('ORDER_STATUS', $orders_status_array[$orders_status_id]);
        if (defined('EMAIL_SIGNATURE')) {
            $smarty->assign('EMAIL_SIGNATURE_HTML', nl2br(EMAIL_SIGNATURE));
            $smarty->assign('EMAIL_SIGNATURE_TEXT', EMAIL_SIGNATURE);
        }

        // START Parcel Tracking Code
        /** @var ParcelTrackingCode $coo_parcel_tracking_code_item */
        $coo_parcel_tracking_code_item = MainFactory::create_object('ParcelTrackingCode');
        /** @var ParcelTrackingCodeReader $coo_parcel_tracking_code_reader */
        $coo_parcel_tracking_code_reader = MainFactory::create_object('ParcelTrackingCodeReader');
        $t_parcel_tracking_codes_array = $coo_parcel_tracking_code_reader->getTackingCodeItemsByOrderId($coo_parcel_tracking_code_item,
            $orders_id);
        $smarty->assign('PARCEL_TRACKING_CODES_ARRAY', $t_parcel_tracking_codes_array);
        $smarty->assign('PARCEL_TRACKING_CODES', 'true');
        // END Parcel Tracking Code

        $smarty->assign('NOTIFY_COMMENTS', nl2br($order_status_comment));
        $html_mail = fetch_email_template($smarty, 'change_order_mail', 'html');
        $smarty->assign('NOTIFY_COMMENTS', $order_status_comment);
        $txt_mail = fetch_email_template($smarty, 'change_order_mail', 'txt');

        // need new language text manager to get text in correct language
        $languageManager = MainFactory::create('LanguageTextManager');
        $subject         = $languageManager->get_text('UPDATE_ORDER_EMAIL_SUBJECT_TEXT',
                                                      'configuration',
                                                      (int)$order->info['languages_id']) . ' ' . $orders_id . ', '
                           . xtc_date_long($order->info['date_purchased'],
                                           (int)$order->info['languages_id']) . ', ' . $order->customer['name'];
        
        xtc_php_mail(
            EMAIL_BILLING_ADDRESS,
            EMAIL_BILLING_NAME,
            $order->customer['email_address'],
            $order->customer['name'],
            '',
            EMAIL_BILLING_REPLY_ADDRESS,
            EMAIL_BILLING_REPLY_ADDRESS_NAME,
            '',
            '',
            $subject,
            $html_mail,
            $txt_mail
        );
    }

    /**
     * Retrieves a list of shipments created for a given order
     * @param int
     * @return stdClass
     */
    public function findShipments($orders_id)
    {
        $shipmentsRequest = MainFactory::create('ShipcloudRestRequest', 'GET',
            '/v1/shipments?reference_number=' . (int)$orders_id);
        $restService = MainFactory::create('ShipcloudRestService');
        $result = $restService->performRequest($shipmentsRequest);
        $shipments = $result->getResponseObject();
        return $shipments;
    }

}
