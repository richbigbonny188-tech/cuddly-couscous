<?php
/* --------------------------------------------------------------
   HermesHSIController.inc.php 2021-03-30
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/
declare(strict_types=1);

class HermesHSIController extends AdminHttpViewController
{
    /** @var LanguageTextManager */
    protected $languageTextManager;
    
    /** @var HermesHSIConfigurationStorage */
    protected $configuration;
    
    /** @var HermesHSILogger */
    protected $logger;
    
    
    public function proceed(HttpContextInterface $httpContext)
    {
        $this->languageTextManager = MainFactory::create('LanguageTextManager', 'module_center_module');
        $this->configuration       = MainFactory::create('HermesHSIConfigurationStorage');
        $this->logger              = MainFactory::create('HermesHSILogger');
        parent::proceed($httpContext);
    }
    
    
    public function actionDefault()
    {
        return parent::actionDefault();
    }
    
    
    public function actionPrepareLabel()
    {
        $ordersId = new IdType((int)$this->_getQueryParameter('oID'));
        if (($downloadLabelShipmentOrderId = $this->_getQueryParameter('downloadLabel')) !== null) {
            $downloadLabelUrl = xtc_href_link('admin.php',
                                      'do=HermesHSI/RetrieveLabel&shipmentOrderId=' . $downloadLabelShipmentOrderId);
            header('Refresh: 1;url=' . $downloadLabelUrl);
        }
        /** @var OrderReadService $orderReadService */
        $orderReadService = StaticGXCoreLoader::getService('OrderRead');
        $order            = $orderReadService->getOrderById($ordersId);
        switch ($order->getDeliveryAddress()->getGender()) {
            case 'm':
                $receiverGender = 'M';
                break;
            case 'f':
                $receiverGender = 'W';
                break;
            default:
                $receiverGender = 'O';
        }
        
        $parcelWeight = 0;
        if ($this->configuration->get('parcelWeightMode') === 'products_weight') {
            $parcelWeight = 1000 * $order->getTotalWeight();
        } elseif ($this->configuration->get('parcelWeightMode') === 'add_packing_weight') {
            $parcelWeight  = 1000 * $order->getTotalWeight();
            $packingWeight = max(1000 * (float)@constant('SHIPPING_BOX_WEIGHT'),
                                 $parcelWeight * ((float)@constant('SHIPPING_BOX_PADDING') / 100));
            $parcelWeight  += $packingWeight;
        }
        
        $codAmount       = 0;
        $orderTotalValue = 0;
        foreach ($order->getOrderTotals() as $orderTotal) {
            if ($orderTotal->getClass() === 'ot_total') {
                $orderTotalValue = $orderTotal->getValue();
            }
        }
        if (stripos($order->getPaymentType()->getPaymentClass(), 'cod') !== false) {
            $codAmount = $orderTotalValue;
        }
    
        $transportConditions = $this->getTransportConditions($ordersId);
        $notificationAllowed = ($transportConditions === 'accepted' || $transportConditions === 'unshown');
        $notificationEmail   = $notificationAllowed ? $order->getCustomerEmail() : '';
    
        $template                 = $this->getTemplateFile('hermeshsi_form.html');
        $title                    = new NonEmptyStringType($this->languageTextManager->get_text('hermeshsi_create_order_title'));
        $formData = [
            'ordersId'                        => $ordersId->asInt(),
            'senderNameGender'                => $this->configuration->get('senderNameGender'),
            'senderNameTitle'                 => $this->configuration->get('senderNameTitle'),
            'senderNameFirstname'             => $this->configuration->get('senderNameFirstname'),
            'senderNameMiddlename'            => $this->configuration->get('senderNameMiddlename'),
            'senderNameLastname'              => $this->configuration->get('senderNameLastname'),
            'senderAddressStreet'             => $this->configuration->get('senderAddressStreet'),
            'senderAddressHouseNumber'        => $this->configuration->get('senderAddressHouseNumber'),
            'senderAddressZipCode'            => $this->configuration->get('senderAddressZipCode'),
            'senderAddressTown'               => $this->configuration->get('senderAddressTown'),
            'senderAddressCountryCode'        => $this->configuration->get('senderAddressCountryCode'),
            'senderAddressAddressAddition'    => $this->configuration->get('senderAddressAddressAddition'),
            'senderAddressAddressAddition2'   => $this->configuration->get('senderAddressAddressAddition2'),
            'senderAddressAddressAddition3'   => $this->configuration->get('senderAddressAddressAddition3'),
            'receiverNameGender'              => $receiverGender,
            'receiverNameTitle'               => '',
            'receiverNameFirstname'           => (string)$order->getDeliveryAddress()->getFirstname(),
            'receiverNameMiddlename'          => '',
            'receiverNameLastname'            => (string)$order->getDeliveryAddress()->getLastname(),
            'receiverAddressStreet'           => (string)$order->getDeliveryAddress()->getStreet(),
            'receiverAddressHouseNumber'      => (string)$order->getDeliveryAddress()->getHouseNumber(),
            'receiverAddressZipCode'          => (string)$order->getDeliveryAddress()->getPostcode(),
            'receiverAddressTown'             => (string)$order->getDeliveryAddress()->getCity(),
            'receiverAddressCountryCode'      => (string)$order->getDeliveryAddress()->getCountry()->getIso2(),
            'receiverAddressAddressAddition'  => (string)$order->getDeliveryAddress()->getCompany(),
            'receiverAddressAddressAddition2' => (string)$order->getDeliveryAddress()->getAdditionalAddressInfo(),
            'receiverAddressAddressAddition3' => '',
            'receiverContactMail'             => $notificationEmail,
            'receiverContactMobile'           => '',
            'receiverContactPhone'            => '',
            'parcelClass'                     => 'S',
            'parcelWidth'                     => '0',
            'parcelHeight'                    => '0',
            'parcelDepth'                     => '0',
            'parcelWeight'                    => (string)$parcelWeight,
            'codAmount'                       => $codAmount,
            'notificationEmail'               => $notificationEmail,
        ];
        $contactRequiredCountries = ['DK', 'SE',];
        if (in_array((string)$order->getDeliveryAddress()->getCountry()->getIso2(), $contactRequiredCountries, true)) {
            $formData['receiverContactMail']  = $order->getCustomerEmail();
            $formData['receiverContactPhone'] = $order->getCustomerTelephone();
        }
        $maxLengthMap    = [
            'receiverNameTitle'               => 20,
            'receiverNameFirstname'           => 20,
            'receiverNameMiddlename'          => 30,
            'receiverNameLastname'            => 30,
            'receiverAddressStreet'           => 27,
            'receiverAddressHouseNumber'      => 5,
            'receiverAddressZipCode'          => 8,
            'receiverAddressTown'             => 30,
            'receiverAddressAddressAddition'  => 20,
            'receiverAddressAddressAddition2' => 20,
            'receiverAddressAddressAddition3' => 20,
        ];
        $shortenedValues = [];
        foreach ($maxLengthMap as $fieldName => $maxLength) {
            if (mb_strlen($formData[$fieldName]) > $maxLength) {
                $shortenedValues[$fieldName] = $formData[$fieldName];
                $formData[$fieldName]        = mb_substr($formData[$fieldName], 0, $maxLength);
            }
        }
        
        $genderOptions = [
            ['value' => 'O', 'label' => $this->languageTextManager->get_text('hermeshsi_sender_gender_o')],
            ['value' => 'W', 'label' => $this->languageTextManager->get_text('hermeshsi_sender_gender_w')],
            ['value' => 'M', 'label' => $this->languageTextManager->get_text('hermeshsi_sender_gender_m')],
        ];
        $parcelClasses = [
            ['value' => 'NONE', 'label' => $this->languageTextManager->get_text('hermeshsi_parcel_class_none')],
            ['value' => 'XS', 'label' => $this->languageTextManager->get_text('hermeshsi_parcel_class_xs')],
            ['value' => 'S', 'label' => $this->languageTextManager->get_text('hermeshsi_parcel_class_s')],
            ['value' => 'M', 'label' => $this->languageTextManager->get_text('hermeshsi_parcel_class_m')],
            ['value' => 'L', 'label' => $this->languageTextManager->get_text('hermeshsi_parcel_class_l')],
            ['value' => 'XL', 'label' => $this->languageTextManager->get_text('hermeshsi_parcel_class_xl')],
        ];
        $parcelPresets = [
            'preset1' => [
                'height' => $this->configuration->get('parcelDimensionPreset1Height'),
                'width'  => $this->configuration->get('parcelDimensionPreset1Width'),
                'depth'  => $this->configuration->get('parcelDimensionPreset1Depth'),
            ],
            'preset2' => [
                'height' => $this->configuration->get('parcelDimensionPreset2Height'),
                'width'  => $this->configuration->get('parcelDimensionPreset2Width'),
                'depth'  => $this->configuration->get('parcelDimensionPreset2Depth'),
            ],
            'preset3' => [
                'height' => $this->configuration->get('parcelDimensionPreset3Height'),
                'width'  => $this->configuration->get('parcelDimensionPreset3Width'),
                'depth'  => $this->configuration->get('parcelDimensionPreset3Depth'),
            ],
            'preset4' => [
                'height' => $this->configuration->get('parcelDimensionPreset4Height'),
                'width'  => $this->configuration->get('parcelDimensionPreset4Width'),
                'depth'  => $this->configuration->get('parcelDimensionPreset4Depth'),
            ],
            'preset5' => [
                'height' => $this->configuration->get('parcelDimensionPreset5Height'),
                'width'  => $this->configuration->get('parcelDimensionPreset5Width'),
                'depth'  => $this->configuration->get('parcelDimensionPreset5Depth'),
            ],
        ];
        /** @var CountryService $countryService */
        $countryService  = StaticGXCoreLoader::getService('Country');
        $shopCountry     = $countryService->getCountryById(new IdType(STORE_COUNTRY));
        $locale          = $_SESSION['language_code'] . '_' . $shopCountry->getIso2();
        $countries       = array_merge(HermesHSICountriesHelper::getCountries(true, $locale),
                                       HermesHSICountriesHelper::getCountries(false, $locale));
        $selectCountries = array_map(static function ($countryRow) {
            return ['value' => $countryRow['iso2'], 'label' => $countryRow['name']];
        },
            $countries);
        
        $hsiService = MainFactory::create('HermesHSIService', $this->configuration);
        $hsiService->setLogger($this->logger);
        $shipmentOrderRepository = MainFactory::create('HermesHSIShipmentsRepository',
                                                       (bool)$this->configuration->get('testMode'));
        $existingShipmentOrders  = $shipmentOrderRepository->retrieveAllShipmentIdsForOrder($ordersId);
        $shipmentOrdersData = [];
        if (!empty($existingShipmentOrders)) {
            try {
                $shipmentOrders     = $hsiService->retrieveShipmentOrders($existingShipmentOrders);
                $shipmentOrdersData = array_map(static function ($shipmentOrdersId) use ($shipmentOrders) {
                    $labelUrl = xtc_href_link('admin.php',
                                              'do=HermesHSI/RetrieveLabel&shipmentOrderId=' . $shipmentOrdersId);
        
                    return [
                        'shipment_id' => $shipmentOrdersId,
                        'labelUrl'    => $labelUrl,
                        'status'      => $shipmentOrders[$shipmentOrdersId] ?? false,
                    ];
                },
                    $existingShipmentOrders);
            } catch (HermesHSIAuthenticationFailedException $e) {
                /** @var messageStack_ORIGIN $messageStack */
                $messageStack = $GLOBALS['messageStack'];
                $messageStack->add($this->languageTextManager->get_text('hermeshsi_authentication_failed'));
            }
        }
        
        $templateData = MainFactory::create('KeyValueCollection',
                                            [
                                                'pageToken'              => $_SESSION['coo_page_token']->generate_token(),
                                                'saveOrderFormActionUrl' => xtc_href_link('admin.php',
                                                                                          'do=HermesHSI/SaveOrder'),
                                                'formData'               => $formData,
                                                'shortenedValues'        => $shortenedValues,
                                                'genderOptions'          => $genderOptions,
                                                'parcelClasses'          => $parcelClasses,
                                                'countries'              => $selectCountries,
                                                'shipmentOrders'         => $shipmentOrdersData,
                                                'labelDownloadMethod'    => $this->configuration->get('labelDownloadMethod'),
                                                'orderCurrency'          => $order->getCurrencyCode()->getCode(),
                                                'parcelPresets'          => $parcelPresets,
                                            ]);
        
        $assets = MainFactory::create('AssetCollection');
        /*
        $assets->add(MainFactory::create('Asset',
                                         DIR_WS_CATALOG
                                         . 'admin/html/assets/styles/modules/hermeshsi.min.css'));
        */
        $assets->add(MainFactory::create('Asset',
                                         DIR_WS_CATALOG
                                         . 'admin/html/assets/javascript/modules/hermeshsi/hermeshsi-form.min.js'));
        
        return MainFactory::create('AdminLayoutHttpControllerResponse', $title, $template, $templateData, $assets);
    }
    
    
    /**
     * @return bool|mixed|RedirectHttpControllerResponse
     * @throws HermesHSIInvalidDataException
     */
    public function actionSaveOrder()
    {
        $postData = $this->_getPostDataCollection();
        $ordersId = new IdType((int)$postData->getValue('ordersId'));
        $lastName = new NonEmptyStringType($postData->getValue('senderNameLastname'));
        
        /** @var HermesHSIName $senderName */
        $senderName = MainFactory::create('HermesHSIName', $lastName);
        $senderName->setGender($postData->getValue('senderNameGender'));
        $senderName->setTitle($postData->getValue('senderNameTitle'));
        $senderName->setFirstname($postData->getValue('senderNameFirstname'));
        $senderName->setMiddlename($postData->getValue('senderNameMiddlename'));
        /** @var HermesHSIAddress $senderAddress */
        $senderAddress = MainFactory::create('HermesHSIAddress',
                                             new NonEmptyStringType($postData->getValue('senderAddressStreet')),
                                             new NonEmptyStringType($postData->getValue('senderAddressHouseNumber')),
                                             new NonEmptyStringType($postData->getValue('senderAddressZipCode')),
                                             new NonEmptyStringType($postData->getValue('senderAddressTown')));
        $senderAddress->setCountryCode($postData->getValue('senderAddressCountryCode'));
        $senderAddress->setAddressAddition($postData->getValue('senderAddressAddressAddition'));
        $senderAddress->setAddressAddition2($postData->getValue('senderAddressAddressAddition2'));
        $senderAddress->setAddressAddition3($postData->getValue('senderAddressAddressAddition3'));
        
        /** @var HermesHSIName $receiverName */
        $receiverName = MainFactory::create('HermesHSIName',
                                            new NonEmptyStringType($postData->getValue('receiverNameLastname')));
        $receiverName->setGender($postData->getValue('receiverNameGender'));
        $receiverName->setTitle($postData->getValue('receiverNameTitle'));
        $receiverName->setFirstname($postData->getValue('receiverNameFirstname'));
        $receiverName->setMiddlename($postData->getValue('receiverNameMiddlename'));
        /** @var HermesHSIAddress $receiverAddress */
        $receiverAddress = MainFactory::create('HermesHSIAddress',
                                               new NonEmptyStringType($postData->getValue('receiverAddressStreet')),
                                               new StringType($postData->getValue('receiverAddressHouseNumber')),
                                               new NonEmptyStringType($postData->getValue('receiverAddressZipCode')),
                                               new NonEmptyStringType($postData->getValue('receiverAddressTown')));
        $receiverAddress->setCountryCode($postData->getValue('receiverAddressCountryCode'));
        $receiverAddress->setAddressAddition($postData->getValue('receiverAddressAddressAddition'));
        $receiverAddress->setAddressAddition2($postData->getValue('receiverAddressAddressAddition2'));
        $receiverAddress->setAddressAddition3($postData->getValue('receiverAddressAddressAddition3'));
        
        /** @var HermesHSIContact $receiverContact */
        $receiverContact = MainFactory::create('HermesHSIContact');
        $receiverContact->setMail($postData->getValue('receiverContactMail'));
        $receiverContact->setMobile($postData->getValue('receiverContactMobile'));
        $receiverContact->setPhone($postData->getValue('receiverContactPhone'));
        
        /** @var HermesHSIParcel $parcel */
        $parcel = MainFactory::create('HermesHSIParcel');
        $parcel->setParcelClass($postData->getValue('parcelClass'));
        $parcel->setParcelWidth((int)$postData->getValue('parcelWidth'));
        $parcel->setParcelHeight((int)$postData->getValue('parcelHeight'));
        $parcel->setParcelDepth((int)$postData->getValue('parcelDepth'));
        $parcel->setParcelWeight((int)$postData->getValue('parcelWeight'));
        
        /** @var HermesHSIServiceParameters $serviceParameters */
        $serviceParameters = MainFactory::create('HermesHSIServiceParameters');
        $codAmount         = $postData->getValue('serviceCodAmount');
        if (!empty($codAmount)) {
            try {
                $serviceParameters->setCashOnDeliveryServiceAmount((float)$codAmount);
                $serviceParameters->setCashOnDeliveryServiceCurrency($postData->getValue('serviceCodCurrency'));
                $notificationEmail = (string)$postData->getValue('notificationEmail');
                if (!empty($notificationEmail)) {
                    $serviceParameters->setCustomerAlertServiceNotificationType('EMAIL');
                    $serviceParameters->setCustomerAlertServiceNotificationEmail($notificationEmail);
                }
            } catch (HermesHSIInvalidDataException $e) {
            } catch (HermesHSIServicesIncompatibleException $e) {
            }
        }

        /** @var HermesHSIShipmentOrder $shipmentOrder */
        $shipmentOrder = MainFactory::create('HermesHSIShipmentOrder',
                                             $receiverAddress,
                                             $receiverName,
                                             $senderAddress,
                                             $senderName,
                                             $parcel,
                                             $serviceParameters);
        $shipmentOrder->setReceiverContact($receiverContact);
        $shipmentOrder->setClientReference((string)$ordersId->asInt());
        
        /** @var messageStack_ORIGIN $messageStack */
        $messageStack = $GLOBALS['messageStack'];
        
        /** @var HermesHSIService $hsiService */
        $hsiService = MainFactory::create('HermesHSIService', $this->configuration);
        $hsiService->setLogger($this->logger);
        try {
            $shipmentOrderId = $hsiService->createShipmentOrder($shipmentOrder);
            $message         = $this->languageTextManager->get_text('hermeshsi_created_shipment_order');
            $messageStack->add_session($message, 'info');
            $shipmentRepository = MainFactory::create('HermesHSIShipmentsRepository',
                                                      (bool)$this->configuration->get('testMode'));
            $shipmentRepository->storeShipment($ordersId, $shipmentOrderId);
        
            if ($this->configuration->get('orderStatusAfterSave') >= 0) {
                /** @var OrderWriteService $orderWriteService */
                $orderWriteService = StaticGXCoreLoader::getService('OrderWrite');
                $newOrderStatus    = new IdType((int)$this->configuration->get('orderStatusAfterSave'));
                $customerNotified  = false;
                $orderWriteService->updateOrderStatus($ordersId,
                                                      $newOrderStatus,
                                                      new StringType($message),
                                                      new BoolType($customerNotified));
            }
            if ((bool)$this->configuration->get('directDownload')) {
                try {
                    $hsiLabel = $this->retrieveLabel($shipmentOrderId, $ordersId);
                } catch (Exception $e) {
                    // pass
                }
            }
        } catch (HermesHSIAuthenticationFailedException $e) {
            $messageStack->add_session('authentication error: ' . $e->getMessage(), 'error');
        } catch (HermesHSIInvalidDataException $e) {
            $messageStack->add_session('invalid data error: ' . $e->getMessage(), 'error');
        } catch (HermesHSIOrderException $e) {
            $messageStack->add_session('order error: ' . $e->getMessage(), 'error');
        } catch (RestTimeoutException $e) {
            $messageStack->add_session('communication error: ' . $e->getMessage(), 'error');
        } catch (RestException $e) {
            $messageStack->add_session('communication error: ' . $e->getMessage(), 'error');
        }
    
        $prepareOrderUrl = xtc_href_link('admin.php',
                                         'do=HermesHSI/PrepareLabel&oID=' . $postData->getValue('ordersId'));
        if (isset($shipmentOrderId) && (bool)$this->configuration->get('directDownload')) {
            $prepareOrderUrl .= '&downloadLabel=' . $shipmentOrderId;
        }
        
        return MainFactory::create('RedirectHttpControllerResponse', $prepareOrderUrl);
    }
    
    
    /**
     * @return HttpControllerResponseInterface
     * @throws HermesHSIShipmentNotFoundException
     */
    public function actionRetrieveLabel(): HttpControllerResponseInterface
    {
        $pageContent     = '';
        $shipmentOrderId = (string)$this->_getQueryParameter('shipmentOrderId');
        if (empty($shipmentOrderId)) {
            throw new \RuntimeException('required parameter missing');
        }
        $shipmentsRepository = MainFactory::create('HermesHSIShipmentsRepository',
                                                   $this->configuration->get('testMode'));
        $ordersId            = $shipmentsRepository->findOrdersIdByShipmentOrderId($shipmentOrderId);
        $hsiService          = MainFactory::create('HermesHSIService', $this->configuration);
        $hsiService->setLogger($this->logger);
        try {
            $hsiLabel = $this->retrieveLabel($shipmentOrderId, $ordersId);
            ob_clean();
            header('Content-Type: application/pdf');
            if ($this->configuration->get('labelDownloadMethod') === 'inline') {
                header('Content-Disposition: inline; filename="HermesHSI_' . $shipmentOrderId . '.pdf"');
            } else {
                header('Content-Description: Hermes Label');
                header('Content-Disposition: attachment; filename="HermesHSI_' . $shipmentOrderId . '.pdf"');
            }
            if ((bool)ini_get('zlib.output_compression') === true) {
                header('Content-Encoding: gzip');
            } else {
                header('Content-Length: ' . strlen($hsiLabel->getLabelData()));
            }
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            echo $hsiLabel->getLabelData();
        } catch (HermesHSIAuthenticationFailedException $e) {
            $pageContent = $this->languageTextManager->get_text('hermeshsi_authentication_failed');
        } catch (HermesHSIInvalidDataException $e) {
            $pageContent = $this->languageTextManager->get_text('hermeshsi_cannot_retrieve_label') . ' ('
                           . $e->getMessage() . ')';
        } catch (HermesHSILabelException $e) {
            $pageContent = $this->languageTextManager->get_text('hermeshsi_cannot_retrieve_label') . ' ('
                           . $e->getMessage() . ')';
        } catch (RestTimeoutException $e) {
            $pageContent = $this->languageTextManager->get_text('hermeshsi_cannot_retrieve_label') . ' ('
                           . $e->getMessage() . ')';
        } catch (RestException $e) {
            $pageContent = $this->languageTextManager->get_text('hermeshsi_cannot_retrieve_label') . ' ('
                           . $e->getMessage() . ')';
        } catch (Exception $e) {
            $pageContent = $this->languageTextManager->get_text('hermeshsi_cannot_retrieve_label') . ' ('
                           . $e->getMessage() . ')';
        }
    
        return MainFactory::create('HttpControllerResponse', $pageContent);
    }

    /*
     * Helper methods
     */
    
    /**
     * @param string $shipmentOrderId
     *
     * @param IdType $ordersId
     *
     * @return HermesHSILabel
     * @throws HermesHSIAuthenticationFailedException
     * @throws HermesHSIInvalidDataException
     * @throws HermesHSILabelException
     * @throws RestException
     * @throws RestTimeoutException
     */
    protected function retrieveLabel(string $shipmentOrderId, IdType $ordersId): HermesHSILabel
    {
        $hsiService          = MainFactory::create('HermesHSIService', $this->configuration);
        $hsiService->setLogger($this->logger);
        $hsiLabel = $hsiService->getLabel((string)$shipmentOrderId);
        if (($hsiLabel->isFromLocalStorage() === false)) {
            if ($this->configuration->get('orderStatusAfterPrint') >= 0) {
                /** @var OrderWriteService $orderWriteService */
                $orderWriteService = StaticGXCoreLoader::getService('OrderWrite');
                $newOrderStatus    = new IdType((int)$this->configuration->get('orderStatusAfterPrint'));
                $customerNotified  = false;
                $message           = $this->languageTextManager->get_text('hermeshsi_label_printed');
                $orderWriteService->updateOrderStatus($ordersId,
                                                      $newOrderStatus,
                                                      new StringType($message),
                                                      new BoolType($customerNotified));
            }
        
            if ($this->configuration->get('parcelServiceId') >= 0) {
                $shipmentOrder = $hsiService->retrieveShipmentOrder($shipmentOrderId);
                if (isset($shipmentOrder['listOfClients'][0]['listOfSenders'][0]['listOfShipmentOrders'][0]['shipmentID'])) {
                    $shipmentId               = $shipmentOrder['listOfClients'][0]['listOfSenders'][0]['listOfShipmentOrders'][0]['shipmentID'];
                    $parcelServiceReader      = MainFactory::create('ParcelServiceReader');
                    $parcelTrackingCodeWriter = MainFactory::create('ParcelTrackingCodeWriter');
                    $parcelTrackingCodeWriter->insertTrackingCode($ordersId->asInt(),
                                                                  $shipmentId,
                                                                  (int)$this->configuration->get('parcelServiceId'),
                                                                  $parcelServiceReader);
                } else {
                    $this->logger->debug('shipment ID for tracking not found');
                }
            }
        }
        return $hsiLabel;
    }
    
    protected function getTransportConditions(IdType $orderId)
    {
        try {
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
    
    
}
