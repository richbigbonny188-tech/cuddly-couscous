<?php
/* --------------------------------------------------------------
	GeschaeftskundenversandController.inc.php 2020-11-24
	Gambio GmbH
	http://www.gambio.de
	Copyright (c) 2020 Gambio GmbH
	Released under the GNU General Public License (Version 2)
	[http://www.gnu.org/licenses/gpl-2.0.html]
	--------------------------------------------------------------
*/

use Gambio\Core\Language\LanguageService;

/**
 * Class GeschaeftskundenversandController
 *
 * @extends    AdminHttpViewController
 * @category   System
 * @package    AdminHttpViewControllers
 */
class GeschaeftskundenversandController extends AdminHttpViewController
{
    /**
     * @var LanguageTextManager
     */
    protected $languageTextManager;
    
    /**
     * @var GeschaeftskundenversandConfigurationStorage
     */
    protected $configuration;
    
    /**
     * @var GeschaeftskundenversandLogger
     */
    protected $logger;
    
    
    /**
     * Override "proceed" method of parent and use it for initialization.
     *
     * @param HttpContextInterface $httpContext
     */
    public function proceed(HttpContextInterface $httpContext)
    {
        $this->logger = MainFactory::create('GeschaeftskundenversandLogger');
        $this->contentView->set_template_dir(DIR_FS_ADMIN . 'html/content/');
        $this->languageTextManager = MainFactory::create('LanguageTextManager',
                                                         'module_center_module',
                                                         $_SESSION['languages_id']);
        $this->configuration       = MainFactory::create('GeschaeftskundenversandConfigurationStorage');
        
        parent::proceed($httpContext);
    }
    
    
    public function actionPrepareLabel()
    {
        require_once DIR_FS_ADMIN . 'includes/classes/order.php';
        $orders_id        = (int)$this->_getQueryParameter('oID');
        /** @var order_ORIGIN $order */
        $order            = new order($orders_id);
        $orderReadService = StaticGXCoreLoader::getService('OrderRead');
        $storedOrder      = $orderReadService->getOrderById(new IdType($orders_id));
        $orderTotals      = $storedOrder->getOrderTotals();
        $orderTotalValue  = 0;
        foreach ($orderTotals as $orderTotal) {
            if ($orderTotal->getClass() === 'ot_total') {
                $orderTotalValue = $orderTotal->getValue();
            }
        }
        $isAdultContent     = false;
        $orderReadService   = StaticGXCoreLoader::getService('OrderRead');
        $productReadService = StaticGXCoreLoader::getService('ProductRead');
        $storedOrder        = $orderReadService->getOrderById(new IdType($orders_id));
        foreach ($storedOrder->getOrderItems() as $orderItem) {
            $productId = $orderItem->getAddonValue(new StringType('productId'));
            try {
                /** @var GXEngineProduct $product */
                $product = $productReadService->getProductById(new IdType($productId));
                if ($product->isFsk18()) {
                    $isAdultContent = true;
                    break;
                }
            } catch (UnexpectedValueException $e) {
                // probably a product imported from elsewhere; just ignore it.
            }
        }
        
        $customerReadService = StaticGXCoreLoader::getService('CustomerRead');
        $customer            = $customerReadService->findCustomerByEmail(new CustomerEmail($order->customer['email_address']));
        $customersDob        = ($customer instanceof CustomerInterface) ? $customer->getDateOfBirth()
            ->format('Y-m-d') : '1900-01-01';
        $customersDob        = $customersDob === '1000-01-01' ? '1900-01-01' : $customersDob;
    
        $transportConditions = $this->getTransportConditions($orders_id);
        $prefillAllowed      = ($transportConditions === 'accepted' || $transportConditions === 'unshown');
        $prefillEmail        = (bool)$this->configuration->get('prefill_email') === true
                               && $prefillAllowed === true;
        $prefillPhone = (bool)$this->configuration->get('prefill_phone') === true && $prefillAllowed === true;
    
        $productNames = [];
        $allTypes     = array_merge(GeschaeftskundenversandProduct::getValidTypes(),
                                    GeschaeftskundenversandProduct::getDeprecatedTypes());
        foreach ($allTypes as $productType) {
            $productNames[$productType] = $this->languageTextManager->get_text('gkv_product_' . $productType);
        }
    
        $postnumberMatches = [];
        if (preg_match('/^(?P<name>.+)\s*\/\s*(?P<postnumber>\d+)\s*$/', $order->delivery['name'], $postnumberMatches)
            === 1) {
            $receiver_name1_original                  = $postnumberMatches['name'];
            $receiver_packstation_postnumber_original = $postnumberMatches['postnumber'];
        } else {
            $receiver_packstation_postnumber_original = '';
            $receiver_name1_original                  = $order->delivery['name'];
            if (preg_match('/.*(packstation|parcelshop|dhl paketshop|postfiliale).*/i',
                           $order->delivery['street_address']) === 1) {
                if (is_numeric($order->delivery['additional_address_info'])) {
                    $receiver_packstation_postnumber_original = $order->delivery['additional_address_info'];
                } else {
                    if (preg_match('/^postnummer (\d{6,10})/i',
                                   $order->delivery['additional_address_info'],
                                   $postnumberMatches) === 1) {
                        $receiver_packstation_postnumber_original = $postnumberMatches[1];
                    }
                }
            }
        }
        $receiver_packstation_postnumber = mb_substr($receiver_packstation_postnumber_original, 0, 10);
        $receiver_name1         = mb_substr($receiver_name1_original, 0, 50);
        $receiver_address_name2_original = $order->delivery['company'];
        $receiver_address_name2 = mb_substr($receiver_address_name2_original, 0, 35);
        $receiver_address_name3_original = $order->delivery['additional_address_info'];
        $receiver_address_name3 = mb_substr($receiver_address_name3_original, 0, 35);
        if (empty($order->delivery['house_number'])) {
            $splitStreet                            = $this->splitStreet($order->delivery['street_address']);
            $receiver_address_streetname_original   = $order->delivery['street_address'];
            $receiver_address_streetname            = $splitStreet['street'];
            $receiver_address_streetnumber_original = '';
            $receiver_address_streetnumber          = $splitStreet['house_no'];
        } else {
            $receiver_address_streetname_original   = $order->delivery['street_address'];
            $receiver_address_streetname            = mb_substr($receiver_address_streetname_original, 0, 35);
            $receiver_address_streetnumber_original = $order->delivery['house_number'];
            $receiver_address_streetnumber          = mb_substr($receiver_address_streetnumber_original, 0, 5);
        }
        $receiver_packstation_packstationnumber_original = stripos($receiver_address_streetname, 'packstation') !== false ? $receiver_address_streetnumber : '';
        $receiver_packstation_packstationnumber = mb_substr($receiver_packstation_packstationnumber_original, 0, 3);
        $postfilialNumber  = stripos($receiver_address_streetname, 'postfiliale') !== false ? $receiver_address_streetnumber : '';
        $parcelshopNumber  = stripos($receiver_address_streetname, 'parcelshop') !== false ? $receiver_address_streetnumber : '';
        $parcelshopNumber  = empty($parcelshopNumber) && stripos($receiver_address_streetname, 'paketshop') !== false ? $receiver_address_streetnumber : '';
        if ($order->delivery['country_iso_code_2'] === $this->configuration->get('shipper/origincountry')) {
            if (empty($postfilialNumber) && !empty($parcelshopNumber)) {
                $postfilialNumber = $parcelshopNumber;
            }
            $parcelshopNumber = '';
        }
        $receiver_type = 'address';
        if (!empty($receiver_packstation_packstationnumber)) {
            $receiver_type = 'packstation';
        } elseif (!empty($postfilialNumber)) {
            $receiver_type = 'postfiliale';
        } elseif (!empty($parcelshopNumber)) {
            $receiver_type = 'parcelshop';
        }
        $receiver_address_addressaddition_original = '';
        $receiver_address_addressaddition = mb_substr($receiver_address_addressaddition_original, 0, 35);
        $receiver_address_zip_original = $order->delivery['postcode'];
        $receiver_address_zip = mb_substr($receiver_address_zip_original, 0, 10);
        $receiver_address_city_original = $order->delivery['city'];
        $receiver_address_city = mb_substr($receiver_address_city_original, 0, 35);
        $receiver_address_origincountry = $order->delivery['country_iso_code_2'];
        $receiver_email_original = '';
        $receiver_email = '';
        if ($prefillEmail) {
            $receiver_email_original = $order->customer['email_address'];
            $receiver_email = mb_substr($receiver_email_original, 0, 50);
        }
        $receiver_phone_original = '';
        $receiver_phone = '';
        if ($prefillPhone) {
            $receiver_phone_original = $order->customer['telephone'];
            $receiver_phone = mb_substr($receiver_phone_original, 0, 20);
        }

        $shipment_date = date('Y-m-d');
    
        $isExport                             = $receiver_address_origincountry
                                                !== $this->configuration->get('shipper/origincountry');
        $exportdoc_exporttype                 = 'EU';
        $exportdoc_exporttypedescription      = '';
        $exportdoc_invoicenumber              = $orders_id;
        $exportdoc_termsoftrade               = '';
        $exportdoc_placeofcommittal           = '';
        $exportdoc_additionalfee              = '';
        $exportdoc_permitnumber               = '';
        $exportdoc_attestationnumber          = '';
        $exportdoc_withelectronicexportntfctn = false;
    
        $productsWeight = $this->getProductsWeights($order);
        $exportPositions = [];
        foreach($order->products as $orderProductsIndex => $orderProduct) {
            $exportPositions[] = [
                'name'                => mb_substr($orderProduct['name'], 0, 256),
                'countrycodeorigin'   => $this->configuration->get('shipper/origincountry'),
                'customstariffnumber' => '',
                'amount'              => $orderProduct['qty'],
                'netweightkg'         => $productsWeight[$orderProductsIndex],
                'customsvalue'        => number_format($orderProduct['price'], 2, '.', ''),
            ];
        }
        
        $products = $this->configuration->getProducts();
        if (count($products) === 0) {
            $configUrl = xtc_href_link('admin.php', 'do=GeschaeftskundenversandModuleCenterModule');
            return new RedirectHttpControllerResponse($configUrl);
        }
        /**
         * @var int $productsIdx
         * @var GeschaeftskundenversandProduct $product
         */
        foreach ($products as $productsIdx => $product) {
            $productsDefault = $productsDefault ?? $productsIdx;
            if ($isExport && $product->getTargetArea() !== 'domestic') {
                $productsDefault = $productsIdx;
                break;
            }
            if (!$isExport && $product->getTargetArea() === 'domestic') {
                $productsDefault = $productsIdx;
                break;
            }
        }
        
        $premiumPreselect = false;
        if ($isExport === true) {
            if ($this->configuration->get('intlpremium') === 'always') {
                $premiumPreselect = true;
            } elseif ($this->configuration->get('intlpremium') === 'eu-only') {
                $euCountries     = static::getEuCountries();
                $deliveryCountry = strtolower($order->delivery['country_iso_code_2']);
                if (in_array($deliveryCountry, $euCountries, true)) {
                    $premiumPreselect = true;
                }
            }
        }
        
        $notification_original = '';
        $notification = '';
        if ($prefillEmail) {
            $notification_original = $order->customer['email_address'];
            $notification = mb_substr($notification, 0, 50);
        }
        $visualcheckofage = '';
        if ($isAdultContent && $this->configuration->get('age_check') === 'visualage18') {
            $visualcheckofage = 'a18';
        }
        $identcheck = $isAdultContent && $this->configuration->get('age_check') === 'identcheck18';
        $identcheck_surname_original = $order->delivery['lastname'];
        $identcheck_surname = mb_substr($identcheck_surname_original, 0, 255);
        $identcheck_givenname_original = $order->delivery['firstname'];
        $identcheck_givenname = mb_substr($identcheck_givenname_original, 0, 255);
        $identcheck_dob       = $customersDob;
        $identcheck_minimumage = 'A18';

        $namedpersononly = false;
        $preferredday = '';
        $preferredtime = '';
        
        $today               = new DateTime('now', new DateTimeZone('Europe/Berlin'));
        
        $shippingWeight = (float)$order->info['total_weight'];
        if ((bool)$this->configuration->get('add_packing_weight') === true) {
            $packingWeight  = max((float)@constant('SHIPPING_BOX_WEIGHT'),
                                  $shippingWeight * ((float)@constant('SHIPPING_BOX_PADDING') / 100));
            $shippingWeight += $packingWeight;
        }
        $shippingWeight = max(0.1, $shippingWeight);
        $shipping_weight = number_format($shippingWeight, 3, '.', '');
    
        $preferredlocation         = '';
        $preferredneighbour        = '';
        $noneighbourdelivery       = false;
        $parceloutletrouting       = false;
        $parceloutletrouting_email = '';
        if ($prefillEmail) {
            $parceloutletrouting_email = mb_substr($order->customer['email_address'], 0, 100);
        }
        $packagingreturn     = false;
        $returnimmediately   = false;
        $premium             = $premiumPreselect;
        $bulkygoods          = false;
        $additionalinsurance = false;
        $only_if_codeable    = true;
    
        $failedData = $_SESSION['geschaeftskundenversand_shipment_params'][$orders_id] ?? [];

        if (!empty($failedData)) {
            $exportPosData = array_filter($failedData, static function($key) {
                return preg_match('/exportpos_\d+_/', $key) === 1;
            }, ARRAY_FILTER_USE_KEY);
            $exportPositions = [];
            foreach($exportPosData as $epdKey => $epdValue) {
                [$prefix, $epdIndex, $epdSubkey] = explode('_', $epdKey);
                if ($epdSubkey === 'description') {
                    $exportPositions[] = [
                        'name'                => $failedData['exportpos_' . $epdIndex . '_description'],
                        'countrycodeorigin'   => $failedData['exportpos_' . $epdIndex . '_countrycodeorigin'],
                        'customstariffnumber' => $failedData['exportpos_' . $epdIndex . '_customstariffnumber'],
                        'amount'              => $failedData['exportpos_' . $epdIndex . '_amount'],
                        'netweightkg'         => $failedData['exportpos_' . $epdIndex . '_netweightkg'],
                        'customsvalue'        => $failedData['exportpos_' . $epdIndex . '_customsvalue'],
                    ];
                }
            }
        }
    
        $formdata = [
            'pageToken'                                 => $_SESSION['coo_page_token']->generate_token(),
            'order_url'                                 => xtc_href_link('orders.php',
                                                                         'oID=' . $orders_id . '&action=edit'),
            'orders_id'                                 => $orders_id,
            'countries'                                 => $this->getCountries(),
            'shipments'                                 => $this->getShipments($orders_id),
            'configuration'                             => $this->configuration->get_all(),
            'action_create_label'                       => xtc_href_link('admin.php',
                                                                         'do=Geschaeftskundenversand/CreateLabel'),
            'action_reset_inputs'                       => xtc_href_link('admin.php',
                                                                         'do=Geschaeftskundenversand/ResetInputs'),
            'action_delete_label'                       => xtc_href_link('admin.php',
                                                                         'do=Geschaeftskundenversand/DeleteLabel'),
            'label_target'                              => (int)$this->configuration->get('open_in_new_tab')
                                                           === 1 ? '_blank' : '',
            'extd_view'                                 => (isset($failedData['extd_view']) &&
                                                            (bool)$failedData['extd_view']) ? '1' : '0',
            'products'                                  => $products,
            'products_default'                          => $failedData['product_index'] ?? $productsDefault,
            'product_names'                             => $productNames,
            'order'                                     => $order,
            'delivery_postfiliale'                      => $postfilialNumber,
            'delivery_parcelshop'                       => $parcelshopNumber,
            'preferredday_min'                          => $this->addWorkdays($today, 2)->format('Y-m-d'),
            'preferredday_max'                          => $this->addWorkdays($today, 6)->format('Y-m-d'),
            'is_cod'                                    => (isset($failedData['cashondelivery'])
                                                            && (float)$failedData['cashondelivery'] > 0)
                                                           || stripos($order->info['payment_method'], 'cod') !== false,
            'isAdultContent'                            => $isAdultContent,
            'failed_data'                               => $failedData,
            /* ------------------ */
            'shipper_name1'                             => $failedData['shipper/name1'] ??
                                                           $this->configuration->get('shipper/name1'),
            'shipper_name2'                             => $failedData['shipper/name2'] ??
                                                           $this->configuration->get('shipper/name2'),
            'shipper_name3'                             => $failedData['shipper/name3'] ??
                                                           $this->configuration->get('shipper/name3'),
            'shipper_streetname'                        => $failedData['shipper/streetname'] ??
                                                           $this->configuration->get('shipper/streetname'),
            'shipper_streetnumber'                      => $failedData['shipper/streetnumber'] ??
                                                           $this->configuration->get('shipper/streetnumber'),
            'shipper_addressaddition'                   => $failedData['shipper/addressaddition'] ??
                                                           $this->configuration->get('shipper/addressaddition'),
            'shipper_zip'                               => $failedData['shipper/zip'] ??
                                                           $this->configuration->get('shipper/zip'),
            'shipper_city'                              => $failedData['shipper/city'] ??
                                                           $this->configuration->get('shipper/city'),
            'shipper_origincountry'                     => $failedData['shipper/origincountry'] ??
                                                           $this->configuration->get('shipper/origincountry'),
            'shipper_email'                             => $failedData['shipper/email'] ??
                                                           $this->configuration->get('shipper/email'),
            'shipper_phone'                             => $failedData['shipper/phone'] ??
                                                           $this->configuration->get('shipper/phone'),
            'returnreceiver_name1'                      => $failedData['returnreceiver/name1'] ??
                                                           $this->configuration->get('returnreceiver/name1'),
            'returnreceiver_name2'                      => $failedData['returnreceiver/name2'] ??
                                                           $this->configuration->get('returnreceiver/name2'),
            'returnreceiver_name3'                      => $failedData['returnreceiver/name3'] ??
                                                           $this->configuration->get('returnreceiver/name3'),
            'returnreceiver_streetname'                 => $failedData['returnreceiver/streetname'] ??
                                                           $this->configuration->get('returnreceiver/streetname'),
            'returnreceiver_streetnumber'               => $failedData['returnreceiver/streetnumber'] ??
                                                           $this->configuration->get('returnreceiver/streetnumber'),
            'returnreceiver_addressaddition'            => $failedData['returnreceiver/addressaddition'] ??
                                                           $this->configuration->get('returnreceiver/addressaddition'),
            'returnreceiver_zip'                        => $failedData['returnreceiver/zip'] ??
                                                           $this->configuration->get('returnreceiver/zip'),
            'returnreceiver_city'                       => $failedData['returnreceiver/city'] ??
                                                           $this->configuration->get('returnreceiver/city'),
            'returnreceiver_origincountry'              => $failedData['returnreceiver/origincountry'] ??
                                                           $this->configuration->get('returnreceiver/origincountry'),
            'returnreceiver_email'                      => $failedData['returnreceiver/email'] ??
                                                           $this->configuration->get('returnreceiver/email'),
            'returnreceiver_phone'                      => $failedData['returnreceiver/phone'] ??
                                                           $this->configuration->get('returnreceiver/phone'),
            'receiver_name1'                            => $failedData['receiver/name1'] ?? $receiver_name1,
            'receiver_type'                             => $failedData['receiver_type'] ?? $receiver_type,
            'receiver_address_name2'                    => $failedData['receiver_address/name2'] ??
                                                           $receiver_address_name2,
            'receiver_address_name2_original'           => $receiver_address_name2_original,
            'receiver_address_name3'                    => $failedData['receiver_address/name3'] ??
                                                           $receiver_address_name3,
            'receiver_address_name3_original'           => $receiver_address_name3_original,
            'receiver_address_streetname'               => $failedData['receiver_address/streetname'] ??
                                                           $receiver_address_streetname,
            'receiver_address_streetname_original'      => $receiver_address_streetname_original,
            'receiver_address_streetnumber'             => $failedData['receiver_address/streetnumber'] ??
                                                           $receiver_address_streetnumber,
            'receiver_address_streetnumber_original'    => $receiver_address_streetnumber_original,
            'receiver_address_addressaddition'          => $failedData['receiver_address/addressaddition'] ??
                                                           $receiver_address_addressaddition,
            'receiver_address_addressaddition_original' => $receiver_address_addressaddition_original,
            'receiver_address_zip_original'             => $receiver_address_zip_original,
            'receiver_address_zip'                      => $failedData['receiver_address/zip'] ?? $receiver_address_zip,
            'receiver_address_city_original'            => $receiver_address_city_original,
            'receiver_address_city'                     => $failedData['receiver_address/city'] ??
                                                           $receiver_address_city,
            'receiver_address_origincountry'            => $failedData['receiver_address/origincountry'] ??
                                                           $receiver_address_origincountry,
            'receiver_packstation_postnumber'           => $failedData['receiver_packstation/postnumber'] ??
                                                           $receiver_packstation_postnumber,
            'receiver_packstation_packstationnumber'    => $failedData['receiver_packstation/packstationnumber'] ??
                                                           $receiver_packstation_packstationnumber,
            'receiver_packstation_zip'                  => $failedData['receiver_packstation/zip'] ??
                                                           $receiver_address_zip,
            'receiver_packstation_city'                 => $failedData['receiver_packstation/city'] ??
                                                           $receiver_address_city,
            'receiver_packstation_origincountry'        => $failedData['receiver_packstation/origincountry'] ??
                                                           $receiver_address_origincountry,
            'receiver_postfiliale_postnumber'           => $failedData['receiver_postfiliale/postnumber'] ??
                                                           $receiver_packstation_postnumber,
            'receiver_postfiliale_postfilialnumber'     => $failedData['receiver_postfiliale/postfilialnumber'] ??
                                                           $postfilialNumber,
            'receiver_postfiliale_zip'                  => $failedData['receiver_postfiliale/postnumber'] ??
                                                           $receiver_address_zip,
            'receiver_postfiliale_city'                 => $failedData['receiver_postfiliale/city'] ??
                                                           $receiver_address_city,
            'receiver_postfiliale_origincountry'        => $failedData['receiver_postfiliale/origincountry'] ??
                                                           $receiver_address_origincountry,
            'receiver_parcelshop_parcelshopnumber'      => $failedData['receiver_parcelshop/parcelshopnumber'] ??
                                                           $parcelshopNumber,
            'receiver_parcelshop_streetname'            => $failedData['receiver_parcelshop/streetname'] ??
                                                           $receiver_address_streetname,
            'receiver_parcelshop_streetnumber'          => $failedData['receiver_parcelshop/streetnumber'] ??
                                                           $receiver_address_streetnumber,
            'receiver_parcelshop_zip'                   => $failedData['receiver_parcelshop/zip'] ??
                                                           $receiver_address_zip,
            'receiver_parcelshop_city'                  => $failedData['receiver_parcelshop/city'] ??
                                                           $receiver_address_city,
            'receiver_parcelshop_origincountry'         => $failedData['receiver_parcelshop/origincountry'] ??
                                                           $receiver_address_origincountry,
            'receiver_email_original'                   => $receiver_email_original,
            'receiver_email'                            => $failedData['receiver/email'] ?? $receiver_email,
            'receiver_phone_original'                   => $receiver_phone_original,
            'receiver_phone'                            => $failedData['receiver/phone'] ?? $receiver_phone,
            'shipping_weight'                           => $failedData['shipping_weight'] ?? $shipping_weight,
            'shipment_date'                             => $failedData['shipment_date'] ?? $shipment_date,
            'customer_reference'                        => $failedData['customer_reference'] ?? $orders_id,
            'exportdoc_exporttype'                      => $failedData['exportdoc/exporttype'] ?? $exportdoc_exporttype,
            'exportdoc_exporttypedescription'           => $failedData['exportdoc/exporttypedescription'] ??
                                                           $exportdoc_exporttypedescription,
            'exportdoc_invoicenumber'                   => $failedData['exportdoc/invoicenumber'] ??
                                                           $exportdoc_invoicenumber,
            'exportdoc_termsoftrade'                    => $failedData['exportdoc/termsoftrade'] ??
                                                           $exportdoc_termsoftrade,
            'exportdoc_placeofcommittal'                => $failedData['exportdoc/placeofcommital'] ??
                                                           $exportdoc_placeofcommittal,
            'exportdoc_additionalfee'                   => $failedData['exportdoc/additionalfee'] ??
                                                           $exportdoc_additionalfee,
            'exportdoc_permitnumber'                    => $failedData['exportdoc/permitnumber'] ??
                                                           $exportdoc_permitnumber,
            'exportdoc_attestationnumber'               => $failedData['exportdoc/attestationnumber'] ??
                                                           $exportdoc_attestationnumber,
            'exportdoc_withelectronicexportntfctn'      => $failedData['exportdoc/withelectronicexportntfctn'] ??
                                                           $exportdoc_withelectronicexportntfctn,
            'export_positions'                          => $exportPositions,
            'preferredlocation'                         => $failedData['preferredlocation'] ?? $preferredlocation,
            'preferredneighbour'                        => $failedData['preferredneighbour'] ?? $preferredneighbour,
            'notification_original'                     => $notification_original,
            'notification'                              => $failedData['notification'] ?? $notification,
            'visualcheckofage'                          => $failedData['visualcheckofage'] ?? $visualcheckofage,
            'identcheck'                                => isset($failedData['identcheck']) ? (bool)$failedData['identcheck'] : $identcheck,
            'identcheck_surname'                        => $failedData['identcheck_surname'] ?? $identcheck_surname,
            'identcheck_givenname'                      => $failedData['identcheck_givenname'] ?? $identcheck_givenname,
            'identcheck_dob'                            => $failedData['identcheck_dateofbirth'] ?? $identcheck_dob,
            'identcheck_minimumage'                     => $failedData['identcheck_minimumage'] ??
                                                           $identcheck_minimumage,
            'namedpersononly'                           => isset($failedData['namedpersononly']) ? (bool)$failedData['namedpersononly'] : $namedpersononly,
            'preferredday'                              => $failedData['preferredday'] ?? $preferredday,
            'preferredtime'                             => $failedData['preferredtime'] ?? $preferredtime,
            'noneighbourdelivery'                       => isset($failedData['noneighbourdelivery']) ? (bool)$failedData['noneighbourdelivery'] : $noneighbourdelivery,
            'parceloutletrouting'                       => isset($failedData['parceloutletrouting']) ? (bool)$failedData['parceloutletrouting'] : $parceloutletrouting,
            'parceloutletrouting_email'                 => $failedData['parceloutletrouting_email'] ??
                                                           $parceloutletrouting_email,
            'packagingreturn'                           => isset($failedData['packagingreturn']) ? (bool)$failedData['packagingreturn'] : $packagingreturn,
            'returnimmediately'                         => isset($failedData['returnimmediately']) ? (bool)$failedData['returnimmediately'] : $returnimmediately,
            'premium'                                   => isset($failedData['premium']) ? (bool)$failedData['premium'] : $premium,
            'bulkygoods'                                => isset($failedData['bulkygoods']) ? (bool)$failedData['bulkygoods'] : $bulkygoods,
            'cod_amount'                                => $failedData['cashondelivery'] ??
                                                           number_format($orderTotalValue, 2, '.', ''),
            'additionalinsurance'                       => $failedData['additionalinsurance'] ?? $additionalinsurance,
            'insurance_amount'                          => number_format($orderTotalValue, 2, '.', ''),
            'only_if_codeable'                          => isset($failedData['only_if_codeable']) ? (bool)$failedData['only_if_codeable'] : $only_if_codeable,
        ];
    
        $assets = [
            DIR_WS_CATALOG . 'admin/html/assets/styles/modules/geschaeftskundenversand.min.css',
            DIR_WS_CATALOG
            . 'admin/html/assets/javascript/modules/geschaeftskundenversand/geschaeftskundenversand-form.min.js',
        ];
        
        return AdminLayoutHttpControllerResponse::createAsLegacyAdminPageResponse($this->languageTextManager->get_text('gkv_head_prepare_label'),
                                                                                  'geschaeftskundenversand_form_single.html',
                                                                                  $formdata,
                                                                                  $assets);
    }
    
    protected function getProductsWeights($order): array
    {
        $productsWeight = [];
        $db             = StaticGXCoreLoader::getDatabaseQueryBuilder();
        foreach ($order->products as $productsIndex => $product) {
            $productRow                     = $db->get_where('products', ['products_id' => $product['id']])->row();
            $productsWeight[$productsIndex] = $productRow->products_weight;
        
            $propertiesRow                  = $db->from('orders_products_properties')
                ->select('orders_products_properties.products_properties_combis_id, combi_weight')
                ->join('products_properties_combis',
                       'orders_products_properties.products_properties_combis_id = products_properties_combis.products_properties_combis_id')
                ->where('orders_products_id', (int)$product['opid'])
                ->group_by('orders_products_properties.products_properties_combis_id, combi_weight')
                ->get()
                ->row();
            $productsWeight[$productsIndex] += $propertiesRow->combi_weight;
        
            $attributesQuery = $db->from('orders_products_attributes')
                ->join('products_attributes',
                       'products_attributes.products_id = ' . (int)$product['id'] . ' AND '
                       . 'products_attributes.options_id = orders_products_attributes.options_id AND '
                       . 'products_attributes.options_values_id = orders_products_attributes.options_values_id')
                ->select('products_attributes.options_values_weight, products_attributes.weight_prefix')
                ->where('orders_products_id', (int)$product['opid']);
            foreach ($attributesQuery->get()->result() as $attributesRow) {
                $prefixFactor                   = $attributesRow->weight_prefix === '+' ? 1 : -1;
                $productsWeight[$productsIndex] += $attributesRow->options_values_weight * $prefixFactor;
            }
        
            $productsWeight[$productsIndex] = max(0.01, $productsWeight[$productsIndex]);
            $productsWeight[$productsIndex] = number_format($productsWeight[$productsIndex], 3, '.', '');
        }
        return $productsWeight;
    }

    protected static function getEuCountries(): array
    {
        return [
            'be',
            'gr',
            'mt',
            'sk',
            'bg',
            'ie',
            'nl',
            'si',
            'dk',
            'it',
            'at',
            'es',
            'de',
            'hr',
            'pl',
            'cz',
            'ee',
            'lv',
            'pt',
            'hu',
            'fi',
            'lt',
            'ro',
            'gb',
            'fr',
            'lu',
            'se',
            'cy',
        ];
    }
    
    
    protected function getTransportConditions($orderIdString)
    {
        try {
            $orderId           = new IdType($orderIdString);
            $orderReadService  = StaticGXCoreLoader::getService('OrderRead');
            $order             = $orderReadService->getOrderById($orderId);
            $addonValueService = StaticGXCoreLoader::getService('AddonValue');
            $addonValueService->loadAddonValues($order);
            $transportConditions = $order->getAddonValue(new StringType('transportConditions'));
        } catch (Exception $e) {
            $transportConditions = 'unshown';
        }
        
        return $transportConditions;
    }
    
    
    protected function getCountries()
    {
        $db        = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $countries = $db->order_by('countries_iso_code_2', 'ASC')->get('countries')->result_array();
        
        return $countries;
    }
    
    
    protected function insertBankDataPlaceholders($configuredText, $orders_id)
    {
        $replacements = ['%orders_id%' => (int)$orders_id];
        $outputText   = strtr($configuredText, $replacements);
        
        return $outputText;
    }
    
    
    public function actionResetInputs()
    {
        $this->_validatePageToken();
        $postData = $this->_getPostDataCollection()->getArray();
        unset($_SESSION['geschaeftskundenversand_shipment_params'][$postData['orders_id']]);
        $redirectUrl = xtc_href_link('admin.php',
                                     'do=Geschaeftskundenversand/PrepareLabel&oID=' . (int)$postData['orders_id']);
    
        return MainFactory::create('RedirectHttpControllerResponse', $redirectUrl);
    }
        
        public function actionCreateLabel()
    {
        $this->_validatePageToken();
        $postData = $this->_getPostDataCollection()->getArray();
        
        $product = Mainfactory::create('GeschaeftskundenversandProduct',
                                       $this->configuration->get('products/' . (int)$postData['product_index']
                                                                 . '/type'),
                                       $this->configuration->get('products/' . (int)$postData['product_index']
                                                                 . '/attendance'),
                                       $this->configuration->get('products/' . (int)$postData['product_index']
                                                                 . '/alias'));
        
        $note1            = $this->insertBankDataPlaceholders($this->configuration->get('bankdata/note1'),
                                                              $postData['orders_id']);
        $note2            = $this->insertBankDataPlaceholders($this->configuration->get('bankdata/note2'),
                                                              $postData['orders_id']);
        $accountreference = $this->insertBankDataPlaceholders($this->configuration->get('bankdata/accountreference'),
                                                              $postData['orders_id']);
        
        $shipment = Mainfactory::create('GeschaeftskundenversandShipment', $this->configuration->get('ekp'));
        $shipment->setBankData($this->configuration->get('bankdata/accountowner'),
                               $this->configuration->get('bankdata/bankname'),
                               $this->configuration->get('bankdata/iban'),
                               $note1,
                               $note2,
                               $this->configuration->get('bankdata/bic'),
                               $accountreference);
        $shipment->setShipperName($postData['shipper/name1'], $postData['shipper/name2'], $postData['shipper/name3']);
        $shipment->setShipperAddress($postData['shipper/streetname'],
                                     $postData['shipper/streetnumber'],
                                     $postData['shipper/addressaddition'],
                                     $postData['shipper/zip'],
                                     $postData['shipper/city'],
                                     $postData['shipper/origincountry']);
        $shipment->setShipperCommunication($postData['shipper/phone'],
                                           $postData['shipper/email'],
                                           $postData['shipper/name1']);
        $shipment->setShipperReference($this->configuration->get('shipperreference'));
        $shipment->setReturnReceiverName($postData['returnreceiver/name1'],
                                         $postData['returnreceiver/name2'],
                                         $postData['returnreceiver/name3']);
        $shipment->setReturnReceiverAddress($postData['returnreceiver/streetname'],
                                            $postData['returnreceiver/streetnumber'],
                                            $postData['returnreceiver/addressaddition'],
                                            $postData['returnreceiver/zip'],
                                            $postData['returnreceiver/city'],
                                            $postData['returnreceiver/origincountry']);
        $shipment->setReturnReceiverCommunication($postData['returnreceiver/phone'],
                                                  $postData['returnreceiver/email'],
                                                  $postData['returnreceiver/name1']);
        
        $shipment->setReceiverName($postData['receiver/name1']);
        if ($postData['receiver_type'] === 'address') {
            $shipment->setReceiverAddress($postData['receiver_address/streetname'],
                                          $postData['receiver_address/streetnumber'],
                                          $postData['receiver_address/addressaddition'],
                                          $postData['receiver_address/zip'],
                                          $postData['receiver_address/city'],
                                          $postData['receiver_address/origincountry']);
            $shipment->setReceiverAdditionalNames($postData['receiver_address/name2'],
                                                  $postData['receiver_address/name3']);
        }
        if ($postData['receiver_type'] === 'packstation') {
            $shipment->setReceiverPackstation($postData['receiver_packstation/packstationnumber'],
                                              $postData['receiver_packstation/zip'],
                                              $postData['receiver_packstation/city'],
                                              $postData['receiver_packstation/origincountry'],
                                              $postData['receiver_packstation/postnumber']);
        }
        if ($postData['receiver_type'] === 'postfiliale') {
            $shipment->setReceiverPostfiliale($postData['receiver_postfiliale/postfilialnumber'],
                                              $postData['receiver_postfiliale/zip'],
                                              $postData['receiver_postfiliale/city'],
                                              $postData['receiver_postfiliale/origincountry'],
                                              $postData['receiver_postfiliale/postnumber']);
        }
        if ($postData['receiver_type'] === 'parcelshop') {
            $shipment->setReceiverParcelShop($postData['receiver_parcelshop/parcelshopnumber'],
                                             $postData['receiver_parcelshop/zip'],
                                             $postData['receiver_parcelshop/city'],
                                             $postData['receiver_parcelshop/origincountry'],
                                             $postData['receiver_parcelshop/streetname'],
                                             $postData['receiver_parcelshop/streetnumber']);
        }
        $shipment->setReceiverCommunication($postData['receiver/phone'],
                                            $postData['receiver/email'],
                                            $postData['receiver/name1']);
        $shipment->setNotification($postData['notification']);
        $setReturnProduct = (bool)$this->configuration->get('create_return_label');
        $shipment->setProduct($product, $setReturnProduct);
        $shipment->setWeight($postData['shipping_weight']);
        $shipment->setShipmentDate($postData['shipment_date']);
        $shipment->setCustomerReference($postData['customer_reference']);
        $shipment->setAllServices($postData);
        if ($product->getTargetArea() !== 'domestic' && $postData['exportdoc/exporttype'] !== 'NONE') {
            $shipment->setExportDocument($postData['exportdoc/exporttype'],
                                         $postData['exportdoc/placeofcommital'],
                                         $postData['exportdoc/additionalfee'],
                                         $postData['exportdoc/invoicenumber'],
                                         $postData['exportdoc/termsoftrade'],
                                         $postData['exportdoc/permitnumber'],
                                         $postData['exportdoc/attestationnumber'],
                                         filter_var($postData['exportdoc/withelectronicexportntfctn'],
                                                    FILTER_VALIDATE_BOOLEAN));
            if ($postData['exportdoc/exporttype'] === 'OTHER') {
                $shipment->setExportTypeDescription($postData['exportdoc/exporttypedescription']);
            }
            $exportPosData = array_filter($postData, static function($key) {
                return preg_match('/exportpos_\d+_/', $key) === 1;
            }, ARRAY_FILTER_USE_KEY);
            foreach($exportPosData as $epdKey => $epdValue) {
                [$prefix, $epdIndex, $epdSubkey] = explode('_', $epdKey);
                if ($epdSubkey === 'description') {
                    $shipment->addExportDocPosition($postData['exportpos_' . $epdIndex . '_description'],
                                                    $postData['exportpos_' . $epdIndex . '_countrycodeorigin'],
                                                    $postData['exportpos_' . $epdIndex . '_customstariffnumber'],
                                                    $postData['exportpos_' . $epdIndex . '_amount'],
                                                    $postData['exportpos_' . $epdIndex . '_netweightkg'],
                                                    $postData['exportpos_' . $epdIndex . '_customsvalue']);
                }
            }
        }
        $shipment->setPrintOnlyIfCodeable(!empty($postData['only_if_codeable']));
        $shipment->setLabelResponseType('URL');
        $shipment->setLabelFormat($this->configuration->get('label_format'));
        $shipment->setLabelFormatRetoure($this->configuration->get('label_format_retoure'));
        $shipment->setCombinedPrinting((bool)$this->configuration->get('combined_printing'));
        
        $shipmentParams = $shipment->toArray();
        $this->logger->noticeDebug(var_export($shipmentParams, true));
        $_SESSION['geschaeftskundenversand_shipment_params'] = $_SESSION['geschaeftskundenversand_shipment_params'] ?? [];
        $_SESSION['geschaeftskundenversand_shipment_params'][$postData['orders_id']] = $postData;
        try {
            $gkvSoapAdapter = Mainfactory::create('GeschaeftskundenversandSoapAdapter', $this->configuration);
            $sc             = $gkvSoapAdapter->getSoapClient();
            
            $response = $sc->createShipmentOrder($shipmentParams);
            $this->logger->noticeDebug(sprintf("CreateShipmentOrder request:\n%s\n", $sc->__getLastRequest()));
            $this->logger->noticeDebug(sprintf("CreateShipmentOrder response:\n%s\n", print_r($response, true)));
            if ((int)$response->Status->statusCode === 0) {
                $this->storeCreateShipmentResponse($postData['orders_id'], $response);
                $statusMessage = '';
                foreach ($response->CreationState->LabelData->Status->statusMessage as $statusMessageElement) {
                    $statusMessage .= (string)$statusMessageElement . '<br>';
                }
                $GLOBALS['messageStack']->add_session(sprintf('%s (%s (%s/%s))<br>%s',
                                                              $this->languageTextManager->get_text('gkv_label_created'),
                                                              (string)$response->CreationState->shipmentNumber,
                                                              (string)$response->CreationState->LabelData->Status->statusCode,
                                                              (string)$response->CreationState->LabelData->Status->statusText,
                                                              $statusMessage),
                                                      'info');
                $this->logger->notice(sprintf('Shipment %s created, label URL: %s',
                                              $response->CreationState->LabelData->shipmentNumber,
                                              $response->CreationState->LabelData->labelUrl));
                unset($_SESSION['geschaeftskundenversand_shipment_params'][$postData['orders_id']]);
                
                $parcelServiceId = $this->configuration->get('parcel_service_id');
                if ($parcelServiceId > 0) {
                    $parcelServiceReader      = MainFactory::create('ParcelServiceReader');
                    $parcelTrackingCodeWriter = MainFactory::create('ParcelTrackingCodeWriter');
                    $parcelTrackingCodeWriter->insertTrackingCode((int)$postData['orders_id'],
                                                                  (string)$response->CreationState->shipmentNumber,
                                                                  $parcelServiceId,
                                                                  $parcelServiceReader);
                }
                
                $order_status_id = $this->configuration->get('order_status_after_label');
                $notifyCustomer  = $this->configuration->get('notify_customer');
                if ($order_status_id >= 0) {
                    $order_status_comment = $this->languageTextManager->get_text('gkv_order_status_comment') . ' '
                                            . (string)$response->CreationState->shipmentNumber;
                    $this->setOrderStatus($postData['orders_id'],
                                          $order_status_id,
                                          $order_status_comment,
                                          (bool)$notifyCustomer);
                }
            } else {
                $statusMessage = sprintf('%s (%s) - %s',
                                         $response->Status->statusMessage,
                                         $response->Status->statusCode,
                                         $response->Status->statusText);
                
                if (isset($response->CreationState)) {
                    foreach ($response->CreationState->LabelData->Status->statusMessage as $partMessage) {
                        $statusMessage .= !empty($statusMessage) ? '<br>' : '';
                        $statusMessage .= (string)$partMessage;
                    }
                }
                $GLOBALS['messageStack']->add_session(sprintf('%s - %s (%s)',
                                                              $this->languageTextManager->get_text('gkv_error_creating_label'),
                                                              $statusMessage,
                                                              (string)$response->Status->statusCode),
                                                      'error');
            }
        } catch (SoapFault $sf) {
            $lastRequest  = isset($sc) ? $this->prettyXML($sc->__getLastRequest()) : '-- no request --';
            $lastResponse = isset($sc) ? $this->prettyXML($sc->__getLastResponse()) : '-- no response --';
            
            $GLOBALS['messageStack']->add_session(sprintf('%s - %s',
                                                          $this->languageTextManager->get_text('gkv_error_creating_label'),
                                                          $sf->getMessage()),
                                                  'error');
            $this->logger->notice(sprintf("ERROR/SF creating label:\n%s\nRequest:\n%s\nResponse:\n%s\n",
                                          print_r($sf, true),
                                          $lastRequest,
                                          $lastResponse));
        } catch (GeschaeftskundenversandSoapAdapterServiceUnavailableException $e) {
            $lastRequest  = isset($sc) ? $this->prettyXML($sc->__getLastRequest()) : '-- no request --';
            $lastResponse = isset($sc) ? $this->prettyXML($sc->__getLastResponse()) : '-- no response --';
            
            $GLOBALS['messageStack']->add_session(sprintf('%s - %s',
                                                          $this->languageTextManager->get_text('gkv_error_creating_label'),
                                                          $e->getMessage()),
                                                  'error');
            $this->logger->notice(sprintf("ERROR creating label:\n%s\nRequest:\n%s\nResponse:\n%s\n",
                                          $e->getMessage(),
                                          $lastRequest,
                                          $lastResponse));
        }
        
        // $GLOBALS['messageStack']->add_session(sprintf('<pre>%s</pre>', htmlspecialchars(print_r($postData, true))), 'info');
        $redirectUrl = xtc_href_link('admin.php',
                                     'do=Geschaeftskundenversand/PrepareLabel&oID=' . (int)$postData['orders_id']);
        
        return MainFactory::create('RedirectHttpControllerResponse', $redirectUrl);
    }
    
    
    protected function storeCreateShipmentResponse($orders_id, $response)
    {
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $db->insert('gkv_shipments',
                    [
                        'orders_id'      => $orders_id,
                        'shipmentnumber' => (string)$response->CreationState->shipmentNumber,
                        'labelurl'       => (string)$response->CreationState->LabelData->labelUrl,
                        'returnlabelurl' => (string)$response->CreationState->LabelData->returnLabelUrl,
                        'exportlabelurl' => (string)$response->CreationState->LabelData->exportLabelUrl,
                        'codlabelurl'    => (string)$response->CreationState->LabelData->codLabelUrl,
                    ]);
    }
    
    
    protected function getShipments($orders_id)
    {
        $db        = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $shipments = $db->order_by('last_modified', 'DESC')
            ->get_where('gkv_shipments',
                        ['orders_id' => (int)$orders_id])
            ->result_array();
        
        return $shipments;
    }
    
    
    protected function deleteShipment($shipmentnumber)
    {
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $db->delete('gkv_shipments', ['shipmentnumber' => $shipmentnumber]);
        $db->delete('orders_parcel_tracking_codes', ['tracking_code' => $shipmentnumber]);
    }
    
    
    public function actionDeleteLabel()
    {
        $this->_validatePageToken();
        $postData = $this->_getPostDataCollection()->getArray();
        
        $gkvSoapAdapter = Mainfactory::create('GeschaeftskundenversandSoapAdapter', $this->configuration);
        $sc             = $gkvSoapAdapter->getSoapClient();
        try {
            $params = [
                'Version'        => [
                    'majorRelease' => (string)GeschaeftskundenversandConfigurationStorage::MAJOR_VERSION,
                    'minorRelease' => (string)GeschaeftskundenversandConfigurationStorage::MINOR_VERSION,
                ],
                'shipmentNumber' => $postData['shipmentnumber'],
            ];
            $this->logger->notice(sprintf('deleting shipment %s', $postData['shipmentnumber']));
            $response = $sc->deleteShipmentOrder($params);
            if ($response->Status->statusCode == 0) {
                $GLOBALS['messageStack']->add_session($this->languageTextManager->get_text('gkv_label_deleted'),
                                                      'info');
                $this->deleteShipment($postData['shipmentnumber']);
                $this->logger->notice(sprintf("Shipment %s deleted %s/%s/%s",
                                              $postData['shipmentnumber'],
                                              $response->Status->statusCode,
                                              $response->Status->statusText,
                                              $response->Status->statusMessage));
                
                $order_status_id = $this->configuration->get('order_status_after_label');
                $notifyCustomer  = $this->configuration->get('notify_customer');
                if ($order_status_id >= 0) {
                    $order_status_comment = $this->languageTextManager->get_text('gkv_order_status_comment_cancelled')
                                            . ' ' . $postData['shipmentnumber'];
                    $this->setOrderStatus($postData['orders_id'],
                                          $order_status_id,
                                          $order_status_comment,
                                          (bool)$notifyCustomer);
                }
            } else {
                $this->logger->notice("ERROR deleting shipment:\n" . print_r($response, true));
                $errorMessage = $this->languageTextManager->get_text('gkv_cannot_delete_label');
                $errorMessage .= sprintf(' (%s/%s/%s)',
                                         $response->Status->statusCode,
                                         $response->Status->statusText,
                                         $response->Status->statusMessage);
                $GLOBALS['messageStack']->add_session($errorMessage, 'error');
            }
            // $GLOBALS['messageStack']->add_session(sprintf('<pre>%s</pre>', htmlspecialchars(print_r($response, true))), 'info');
        } catch (Exception $sf) {
            $lastRequest  = isset($sc) ? $this->prettyXML($sc->__getLastRequest()) : '-- no request --';
            $lastResponse = isset($sc) ? $this->prettyXML($sc->__getLastResponse()) : '-- no response --';
            
            $errorMessage = $this->languageTextManager->get_text('gkv_cannot_delete_label');
            $errorMessage .= '<br>' . $sf->getMessage();
            $GLOBALS['messageStack']->add_session($errorMessage, 'error');
            $this->logger->notice(sprintf("ERROR/SF deleting shipment:\n%s\nRequest:\n%s\nResponse:\n%s\n",
                                          print_r($sf, true),
                                          $lastRequest,
                                          $lastResponse));
        }
        
        // $GLOBALS['messageStack']->add_session(sprintf('<pre>%s</pre>', htmlspecialchars(print_r($postData, true))), 'info');
        $redirectUrl = xtc_href_link('admin.php',
                                     'do=Geschaeftskundenversand/PrepareLabel&oID=' . (int)$postData['orders_id']);
        
        return MainFactory::create('RedirectHttpControllerResponse', $redirectUrl);
    }
    
    /* ================================================================================================================================= */
    
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
    
    
    protected function prettyXML($xml)
    {
        if (empty($xml)) {
            return '-- NO CONTENT --';
        }
        $doc                     = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput       = true;
        $doc->loadXML($xml);
        
        return $doc->saveXML();
    }
    
    
    /**
     * set order status and (optionally) notify customer by email
     *
     * @param int orders_id
     * @param int orders_status_id
     * @param string  $order_status_comment
     * @param boolean $notifyCustomer
     */
    protected function setOrderStatus($orders_id, $order_status_id, $order_status_comment = '', $notifyCustomer = false)
    {
        $this->logger->notice(sprintf('changing orders status of order %s to %s', $orders_id, $order_status_id));
        $orderWriteService = StaticGXCoreLoader::getService('OrderWrite');
        $orderWriteService->updateOrderStatus(new IdType((int)$orders_id),
                                              new IntType((int)$order_status_id),
                                              new StringType($order_status_comment),
                                              new BoolType($notifyCustomer));
        if ($notifyCustomer === true) {
            $this->logger->notice(sprintf('sending email notification regarding status change of order %s',
                                          $orders_id));
            $this->notifyCustomer($orders_id, $order_status_id, $order_status_comment);
        }
    }
    
    
    /**
     * notify customer of a change in order status
     *
     * This is mostly copypasted from orders.php and MUST be refactored ASAP!
     * refactored to drop use of order_ORIGIN.
     */
    protected function notifyCustomer($orders_id, $orders_status_id, $order_status_comment)
    {
        require_once DIR_FS_INC . 'xtc_php_mail.inc.php';
        
        /** @var OrderReadService $orderReadService */
        $orderReadService  = StaticGXCoreLoader::getService('OrderRead');
        $gxOrder           = $orderReadService->getOrderById(new IdType((int)$orders_id));
        $orderLanguageCode = $gxOrder->getLanguageCode();
        
        /** @var OrderStatusServiceInterface $orderStatusService */
        $orderStatusService = StaticGXCoreLoader::getService('OrderStatus');
        $orderStatus        = $orderStatusService->get(new IntType((int)$orders_status_id));
        $orderStatusName    = $orderStatus->getName($orderLanguageCode);

        /** @var LanguageService $languageService */
        $languageService = LegacyDependencyContainer::getInstance()->get(LanguageService::class);

        $language        = $languageService->getLanguageByCode($gxOrder->getLanguageCode()->asString());
        $orderLanguageId = $language->id();
        $languageName    = $language->directory();
        
        $customerName = $gxOrder->getCustomerAddress()->getFirstname() . ' ' . $gxOrder->getCustomerAddress()
                ->getLastname();
        $orderDate    = $gxOrder->getPurchaseDateTime()->format('Y-m-d H:i:s');
        
        $smarty       = new Smarty();
        $gm_logo_mail = MainFactory::create_object('GMLogoManager', ['gm_logo_mail']);
        if ((bool)$gm_logo_mail->logo_use) {
            $smarty->assign('gm_logo_mail', $gm_logo_mail->get_logo());
        }
        $smarty->assign('language', $languageName);
        $smarty->caching      = false;
        $smarty->template_dir = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeHtmlPath();
        $smarty->config_dir   = DIR_FS_CATALOG . 'lang';
        $smarty->assign('tpl_path', DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeHtmlPath());
        $smarty->assign('logo_path',
                        HTTP_SERVER . DIR_WS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeImagePath());
        $smarty->assign('NAME', $customerName);
        $smarty->assign('GENDER', (string)$gxOrder->getCustomerAddress()->getGender());
        $smarty->assign('ORDER_NR', $orders_id);
        $smarty->assign('ORDER_LINK',
                        xtc_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders_id, 'SSL'));
        $smarty->assign('ORDER_DATE', xtc_date_long($orderDate, $orderLanguageId));
        $smarty->assign('ORDER_STATUS', $orderStatusName);
        if (defined('EMAIL_SIGNATURE')) {
            $smarty->assign('EMAIL_SIGNATURE_HTML', nl2br(EMAIL_SIGNATURE));
            $smarty->assign('EMAIL_SIGNATURE_TEXT', EMAIL_SIGNATURE);
        }
        
        // START Parcel Tracking Code
        /** @var ParcelTrackingCode $coo_parcel_tracking_code_item */
        $coo_parcel_tracking_code_item = MainFactory::create('ParcelTrackingCode');
        /** @var ParcelTrackingCodeReader $coo_parcel_tracking_code_reader */
        $coo_parcel_tracking_code_reader = MainFactory::create('ParcelTrackingCodeReader');
        $t_parcel_tracking_codes_array   = $coo_parcel_tracking_code_reader->getTackingCodeItemsByOrderId($coo_parcel_tracking_code_item,
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
                                                      $orderLanguageId) . ' ' . $orders_id . ', '
                           . xtc_date_long($orderDate, $orderLanguageId) . ', ' . $customerName;
        
        xtc_php_mail(EMAIL_BILLING_ADDRESS,
                     EMAIL_BILLING_NAME,
                     $gxOrder->getCustomerEmail(),
                     $customerName,
                     '',
                     EMAIL_BILLING_REPLY_ADDRESS,
                     EMAIL_BILLING_REPLY_ADDRESS_NAME,
                     '',
                     '',
                     $subject,
                     $html_mail,
                     $txt_mail);
    }
    
    
    public function isWorkDay(DateTime $datetime)
    {
        $holidays        = [
            '01-01', // Neujahr
            '05-01', // Tag der Arbeit
            '10-03', // Tag der dt. Einheit
            '12-25', // 1. Weihnachtstag
            '12-26', // 2. Weihnachtstag
        ];
        $easterTimestamp = static::getEasterDate($datetime->format('Y'));
        $holiday         = new DateTime('@' . $easterTimestamp);
        $holiday->setTimezone(new DateTimeZone('Europe/Berlin'));
        $holidays[] = $holiday->format('m-d'); // Ostersonntag
        $holidays[] = $holiday->sub(new DateInterval('P2D'))->format('m-d'); // Karfreitag
        $holidays[] = $holiday->add(new DateInterval('P3D'))->format('m-d'); // Ostermontag
        $holidays[] = $holiday->add(new DateInterval('P38D'))->format('m-d'); // Himmelfahrt
        $holidays[] = $holiday->add(new DateInterval('P11D'))->format('m-d'); // Pfingstmontag
        
        $isHoliday = in_array($datetime->format('m-d'), $holidays, true);
        $isSunday  = $datetime->format('N') === '7';
        $isWorkday = !$isSunday && !$isHoliday;
        
        return $isWorkday;
    }
    
    
    /**
     * This is a drop-in replacement for easter_date() from ext-calendar for years 2010 to 2037.
     *
     * N.B.: extending this beyond 2037 can cause problems on 32-bit systems.
     *
     * @param string $year
     *
     * @return int
     */
    public static function getEasterDate(string $year): int
    {
        $easterDates = [
            '2010' => 1270332000,
            '2011' => 1303596000,
            '2012' => 1333836000,
            '2013' => 1364684400,
            '2014' => 1397944800,
            '2015' => 1428184800,
            '2016' => 1459033200,
            '2017' => 1492293600,
            '2018' => 1522533600,
            '2019' => 1555797600,
            '2020' => 1586642400,
            '2021' => 1617487200,
            '2022' => 1650146400,
            '2023' => 1680991200,
            '2024' => 1711839600,
            '2025' => 1745100000,
            '2026' => 1775340000,
            '2027' => 1806188400,
            '2028' => 1839448800,
            '2029' => 1869688800,
            '2030' => 1902952800,
            '2031' => 1933797600,
            '2032' => 1964041200,
            '2033' => 1997301600,
            '2034' => 2028146400,
            '2035' => 2058390000,
            '2036' => 2091650400,
            '2037' => 2122495200,
        ];
        if (array_key_exists($year, $easterDates)) {
            return $easterDates[$year];
        }
        throw new \RuntimeException('Easter date is unknown for year ' . $year);
    }
    
    
    public function addWorkdays(DateTime $dateTime, $days)
    {
        $endDate = clone $dateTime;
        $days    = abs((int)$days);
        do {
            $endDate->add(new DateInterval('P1D'));
            if ($this->isWorkDay($endDate) === true) {
                $days--;
            }
        } while ($days > 0);
        
        return $endDate;
    }
}

