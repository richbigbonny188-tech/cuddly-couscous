<?php
/* --------------------------------------------------------------
	ShipcloudController.inc.php 2021-01-07
	Gambio GmbH
	http://www.gambio.de
	Copyright (c) 2017 Gambio GmbH
	Released under the GNU General Public License (Version 2)
	[http://www.gnu.org/licenses/gpl-2.0.html]
	--------------------------------------------------------------
*/

/**
 * Class ShipcloudController
 * @package HttpViewControllers
 */
class ShipcloudController extends AdminHttpViewController
{
    /**
     * Query Builder
     * @var CI_DB_query_builder
     */
    private $db;
    
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
     * logger
     * @var ShipcloudLogger
     */
    protected $shipcloudLogger;
    
    const MAX_DESCRIPTION_LENGTH = 50;
    
    
    public function __construct(
        HttpContextReaderInterface $httpContextReader,
        HttpResponseProcessorInterface $httpResponseProcessor,
        ContentViewInterface $contentView
    ) {
        parent::__construct($httpContextReader, $httpResponseProcessor, $contentView);
        $this->shipcloudText                 = MainFactory::create('ShipcloudText');
        $this->shipcloudConfigurationStorage = MainFactory::create('ShipcloudConfigurationStorage');
        $this->shipcloudLogger               = MainFactory::create('ShipcloudLogger');
    }
    
    
    /**
     * determines if Shipcloud is configured and ready to use
     */
    protected function isConfigured()
    {
        $mode         = $this->shipcloudConfigurationStorage->get('mode');
        $apiKey       = $this->shipcloudConfigurationStorage->get('api-key/' . $mode);
        $isConfigured = !empty($apiKey);
        
        return $isConfigured;
    }
    
    
    /**
     * Override "proceed" method of parent and use it for initialization.
     *
     * This method must call the parent "proceed" in order to work properly.
     *
     * @param HttpContextInterface $httpContext
     */
    public function proceed(HttpContextInterface $httpContext)
    {
        $this->db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        // Set the template directory.
        $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/');
        // Call the parent "proceed" method.
        parent::proceed($httpContext);
    }
    
    
    /**
     * Run the actionDefault method.
     */
    public function actionDefault()
    {
        # return MainFactory::create('HttpControllerResponse', 'not implemented');
        return MainFactory::create('RedirectHttpControllerResponse', GM_HTTP_SERVER . DIR_WS_CATALOG);
    }
    
    
    /**
     * Heuristically splits up a street address into its component street name and house number
     *
     * @param string
     *
     * @return array with keys 'street' and 'house_no'
     */
    protected function splitStreet($street_address)
    {
        $street_address = trim($street_address);
        $splitStreet    = [
            'street'   => $street_address,
            'house_no' => '',
        ];
        $matches        = [];
        if (preg_match('_^(\d.*?)\s(.+)_', $street_address, $matches) === 1) {
            $splitStreet['street']   = $matches[2];
            $splitStreet['house_no'] = $matches[1];
        } else {
            if (preg_match('_(.+?)\s?(\d.*)_', $street_address, $matches) === 1) {
                $splitStreet['street']   = $matches[1];
                $splitStreet['house_no'] = $matches[2];
            }
        }
        
        return $splitStreet;
    }
    
    
    /**
     * retrieves an order's total
     *
     * @param int $orders_id the order's id
     *
     * @return double
     * @todo get this data from OrderService
     *
     */
    protected function getDeclaredValue($orders_id)
    {
        $declared_value = 0;
        $db             = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $db->select('*')->from('orders_total')->where(['orders_id' => $orders_id, 'class' => 'ot_total']);
        foreach ($db->get()->result() as $row) {
            $declared_value = (double)$row->value;
        }
        
        return $declared_value;
    }
    
    
    /**
     * Shows a form for entering data for a label
     *
     * @return HttpControllerResponse
     */
    public function actionCreateLabelForm()
    {
        require_once DIR_FS_ADMIN . 'includes/classes/order.php';
        $orders_id        = (int)$this->_getQueryParameter('orders_id');
        $template_version = (int)$this->_getQueryParameter('template_version');
        $order            = new order($orders_id);
        $order_weight     = $this->_getShippingWeight($orders_id);
        $declared_value   = $this->getDeclaredValue((int)$orders_id);
        $cod_value        = $declared_value;
        if (empty($order->delivery['house_number'])) {
            $splitStreet = $this->splitStreet($order->delivery['street_address']);
        } else {
            $splitStreet = [
                'street'   => $order->delivery['street_address'],
                'house_no' => $order->delivery['house_number'],
            ];
        }
        if ($declared_value < (double)$this->shipcloudConfigurationStorage->get('declared_value/minimum')
            || $declared_value > (double)$this->shipcloudConfigurationStorage->get('declared_value/maximum')) {
            $declared_value = 0;
        }
        $default_package            = $this->shipcloudConfigurationStorage->get('default_package');
        $default_package_data       = $this->shipcloudConfigurationStorage->get_all_tree('packages/' . $default_package
                                                                                         . '/');
        $default_package_dimensions = $default_package_data['packages'][$default_package];
        
        $transportConditions = $this->getTransportConditions($orders_id);
        $notification_phone  = '';
        $notification_email  = '';
        if ($transportConditions !== 'declined') {
            if ((bool)$this->shipcloudConfigurationStorage->get('prefill_phone') === true) {
                $notification_phone = $order->customer['telephone'];
            }
            if ((bool)$this->shipcloudConfigurationStorage->get('prefill_email') === true) {
                $notification_email = $order->customer['email_address'];
            }
        }
        
        $description = '';
        if ((bool)$this->shipcloudConfigurationStorage->get('prefill_description') === true) {
            $productNames = array_map([$this, 'makeProductName'], $order->products);
            $description  = implode(', ', $productNames);
            $description  = $this->shortenString($description, self::MAX_DESCRIPTION_LENGTH);
        }
        
        $toState = '';
        if (!empty($order->delivery['state'])) {
            /** @var CountryService $countryService */
            $countryService = StaticGXCoreLoader::getService('Country');
            try {
                $toCountry = $countryService->getCountryByName($order->delivery['country']);
                if ($countryService->countryHasCountryZones($toCountry)) {
                    $toCountryZone = $countryService->getCountryZoneByNameAndCountry($order->delivery['state'], $toCountry);
                    $toState = (string)$toCountryZone->getCode();
                }
            } catch (Exception $e) {
                // pass – country or zone/state can’t be found
            }
        }
        
        $formdata                     = [
            'isConfigured'             => $this->isConfigured() == true ? '1' : '0',
            'orders_id'                => $orders_id,
            'is_cod'                   => $order->info['payment_method'] == 'cod',
            'cod'                      => [
                'amount'   => number_format($cod_value, 2, '.', ''),
                'currency' => $order->info['currency'],
            ],
            'to'                       => [
                'company'    => $order->delivery['company'],
                'first_name' => $order->delivery['firstname'],
                'last_name'  => $order->delivery['lastname'],
                'care_of'    => $order->delivery['additional_address_info'],
                'street'     => $splitStreet['street'],
                'street_no'  => $splitStreet['house_no'],
                'city'       => $order->delivery['city'],
                'zip_code'   => $order->delivery['postcode'],
                'country'    => $order->delivery['country_iso_code_2'],
                'state'      => $toState,
                'phone'      => $notification_phone,
            ],
            'package'                  => [
                'weight'         => $default_package_dimensions['weight'],
                'width'          => $default_package_dimensions['width'],
                'length'         => $default_package_dimensions['length'],
                'height'         => $default_package_dimensions['height'],
                'declared_value' => [
                    'amount'   => number_format($declared_value, 2, '.', ''),
                    'currency' => $order->info['currency'],
                ],
            ],
            'package_templates'        => $this->shipcloudConfigurationStorage->get_all_tree('packages'),
            'preselected_carriers'     => $this->shipcloudConfigurationStorage->get_all_tree('preselected_carriers'),
            'checked_carriers'         => $this->shipcloudConfigurationStorage->get_all_tree('checked_carriers'),
            'default_package_template' => $default_package,
            'carrier'                  => 'dhl',
            'service'                  => 'standard',
            'notification_email'       => $notification_email,
            'order_weight'             => $order_weight,
            'description'              => $description,
            'pickup_earliest'          => '',
            'pickup_latest'            => '',
            'pickup_mindate'           => date('Y/m/d', time()),
            'pickup_maxdate'           => date('Y/m/d', strtotime('+2 weeks')),
        ];
        $carriersCache                = MainFactory::create('ShipcloudCarriersCache');
        $formdata['carriers']         = $carriersCache->getCarriers();
        $formdata['carriers_classes'] = [];
        foreach ($formdata['carriers'] as $carrier) {
            if ((bool)$formdata['preselected_carriers']['preselected_carriers'][$carrier->name] === true) {
                $formdata['carriers_classes'][] = 'carrier_' . $carrier->name;
            }
        }
        $formdata['carriers_classes_imploded'] = implode(' ', $formdata['carriers_classes']);
        
        if ($template_version == 2) {
            $html = $this->_render('shipcloud_form_single_v2.html', $formdata);
        } else {
            $html = $this->_render('shipcloud_form_single.html', $formdata);
        }
        $html = $this->shipcloudText->replaceLanguagePlaceholders($html);
        
        return new HttpControllerResponse($html);
    }
    
    
    protected function makeProductName($product)
    {
        $name                 = $product['name'];
        $attributesProperties = [];
        if (!empty($product['attributes'])) {
            foreach ($product['attributes'] as $attribute) {
                $attributesProperties[] = sprintf('%s: %s',
                                                  $attribute['option'],
                                                  $attribute['value']);
            }
        }
        if (!empty($product['properties'])) {
            foreach ($product['properties'] as $property) {
                $attributesProperties[] = sprintf('%s: %s',
                                                  $property['properties_name'],
                                                  $property['values_name']);
            }
        }
        if (!empty($attributesProperties)) {
            $name .= ' (' . implode(', ', $attributesProperties) . ')';
        }
        
        return $name;
    }
    
    
    protected function getTransportConditions($orderId)
    {
        try {
            $orderId             = new IdType($orderId);
            $orderReadService    = StaticGXCoreLoader::getService('OrderRead');
            $order               = $orderReadService->getOrderById($orderId);
            $transportConditions = $order->getAddonValue(new StringType('transportConditions'));
        } catch (InvalidArgumentException $e) {
            $transportConditions = 'not set';
        }
        
        return $transportConditions;
    }
    
    
    /**
     * Uses POST data from the form returned by actionCreateLabelForm() to populate a KeyValueCollection to be fed to
     * the ShipmentFactory
     *
     * @param array
     * @param boolean $anon_from     used for shipment quote requests
     * @param string  $language_code ISO2 language code used for advance notices (e.g. DPD Predict)
     *
     * @return KeyValueCollection
     */
    protected function _prepareSingleFormDataForShipmentRequest(
        array $postDataArray,
        $anon_from = false,
        $language_code = null
    ) {
        $language_code = $language_code ? : $_SESSION['language_code'];
        $ordersId = (int)$postDataArray['orders_id'];
        unset($postDataArray['package_template'], $postDataArray['orders_id'], $postDataArray['quote_carriers']);
        if (empty($postDataArray['from'])) {
            $postDataArray['from'] = [
                'street'    => $this->shipcloudConfigurationStorage->get('from/street'),
                'street_no' => $this->shipcloudConfigurationStorage->get('from/street_no'),
                'city'      => $this->shipcloudConfigurationStorage->get('from/city'),
                'zip_code'  => $this->shipcloudConfigurationStorage->get('from/zip_code'),
                'country'   => $this->shipcloudConfigurationStorage->get('from/country'),
            ];
            if ($anon_from === false) {
                $postDataArray['from']['company']    = $this->shipcloudConfigurationStorage->get('from/company');
                $postDataArray['from']['first_name'] = $this->shipcloudConfigurationStorage->get('from/first_name');
                $postDataArray['from']['last_name']  = $this->shipcloudConfigurationStorage->get('from/last_name');
                $postDataArray['from']['phone']      = $this->shipcloudConfigurationStorage->get('from/phone');
            }
        }
        if ((double)$postDataArray['package']['declared_value']['amount'] === 0.0) {
            unset($postDataArray['package']['declared_value']);
        }
        if (!empty($postDataArray['cod']) && in_array($postDataArray['carrier'], ['dhl', 'gls', 'ups'], true)) {
            $codData = $postDataArray['cod'];
            unset($postDataArray['cod']);
            $codService = [
                'name'       => 'cash_on_delivery',
                'properties' => [
                    'amount'              => $codData['amount'],
                    'currency'            => $codData['currency'],
                    'reference1' => 'Order ' . $ordersId,
                ],
            ];
            if ($postDataArray['carrier'] === 'dhl') {
                $bankAccountData = [
                    'bank_account_holder' => $this->shipcloudConfigurationStorage->get('cod-account/bank_account_holder'),
                    'bank_name'           => $this->shipcloudConfigurationStorage->get('cod-account/bank_name'),
                    'bank_account_number' => $this->shipcloudConfigurationStorage->get('cod-account/bank_account_number'),
                    'bank_code'           => $this->shipcloudConfigurationStorage->get('cod-account/bank_code'),
                ];
                $codService['properties'] = array_merge($codService['properties'], $bankAccountData);
            }
            $postDataArray['additional_services']   = is_array($postDataArray['additional_services']) ? $postDataArray['additional_services'] : [];
            $postDataArray['additional_services'][] = $codService;
        }
        if (preg_match('/(postfiliale|packstation)/i', $postDataArray['to']['street']) === 1) {
            if ($postDataArray['carrier'] !== 'dhl') {
                throw new Exception($this->shipcloudText->get_text('invalid_carrier_for_packstation'));
            }
            if (!empty($postDataArray['to']['last_name'])) // empty for shipment quote requests
            {
                $parts = [];
                if (preg_match('/(.*)\/(\d+)/', $postDataArray['to']['last_name'], $parts) === 1) {
                    $lastName                         = $parts[1];
                    $postnummer                       = $parts[2];
                    $postDataArray['to']['last_name'] = $lastName;
                    $postDataArray['to']['care_of']   = $postnummer;
                    $postDataArray['to']['street']    = strtoupper($postDataArray['to']['street']);
                } elseif (preg_match('/^postnummer (\d+)$/i', $postDataArray['to']['care_of'], $parts) === 1) {
                    $postDataArray['to']['care_of'] = $parts[1];
                }
                
                if (preg_match('/^(\d+)$/', $postDataArray['to']['care_of']) !== 1) {
                    throw new Exception($this->shipcloudText->get_text('client_number_missing'));
                }
            }
        }
        
        $postDataArray['additional_services'] = is_array($postDataArray['additional_services']) ? $postDataArray['additional_services'] : [];
        
        if (is_array($postDataArray['carrier_specific'])) {
            foreach ($postDataArray['carrier_specific'] as $specificsCarrier => $specificParameters) {
                if ($specificsCarrier === 'gls' && $specificsCarrier === $postDataArray['carrier']
                    && isset($specificParameters['gls_guaranteed24service'])) {
                    $postDataArray['additional_services'][] = ['name' => 'gls_guaranteed24service'];
                }
            }
            unset($postDataArray['carrier_specific']);
        }
        
        if (!empty($postDataArray['to']['phone'])
            && $postDataArray['carrier'] === 'cargo_international'
            && $postDataArray['service'] === 'standard'
            && in_array($postDataArray['package']['type'],
                        ['disposable_pallet', 'euro_pallet', 'cargo_international_large_parcel'])
            && (bool)$this->shipcloudConfigurationStorage->get('additional_services/cargo_intl_advance_notice')
               === true) {
            $postDataArray['additional_services'][] = [
                'name'       => 'advance_notice',
                'properties' => [
                    'phone'    => $postDataArray['to']['phone'],
                    'language' => $language_code,
                ],
            ];
        }
        
        if (!empty($postDataArray['notification_email'])
            && $postDataArray['carrier'] === 'dhl'
            && (bool)$this->shipcloudConfigurationStorage->get('additional_services/dhl_advance_notice') === true) {
            $postDataArray['additional_services'][] = [
                'name'       => 'advance_notice',
                'properties' => [
                    'email'    => $postDataArray['notification_email'],
                    'language' => $language_code,
                ],
            ];
        }
        
        if (!empty($postDataArray['notification_email'])
            && $postDataArray['carrier'] === 'dpd'
            && $postDataArray['service'] === 'standard'
            && (bool)$this->shipcloudConfigurationStorage->get('additional_services/dpd-predict') === true) {
            $postDataArray['additional_services'][] = [
                'name'       => 'advance_notice',
                'properties' => [
                    'email'    => $postDataArray['notification_email'],
                    'language' => $language_code,
                ],
            ];
        }
        
        if (!empty($postDataArray['notification_email'])
            && $postDataArray['carrier'] === 'gls'
            && $postDataArray['service'] === 'standard'
            && (bool)$this->shipcloudConfigurationStorage->get('additional_services/gls-flexdelivery') === true) {
            $postDataArray['additional_services'][] = [
                'name'       => 'advance_notice',
                'properties' => [
                    'email'    => $postDataArray['notification_email'],
                    'language' => $language_code,
                ],
            ];
        }
        
        if (!empty($postDataArray['notification_email'])
            && $postDataArray['carrier'] === 'hermes'
            && (bool)$this->shipcloudConfigurationStorage->get('additional_services/hermes_advance_notice') === true) {
            $postDataArray['additional_services'][] = [
                'name'       => 'advance_notice',
                'properties' => [
                    'email'    => $postDataArray['notification_email'],
                    'language' => $language_code,
                ],
            ];
        }
    
        if ($postDataArray['carrier'] === 'dhl'
            && (bool)$this->shipcloudConfigurationStorage->get('additional_services/dhl_gogreen') === true) {
            $postDataArray['additional_services'][] = [
                'name'       => 'dhl_gogreen',
            ];
        }
    
        $isDomestic = $postDataArray['to']['country'] === $postDataArray['from']['country'];
        if ($postDataArray['carrier'] === 'dhl'
            && !$isDomestic
            && (bool)$this->shipcloudConfigurationStorage->get('additional_services/dhl_premium_international') === true) {
            $postDataArray['additional_services'][] = [
                'name'       => 'premium_international',
            ];
        }
        
        if (!empty($postDataArray['delivery_note'])) {
            $postDataArray['additional_services'][] = [
                'name' => 'delivery_note',
                'properties' => [
                    'message' => $postDataArray['delivery_note']
                ]
            ];
        }
        unset($postDataArray['delivery_note']);
    
        if (empty($postDataArray['additional_services'])) {
            unset($postDataArray['additional_services']);
        }
        if (empty($postDataArray['notification_email'])) {
            unset($postDataArray['notification_email']);
        }
        $postDataArray['to'] = array_filter($postDataArray['to']);
        
        if (isset($postDataArray['description'])) {
            $postDataArray['description'] = $this->shortenString($postDataArray['description'],
                                                                 self::MAX_DESCRIPTION_LENGTH);
        }
        
        if (empty($postDataArray['pickup']['pickup_time']['earliest'])
            || empty($postDataArray['pickup']['pickup_time']['latest'])) {
            unset($postDataArray['pickup']);
        }
        
        if ($postDataArray['service'] === 'returns') {
            $originalSender        = $postDataArray['from'];
            $originalReceiver      = $postDataArray['to'];
            $postDataArray['from'] = $originalReceiver;
            $postDataArray['to']   = $originalSender;
        }
        
        $shipmentData = MainFactory::create('KeyValueCollection', $postDataArray);
        
        return $shipmentData;
    }
    
    
    /**
     * shortens a string to a maximum length, appending “…” if shortening required
     *
     * @param string $subject   string to be shortened
     * @param int    $maxLength maximum length
     *
     * @return string            shortened string
     */
    protected function shortenString($subject, $maxLength)
    {
        if (mb_strlen($subject) > $maxLength) {
            $subject = mb_substr($subject, 0, $maxLength - 1);
            $subject .= '…';
        }
        while (strlen($subject) > $maxLength) {
            $subject = mb_substr($subject, 0, mb_strlen($subject) - 2) . '…';
        }
        
        return $subject;
    }
    
    
    /**
     * Looks up the language for a given order and returns the 2-letter ISO code
     *
     * @param int $orders_id
     *
     * @return string ISO2 language code
     */
    protected function getOrderLanguageCode($orders_id)
    {
        $language_code = 'de';
        $this->db->select('code');
        $this->db->from('languages');
        $this->db->join('orders', 'orders_id = ' . (int)$orders_id . ' AND orders.language = languages.directory');
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $language_code = $row->code;
        }
        
        return $language_code;
    }
    
    
    /**
     * Processes form submit for forms created by actionCreateLabelForm()
     *
     * @return JsonHttpControllerResponse
     */
    public function actionCreateLabelFormSubmit()
    {
        $postDataArray = $this->_getPostDataCollection()->getArray();
        $orders_id     = (int)$postDataArray['orders_id'];
        $this->shipcloudLogger->notice(__FUNCTION__ . "\n" . print_r($postDataArray, true));
        try {
            if ($this->isConfigured() === true) {
                $shipmentFactory = MainFactory::create('ShipcloudShipmentFactory');
                $shipmentData    = $this->_prepareSingleFormDataForShipmentRequest($postDataArray,
                                                                                   false,
                                                                                   $this->getOrderLanguageCode($orders_id));
                $shipmentId      = $shipmentFactory->createShipment($orders_id, $shipmentData);
                $contentArray    = [
                    'orders_id'   => $orders_id,
                    'result'      => 'OK',
                    'shipment_id' => $shipmentId,
                ];
            } else {
                $contentArray = [
                    'orders_id'   => $orders_id,
                    'result'      => 'UNCONFIGURED',
                    'shipment_id' => 'n/a',
                ];
            }
        } catch (Exception $e) {
            $contentArray = [
                'orders_id'     => $orders_id,
                'result'        => 'ERROR',
                'error_message' => $e->getMessage()
            ];
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $contentArray);
    }
    
    
    /**
     * Uses form data (cf. actionCreateLabelForm()) to retrieve a shipment quote
     *
     * @return JsonHttpControllerResponse
     */
    public function actionGetShipmentQuote()
    {
        $postDataArray = $this->_getPostDataCollection()->getArray();
        $orders_id     = (int)$postDataArray['orders_id'];
        unset($postDataArray['orders_id'], $postDataArray['to']['company'], $postDataArray['to']['first_name'], $postDataArray['to']['last_name'], $postDataArray['to']['care_of'], $postDataArray['notification_email'], $postDataArray['package']['declared_value'], $postDataArray['quote_carriers'], $postDataArray['cod'], $postDataArray['description'], $postDataArray['pickup']);
        
        try {
            if ($this->isConfigured() === true) {
                $shipmentFactory = MainFactory::create('ShipcloudShipmentFactory');
                $shipmentData    = $this->_prepareSingleFormDataForShipmentRequest($postDataArray, true);
                $shipmentQuote   = $shipmentFactory->getShipmentQuote($shipmentData);
                $contentArray    = [
                    'orders_id'      => $orders_id,
                    'result'         => 'OK',
                    'shipment_quote' => $shipmentQuote,
                ];
            } else {
                $contentArray = [
                    'orders_id'      => $orders_id,
                    'result'         => 'UNCONFIGURED',
                    'shipment_quote' => '',
                ];
            }
        } catch (Exception $e) {
            $contentArray = [
                'orders_id'     => $orders_id,
                'result'        => 'ERROR',
                'error_message' => $e->getMessage()
            ];
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $contentArray);
    }
    
    
    /**
     * Retrieves shipment quotes (bulk retrieval)
     *
     * @return JsonHttpControllerResponse
     */
    public function actionGetMultiShipmentQuote()
    {
        require_once DIR_FS_ADMIN . 'includes/classes/order.php';
        $carriersCache = MainFactory::create('ShipcloudCarriersCache');
        $postDataArray = $this->_getPostDataCollection()->getArray();
        $orders_ids    = $postDataArray['orders'];
        $contentArray  = [
            'result'          => 'OK',
            'shipment_quotes' => [],
            'quote_total'     => 0,
            'carriers_total'  => [],
        ];
        foreach ($orders_ids as $orders_id) {
            $contentArray['shipment_quotes'][$orders_id] = [
                'orders_id'      => $orders_id,
                'shipment_quote' => '',
            ];
            $order                                       = new order($orders_id);
            if (empty($order->delivery['house_number'])) {
                $splitStreet = $this->splitStreet($order->delivery['street_address']);
            } else {
                $splitStreet = [
                    'street'   => $order->delivery['street_address'],
                    'house_no' => $order->delivery['house_number']
                ];
            }
            foreach ($postDataArray['quote_carriers'] as $carrier) {
                if (!isset($contentArray['carriers_total'][$carrier])) {
                    $contentArray['carriers_total'][$carrier] = 0;
                }
                $carrierName                  = $carriersCache->getCarrier($carrier)->display_name;
                $getShipmentQuoteParams       = [
                    'to'      => [
                        'street'    => $splitStreet['street'],
                        'street_no' => $splitStreet['house_no'],
                        'city'      => $order->delivery['city'],
                        'zip_code'  => $order->delivery['postcode'],
                        'country'   => $order->delivery['country_iso_code_2'],
                    ],
                    'package' => $postDataArray['package'],
                    'carrier' => $carrier,
                    'service' => $postDataArray['service'],
                    'from'    => [
                        'street'    => $this->shipcloudConfigurationStorage->get('from/street'),
                        'street_no' => $this->shipcloudConfigurationStorage->get('from/street_no'),
                        'city'      => $this->shipcloudConfigurationStorage->get('from/city'),
                        'zip_code'  => $this->shipcloudConfigurationStorage->get('from/zip_code'),
                        'country'   => $this->shipcloudConfigurationStorage->get('from/country'),
                    ]
                ];
                $getShipmentQuoteParams['to'] = $this->_enforceLengthLimits($getShipmentQuoteParams['carrier'],
                                                                            $getShipmentQuoteParams['to']);
                try {
                    $shipmentFactory                          = MainFactory::create('ShipcloudShipmentFactory');
                    $shipmentQuote                            = $shipmentFactory->getShipmentQuote(MainFactory::create('KeyValueCollection',
                                                                                                                       $getShipmentQuoteParams));
                    $contentArray['carriers_total'][$carrier] += (double)str_replace(',', '.', $shipmentQuote);
                    $shipment_quote                           = '<div class="sc_quote_line row"><div class="sc_carrier_name col-md-9">'
                                                                . $carrierName . '</div>'
                                                                . '<div class="sc_quote_value col-md-3">'
                                                                . $shipmentQuote . '</div></div>';
                } catch (Exception $e) {
                    $shipment_quote = '<div class="sc_quote_line row" title="' . $e->getMessage()
                                      . '"><div class="sc_carrier_name col-md-9">' . $carrierName . '</div>'
                                      . '<div class="sc_quote_value col-md-3">---</div></div>';
                }
                
                $contentArray['shipment_quotes'][$orders_id]['shipment_quote'] .= $shipment_quote;
            }
        }
        foreach ($contentArray['carriers_total'] as $carrier => $total) {
            $contentArray['carriers_total'][$carrier] = sprintf('%.2f EUR', $total);
        }
        
        return new JsonHttpControllerResponse($contentArray);
    }
    
    
    /**
     * Returns message to be displayed to users if the Shipcloud interface is still unconfigured
     *
     * @return HttpControllerResponse
     */
    public function actionUnconfiguredNote()
    {
        $templateData = [
            'sc_link'     => $this->shipcloudConfigurationStorage->get('boarding_url'),
            'config_link' => xtc_href_link('admin.php', 'do=ShipcloudModuleCenterModule'),
        ];
        $html         = $this->_render('shipcloud_unconfigurednote.html', $templateData);
        $html         = $this->shipcloudText->replaceLanguagePlaceholders($html);
        
        return MainFactory::create('HttpControllerResponse', $html);
    }
    
    
    /**
     * Outputs a list of labels for an order identified by its orders_id via the corresponding GET parameter
     *
     * @return HttpControllerResponse
     */
    public function actionLoadLabelList()
    {
        $orders_id        = (int)$this->_getQueryParameter('orders_id');
        $template_version = (int)$this->_getQueryParameter('template_version');
        try {
            $shipmentFactory         = MainFactory::create('ShipcloudShipmentFactory');
            $shipments               = $shipmentFactory->findShipments($orders_id);
            $pickupEarliestTimestamp = ceil(time() / 3600) * 3600;
            $pickupLatestTimestamp   = $pickupEarliestTimestamp + (2 * 3600);
            $page_token              = is_object($_SESSION['coo_page_token']) ? $_SESSION['coo_page_token']->generate_token() : '';
            $templateData            = [
                'page_token'      => $page_token,
                'orders_id'       => $orders_id,
                'shipments'       => $shipments->shipments,
                'pickup_carriers' => ['dpd', 'fedex', 'hermes', 'ups'],
                'pickup_earliest' => date('Y-m-d H:i', $pickupEarliestTimestamp),
                'pickup_latest'   => date('Y-m-d H:i', $pickupLatestTimestamp),
                'pickup_mindate'  => date('Y/m/d', time()),
                'pickup_maxdate'  => date('Y/m/d', strtotime('+2 weeks')),
            ];
            if ($template_version == 2) {
                $html = $this->_render('shipcloud_labellist_v2.html', $templateData);
            } else {
                $html = $this->_render('shipcloud_labellist.html', $templateData);
            }
            $html = $this->shipcloudText->replaceLanguagePlaceholders($html);
        } catch (Exception $e) {
            $html .= '<p>ERROR: ' . $e->getMessage() . '</p>';
        }
        
        return new HttpControllerResponse($html);
    }
    
    
    /**
     * Deletes a shipment label
     */
    public function actionDeleteShipment()
    {
        $response      = [];
        $postDataArray = $this->_getPostDataCollection()->getArray();
        if (empty($postDataArray['shipment_id'])) {
            $response['result']        = 'ERROR';
            $response['error_message'] = 'no shipment ID given';
        } else {
            $restService     = MainFactory::create('ShipcloudRestService');
            $shipmentId      = $postDataArray['shipment_id'];
            $shipmentRequest = MainFactory::create('ShipcloudRestRequest', 'GET', '/v1/shipments/' . $shipmentId);
            $shipmentResult  = $restService->performRequest($shipmentRequest);
            if ($shipmentResult->getResponseCode() == 200) {
                $shipment          = $shipmentResult->getResponseObject();
                $ordersId          = $shipment->reference_number;
                $carrierTrackingNo = $shipment->carrier_tracking_no;
                $this->shipcloudLogger->notice(sprintf('deleting label with shipment id %s, carrier tracking no %s for order %s',
                                                       $shipmentId,
                                                       $carrierTrackingNo,
                                                       $ordersId));
                $deleteRequest  = MainFactory::create('ShipcloudRestRequest', 'DELETE', '/v1/shipments/' . $shipmentId);
                $result         = $restService->performRequest($deleteRequest);
                $responseObject = $result->getResponseObject();
                if ($result->getResponseCode() != '204') {
                    $response['result']        = 'ERROR';
                    $response['error_message'] = is_array($responseObject->errors) ? implode('; ',
                                                                                             $responseObject->errors) : 'unspecified error';
                } else {
                    $response['result']      = 'OK';
                    $response['text']        = $this->shipcloudText->get_text('shipment_deleted');
                    $response['shipment_id'] = $shipmentId;
                    
                    $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
                    $db->delete('orders_parcel_tracking_codes',
                                ['order_id' => (int)$ordersId, 'tracking_code' => $carrierTrackingNo]);
                }
            } else {
                $response['result']        = 'ERROR';
                $response['error_message'] = 'shipment not found';
            }
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $response);
    }
    
    
    /**
     * Requests pickups for a given list of shipments from each of the carriers involved
     *
     * @return JsonHttpControllerResponse
     */
    
    public function actionPickupShipments()
    {
        $postDataArray      = $this->_getPostDataCollection()->getArray();
        $shipments          = $postDataArray['pickup_shipments'];
        $shipmentsByCarrier = [];
        foreach ($shipments as $pickupShipment) {
            list($shippingId, $carrier) = explode('/', $pickupShipment);
            if (!is_array($shipmentsByCarrier[$carrier])) {
                $shipmentsByCarrier[$carrier] = [$shippingId];
            } else {
                $shipmentsByCarrier[$carrier][] = $shippingId;
            }
        }
        
        $pickupEarliestString = date('c', strtotime($postDataArray['pickup_earliest']));
        $pickupLatestString   = date('c', strtotime($postDataArray['pickup_latest']));
        $this->shipcloudLogger->notice(sprintf("earliest %s\nlatest %s", $pickupEarliestString, $pickupLatestString));
        
        $result_messages = [];
        foreach ($shipmentsByCarrier as $carrier => $carrierShipmentIds) {
            try {
                $pickupRequestData = [
                    'carrier'     => $carrier,
                    'pickup_time' => ['earliest' => $pickupEarliestString, 'latest' => $pickupLatestString],
                    'shipments'   => [],
                ];
                foreach ($carrierShipmentIds as $shipmentId) {
                    $pickupRequestData['shipments'][] = ['id' => $shipmentId];
                }
                $pickupRequest  = MainFactory::create('ShipcloudRestRequest',
                                                      'POST',
                                                      '/v1/pickup_requests',
                                                      $pickupRequestData);
                $restService    = MainFactory::create('ShipcloudRestService');
                $result         = $restService->performRequest($pickupRequest);
                $responseObject = $result->getResponseObject();
                if ($result->getResponseCode() != '200') {
                    foreach ($responseObject->errors as $errorMessage) {
                        $result_messages[] = sprintf("%s: %s\n",
                                                     $this->shipcloudText->get_text('pickup_error'),
                                                     $errorMessage);
                    }
                } else {
                    $result_messages[] = sprintf('%s, %s: %s %s',
                                                 $this->shipcloudText->get_text('pickup_confirmed'),
                                                 $this->shipcloudText->get_text('carrier_pickup_number'),
                                                 $this->shipcloudText->get_text('carrier_' . $responseObject->carrier),
                                                 $responseObject->carrier_pickup_number);
                }
            } catch (Exception $e) {
                $result_messages[] = sprintf('%s %s: %s',
                                             $this->shipcloudText->get_text('error_requesting_pickup'),
                                             $carrier,
                                             $e->getMessage());
            }
        }
        
        $result = [
            'result_messages' => $result_messages,
        ];
        
        return MainFactory::create('JsonHttpControllerResponse', $result);
    }
    
    
    /**
     * Retrieves list of labels for a set of orders listed in POST[orders_ids].
     *
     * @return HttpControllerResponse
     */
    public function actionLoadMultiLabelList()
    {
        $postData         = $this->_getPostDataCollection()->getArray();
        $template_version = (int)$this->_getQueryParameter('template_version');
        $params           = json_decode(stripcslashes($postData['json']));
        $orders_ids       = $params->orders_ids;
        $shipmentResults  = $params->shipments;
        
        $shipmentFactory = MainFactory::create('ShipcloudShipmentFactory');
        $shipments       = [];
        foreach ($shipmentResults as $shipmentResult) {
            if ($shipmentResult->result == 'OK') {
                try {
                    $shipment = $shipmentFactory->findShipments($shipmentResult->orders_id);
                } catch (Exception $e) {
                    $this->shipcloudLogger->debug_notice(sprintf('no shipment found for orders_id %s: %s',
                                                                 $shipmentResult->orders_id,
                                                                 $e->getMessage()));
                }
            } else {
                $shipment            = new stdClass();
                $shipment->orders_id = $shipmentResult->orders_id;
                if ($shipmentResult->result == 'ERROR') {
                    $shipment->error_message = $shipmentResult->error_message;
                } else {
                    $shipment->error_message = sprintf('unsupported result code %s', $shipmentResult->result);
                }
            }
            $shipments[$shipmentResult->orders_id] = $shipment;
        }
        $pickupEarliestTimestamp = ceil(time() / 3600) * 3600;
        $pickupLatestTimestamp   = $pickupEarliestTimestamp + (2 * 3600);
        $templateData            = [
            'shipments'       => $shipments,
            'pickup_carriers' => ['dpd', 'fedex', 'hermes', 'ups'],
            'pickup_earliest' => date('Y-m-d H:i', $pickupEarliestTimestamp),
            'pickup_latest'   => date('Y-m-d H:i', $pickupLatestTimestamp),
            'pickup_mindate'  => date('Y/m/d', time()),
            'pickup_maxdate'  => date('Y/m/d', strtotime('+2 weeks')),
        ];
        if ($template_version == 2) {
            $html = $this->_render('shipcloud_multilabellist_v2.html', $templateData);
        } else {
            $html = $this->_render('shipcloud_multilabellist.html', $templateData);
        }
        $html = $this->shipcloudText->replaceLanguagePlaceholders($html);
        
        return new HttpControllerResponse($html);
    }
    
    
    /**
     * Shows form for bulk label retrieval
     *
     * @return HttpControllerResponse
     */
    public function actionCreateMultiLabelForm()
    {
        if ($this->isConfigured() !== true) {
            return $this->actionUnconfiguredNote();
        } else {
            require DIR_FS_ADMIN . 'includes/classes/order.php';
            $orders_ids          = $this->_getQueryParameter('orders');
            $template_version    = (int)$this->_getQueryParameter('template_version');
            $orders              = [];
            $orders_weights      = [];
            $transportConditions = [];
            $descriptions        = [];
            foreach ($orders_ids as $orders_id) {
                $orders[$orders_id]              = new order($orders_id);
                $orders_weights[$orders_id]      = $this->_getShippingWeight($orders_id);
                $transportConditions[$orders_id] = $this->getTransportConditions($orders_id);
                $description                     = '';
                if ((bool)$this->shipcloudConfigurationStorage->get('prefill_description') === true) {
                    $productNames = array_map([$this, 'makeProductName'], $orders[$orders_id]->products);
                    $description  = $this->shortenString(implode(', ', $productNames), self::MAX_DESCRIPTION_LENGTH);
                }
                $descriptions[$orders_id] = $description;
            }
            $default_package            = $this->shipcloudConfigurationStorage->get('default_package');
            $default_package_data       = $this->shipcloudConfigurationStorage->get_all_tree('packages/'
                                                                                             . $default_package . '/');
            $default_package_dimensions = $default_package_data['packages'][$default_package];
            $templateData               = [
                'orders'                   => $orders,
                'orders_weights'           => $orders_weights,
                'transport_conditions'     => $transportConditions,
                'descriptions'             => $descriptions,
                'package'                  => [
                    'weight' => $default_package_dimensions['weight'],
                    'width'  => $default_package_dimensions['width'],
                    'length' => $default_package_dimensions['length'],
                    'height' => $default_package_dimensions['height'],
                ],
                'package_templates'        => $this->shipcloudConfigurationStorage->get_all_tree('packages'),
                'default_package_template' => $default_package,
                //'carriers'                 => $this->shipcloudConfigurationStorage->getCarriers(),
                'preselected_carriers'     => $this->shipcloudConfigurationStorage->get_all_tree('preselected_carriers'),
                'checked_carriers'         => $this->shipcloudConfigurationStorage->get_all_tree('checked_carriers'),
                'prefill_email'            => $this->shipcloudConfigurationStorage->get('prefill_email'),
                'prefill_phone'            => $this->shipcloudConfigurationStorage->get('prefill_phone'),
                'pickup_earliest'          => '',
                'pickup_latest'            => '',
                'pickup_mindate'           => date('Y/m/d', time()),
                'pickup_maxdate'           => date('Y/m/d', strtotime('+2 weeks')),
            ];
            $carriersCache              = MainFactory::create('ShipcloudCarriersCache');
            $templateData['carriers']   = $carriersCache->getCarriers();
            
            if ($template_version == 2) {
                $html = $this->_render('shipcloud_form_multi_v2.html', $templateData);
            } else {
                $html = $this->_render('shipcloud_form_multi.html', $templateData);
            }
            $html = $this->shipcloudText->replaceLanguagePlaceholders($html);
            
            return new HttpControllerResponse($html);
        }
    }
    
    
    /**
     * computes total weight of products for an order
     */
    protected function _getShippingWeight($orders_id)
    {
        $queryString = 'SELECT
				ws.orders_id,
				SUM(ws.items_weight) AS shipping_weight
			FROM (
				SELECT
					op.orders_id,
					(op.products_quantity * p.products_weight) AS items_weight
				FROM
					`orders_products` op
				JOIN
					products p ON p.products_id = op.products_id
				WHERE
					op.orders_id = ?
			) ws
			GROUP BY ws.orders_id';
        $db          = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $row         = $db->query($queryString, [(int)$orders_id])->row();
        
        return (double)$row->shipping_weight;
    }
    
    
    /**
     * Processes form data from form returned by actionCreateMultiLabelForm().
     *
     * @return JsonHttpControllerResponse
     */
    public function actionCreateMultiLabelFormSubmit()
    {
        require DIR_FS_ADMIN . 'includes/classes/order.php';
        $postDataArray = $this->_getPostDataCollection()->getArray();
        $orders_ids    = $postDataArray['orders'];
        unset($postDataArray['orders']);
        $orders = [];
        foreach ($orders_ids as $orders_id) {
            $orders[$orders_id] = new order($orders_id);
        }
        $this->shipcloudLogger->notice(__FUNCTION__ . "\n" . print_r($postDataArray, true));
        
        $contentArray = [
            'orders_ids' => $orders_ids,
            'result'     => 'UNDEFINED',
        ];
        
        $shipmentFactory = MainFactory::create('ShipcloudShipmentFactory');
        /** @var CountryService $countryService */
        $countryService = StaticGXCoreLoader::getService('Country');
        foreach ($orders as $orders_id => $order) {
            $this->shipcloudLogger->notice(sprintf('creating label for order %s', $orders_id));
            try {
                if (empty($order->delivery['house_number'])) {
                    $splitStreet = $this->splitStreet($order->delivery['street_address']);
                } else {
                    $splitStreet = [
                        'street'   => $order->delivery['street_address'],
                        'house_no' => $order->delivery['house_number'],
                    ];
                }
                $singlePostDataArray                       = array_merge($postDataArray);
                $singlePostDataArray['to']                 = [
                    'company'    => $order->delivery['company'],
                    'first_name' => $order->delivery['firstname'],
                    'last_name'  => $order->delivery['lastname'],
                    'care_of'    => $order->delivery['additional_address_info'],
                    'street'     => $splitStreet['street'],
                    'street_no'  => $splitStreet['house_no'],
                    'city'       => $order->delivery['city'],
                    'zip_code'   => $order->delivery['postcode'],
                    'country'    => $order->delivery['country_iso_code_2'],
                ];
                if (!empty($order->delivery['state'])) {
                    try {
                        $toCountry = $countryService->getCountryByName($order->delivery['country']);
                        if ($countryService->countryHasCountryZones($toCountry)) {
                            $toCountryZone = $countryService->getCountryZoneByNameAndCountry($order->delivery['state'], $toCountry);
                            $singlePostDataArray['to']['state'] = (string)$toCountryZone->getCode();
                        }
                    } catch (Exception $e) {
                        // pass – country or zone/state can’t be found
                    }
                }
                
                $singlePostDataArray['to']                 = $this->_enforceLengthLimits($postDataArray['carrier'],
                                                                                         $singlePostDataArray['to']);
                $singlePostDataArray['notification_email'] = '';
                $singlePostDataArray['to']['phone']        = '';
                if ($this->getTransportConditions($orders_id) !== 'declined') {
                    if ((bool)$this->shipcloudConfigurationStorage->get('prefill_email') === true) {
                        $singlePostDataArray['notification_email'] = $order->customer['email_address'];
                    }
                    if ((bool)$this->shipcloudConfigurationStorage->get('prefill_phone') === true) {
                        $singlePostDataArray['to']['phone'] = $order->customer['telephone'];
                    }
                }
                
                $productNames                       = array_map([$this, 'makeProductName'], $order->products);
                $singlePostDataArray['description'] = implode(', ', $productNames);
                
                $shipmentData                = $this->_prepareSingleFormDataForShipmentRequest($singlePostDataArray);
                $shipmentId                  = $shipmentFactory->createShipment($orders_id, $shipmentData);
                $contentArray['shipments'][] = [
                    'orders_id'   => $orders_id,
                    'shipment_id' => $shipmentId,
                    'result'      => 'OK',
                ];
            } catch (Exception $e) {
                $contentArray['shipments'][] = [
                    'orders_id'     => $orders_id,
                    'error_message' => $e->getMessage(),
                    'result'        => 'ERROR',
                ];
            }
        }
        $contentArray['result'] = 'OK';
        
        return MainFactory::create('JsonHttpControllerResponse', $contentArray);
    }
    
    
    /**
     * modifies an array containing a delivery address to suit carrier-specific field lengths
     *
     * Currently returns $toArray unchanged. Future operation tbd.
     *
     * @param string $carrier
     * @param array  $toArray delivery address
     *
     * @return array
     */
    protected function _enforceLengthLimits($carrier, $toArray)
    {
        return $toArray;
        /*
        $lengthLimits = array(
            'dhl' => array(
                'company'   => array('min' => 2, 'max' =>  30, 'empty_allowed' => true),
                'last_name' => array('min' => 1, 'max' =>  30, 'empty_allowed' => false),
                'street'    => array('min' => 1, 'max' =>  40, 'empty_allowed' => false),
                'street_no' => array('min' => 1, 'max' =>   5, 'empty_allowed' => false),
                'zip_code'  => array('min' => 5, 'max' =>   5, 'empty_allowed' => false),
                'city'      => array('min' => 1, 'max' =>  50, 'empty_allowed' => false),
            ),
            'dpd' => array(
                'company'   => array('min' => 1, 'max' =>  35, 'empty_allowed' => true),
                'street'    => array('min' => 1, 'max' =>  35, 'empty_allowed' => false),
                'street_no' => array('min' => 0, 'max' =>   8, 'empty_allowed' => false),
                'zip_code'  => array('min' => 1, 'max' =>   9, 'empty_allowed' => false),
                'city'      => array('min' => 1, 'max' =>  35, 'empty_allowed' => false),
            ),
            'ups' => array(
                'company'   => array('min' => 1, 'max' => 200, 'empty_allowed' => true),
                'last_name' => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
                'street'    => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
                'street_no' => array('min' => 1, 'max' =>  10, 'empty_allowed' => false),
                'zip_code'  => array('min' => 0, 'max' =>  12, 'empty_allowed' => false),
                'city'      => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
            ),
            'hermes' => array(
                'company'   => array('min' => 1, 'max' => 200, 'empty_allowed' => true),
                'last_name' => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
                'street'    => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
                'street_no' => array('min' => 1, 'max' =>  10, 'empty_allowed' => false),
                'zip_code'  => array('min' => 0, 'max' =>  12, 'empty_allowed' => false),
                'city'      => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
            ),
            'gls' => array(
                'company'   => array('min' => 1, 'max' => 200, 'empty_allowed' => true),
                'last_name' => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
                'street'    => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
                'street_no' => array('min' => 1, 'max' =>  10, 'empty_allowed' => false),
                'zip_code'  => array('min' => 0, 'max' =>  12, 'empty_allowed' => false),
                'city'      => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
            ),
            'fedex' => array(
                'company'   => array('min' => 1, 'max' => 200, 'empty_allowed' => true),
                'last_name' => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
                'street'    => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
                'street_no' => array('min' => 1, 'max' =>  10, 'empty_allowed' => false),
                'zip_code'  => array('min' => 0, 'max' =>  12, 'empty_allowed' => false),
                'city'      => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
            ),
            'liefery' => array(
                'company'   => array('min' => 1, 'max' => 200, 'empty_allowed' => true),
                'last_name' => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
                'street'    => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
                'street_no' => array('min' => 1, 'max' =>  10, 'empty_allowed' => false),
                'zip_code'  => array('min' => 0, 'max' =>  12, 'empty_allowed' => false),
                'city'      => array('min' => 1, 'max' => 200, 'empty_allowed' => false),
            ),
        );
        $lengthLimitsName = array(
            'dhl' => 30,
            'ups' => 35,
        );
        $padding = '-';

        if(!in_array($carrier, array_keys($lengthLimits)))
        {
            throw new Exception('invalid carrier '.$carrier.' in '.__CLASS__.'::'.__METHOD__);
        }

        foreach($toArray as $key => $value)
        {
            if(!in_array($key, array_keys($lengthLimits[$carrier])))
            {
                // throw new Exception('invalid field '.$key.' in '.__CLASS__.'::'.__METHOD__);
                continue;
            }

            $valueLen = mb_strlen($value);
            if($valueLen < $lengthLimits[$carrier][$key]['min'])
            {
                $toArray[$key] = $value . str_repeat($padding, $lengthLimits[$carrier][$key]['min'] - $valueLen);
            }
            $toArray[$key] = mb_substr($value, 0, $lengthLimits[$carrier][$key]['max']);
        }

        if(in_array($carrier, array_keys($lengthLimitsName)))
        {
            $nameLength = mb_strlen($toArray['last_name'] . $toArray['first_name']);
            if($nameLength > $lengthLimitsName[$carrier])
            {
                $toArray['first_name'] = mb_substr($toArray['first_name'], 0, 1) . '.';
            }
            $nameLength = mb_strlen($toArray['last_name'] . $toArray['first_name']);
            if($nameLength > $lengthLimitsName[$carrier])
            {
                $toArray['last_name'] = mb_substr($toArray['last_name'], 0, $lengthLimitsName[$carrier] - 3);
            }
        }

        return $toArray;
        */
    }
}
