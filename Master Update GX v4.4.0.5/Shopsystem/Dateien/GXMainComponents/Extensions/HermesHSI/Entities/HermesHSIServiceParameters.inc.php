<?php
/* --------------------------------------------------------------
   HermesHSIServiceParameters.inc.php 2021-03-30
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/
declare(strict_types=1);

class HermesHSIServiceParameters implements JsonSerializable
{
    /** @var bool */
    protected $limitedQuantitiesService;
    
    /** @var string */
    protected $cashOnDeliveryServiceCurrency;
    
    /** @var float */
    protected $cashOnDeliveryServiceAmount;
    
    /** @var bool */
    protected $bulkGoodService;
    
    /** @var string */
    protected $statedTimeServiceTimeSlot;
    
    /** @var bool */
    protected $householdSignatureService;
    
    /** @var string */
    protected $customerAlertServiceNotificationEmail;
    
    /** @var string */
    protected $customerAlertServiceNotificationNumber;
    
    /** @var string */
    protected $customerAlertServiceNotificationType;
    
    /** @var string */
    protected $parcelShopDeliveryServicePsCustomerFirstName;
    
    /** @var string */
    protected $parcelShopDeliveryServicePsCustomerLastName;
    
    /** @var string */
    protected $parcelShopDeliveryServicePsId;
    
    /** @var bool */
    protected $eveningDeliveryService;
    
    /** @var string */
    protected $identServiceIdentId;
    
    /** @var string */
    protected $identServiceIdentType;
    
    /** @var string */
    protected $statedDayServiceStatedDay;
    
    /** @var bool */
    protected $nextDayService;
    
    
    /**
     * HermesHSIServiceParameters constructor.
     */
    public function __construct()
    {
        $this->limitedQuantitiesService                     = false;
        $this->cashOnDeliveryServiceCurrency                = '';
        $this->cashOnDeliveryServiceAmount                  = 0.0;
        $this->bulkGoodService                              = false;
        $this->statedTimeServiceTimeSlot                    = '';
        $this->householdSignatureService                    = false;
        $this->customerAlertServiceNotificationEmail        = '';
        $this->customerAlertServiceNotificationNumber       = '';
        $this->customerAlertServiceNotificationType         = '';
        $this->parcelShopDeliveryServicePsCustomerFirstName = '';
        $this->parcelShopDeliveryServicePsCustomerLastName  = '';
        $this->parcelShopDeliveryServicePsId                = '';
        $this->eveningDeliveryService                       = false;
        $this->identServiceIdentId                          = '';
        $this->identServiceIdentType                        = '';
        $this->statedDayServiceStatedDay                    = '';
        $this->nextDayService                               = false;
    }
    
    
    /**
     * @return bool
     */
    public function isLimitedQuantitiesService(): bool
    {
        return $this->limitedQuantitiesService;
    }
    
    
    /**
     * @param bool $limitedQuantitiesService
     */
    public function setLimitedQuantitiesService(bool $limitedQuantitiesService): void
    {
        $this->limitedQuantitiesService = $limitedQuantitiesService;
    }
    
    
    /**
     * @return string
     */
    public function getCashOnDeliveryServiceCurrency(): string
    {
        return $this->cashOnDeliveryServiceCurrency;
    }
    
    
    /**
     * @param string $cashOnDeliveryServiceCurrency
     *
     * @throws HermesHSIInvalidDataException
     */
    public function setCashOnDeliveryServiceCurrency(string $cashOnDeliveryServiceCurrency): void
    {
        if (preg_match('/^[A-Z]{3}$/', $cashOnDeliveryServiceCurrency) !== 1) {
            throw new HermesHSIInvalidDataException('invalid currency code');
        }
        $this->cashOnDeliveryServiceCurrency = $cashOnDeliveryServiceCurrency;
    }
    
    
    /**
     * @return float
     */
    public function getCashOnDeliveryServiceAmount(): float
    {
        return $this->cashOnDeliveryServiceAmount;
    }
    
    
    /**
     * @param float $cashOnDeliveryServiceAmount
     *
     * @throws HermesHSIServicesIncompatibleException
     * @throws HermesHSIInvalidDataException
     */
    public function setCashOnDeliveryServiceAmount(float $cashOnDeliveryServiceAmount): void
    {
        if ($cashOnDeliveryServiceAmount > 0 && !empty($this->parcelShopDeliveryServicePsId)) {
            throw new HermesHSIServicesIncompatibleException('COD cannot be used with parcelShopDelivery');
        }
        if ($cashOnDeliveryServiceAmount < 0) {
            throw new HermesHSIInvalidDataException('COD amount cannot be negative');
        }
        $this->cashOnDeliveryServiceAmount = $cashOnDeliveryServiceAmount;
    }
    
    
    /**
     * @return bool
     */
    public function isBulkGoodService(): bool
    {
        return $this->bulkGoodService;
    }
    
    
    /**
     * @param bool $bulkGoodService
     */
    public function setBulkGoodService(bool $bulkGoodService): void
    {
        $this->bulkGoodService = $bulkGoodService;
    }
    
    
    /**
     * @return string
     */
    public function getStatedTimeServiceTimeSlot(): string
    {
        return $this->statedTimeServiceTimeSlot;
    }
    
    
    /**
     * @param string $statedTimeServiceTimeSlot
     *
     * @throws HermesHSIServicesIncompatibleException
     * @throws HermesHSIInvalidDataException
     */
    public function setStatedTimeServiceTimeSlot(string $statedTimeServiceTimeSlot): void
    {
        if (!empty($statedTimeServiceTimeSlot)) {
            if (!empty($this->parcelShopDeliveryServicePsId)) {
                throw new HermesHSIServicesIncompatibleException('statedTime cannot be used with parcelShopDelivery');
            }
            if (isset($this->eveningDeliveryService) && $this->eveningDeliveryService === true) {
                throw new HermesHSIServicesIncompatibleException('statedTime cannot be used with eveningDelivery');
            }
            if (isset($this->nextDayService) && $this->nextDayService === true) {
                throw new HermesHSIServicesIncompatibleException('statedTime cannot be used with eveningDelivery');
            }
            if (empty($this->statedDayServiceStatedDay)) {
                throw new HermesHSIServicesIncompatibleException('statedDayServiceStatedDay must be set before statedTimeServiceTimeSlot');
            }
            if (!in_array($statedTimeServiceTimeSlot, ['FORENOON', 'NOON', 'AFTERNOON', 'EVENING'], true)) {
                throw new HermesHSIInvalidDataException('invalid value for statedTime.timeSlot');
            }
        }
        $this->statedTimeServiceTimeSlot = $statedTimeServiceTimeSlot;
    }
    
    
    /**
     * @return bool
     */
    public function isHouseholdSignatureService(): bool
    {
        return $this->householdSignatureService;
    }
    
    
    /**
     * @param bool $householdSignatureService
     *
     * @throws HermesHSIServicesIncompatibleException
     */
    public function setHouseholdSignatureService(bool $householdSignatureService): void
    {
        if ($householdSignatureService === true) {
            if (!empty($this->parcelShopDeliveryServicePsId)) {
                throw new HermesHSIServicesIncompatibleException('householdSignature cannot be used with parcelShopDelivery');
            }
            if (!empty($this->identServiceIdentType)) {
                throw new HermesHSIServicesIncompatibleException('householdSignature cannot be used with identService');
            }
        }
        $this->householdSignatureService = $householdSignatureService;
    }
    
    
    /**
     * @return string
     */
    public function getCustomerAlertServiceNotificationEmail(): string
    {
        return $this->customerAlertServiceNotificationEmail;
    }
    
    
    /**
     * @param string $customerAlertServiceNotificationEmail
     *
     * @throws HermesHSIInvalidDataException
     */
    public function setCustomerAlertServiceNotificationEmail(string $customerAlertServiceNotificationEmail): void
    {
        $maximumLength = 70;
        if (mb_strlen($customerAlertServiceNotificationEmail) > $maximumLength) {
            throw new HermesHSIInvalidDataException("notification email cannot be more than {$maximumLength} characters");
        }
        if (!filter_var($customerAlertServiceNotificationEmail, FILTER_VALIDATE_EMAIL)) {
            throw new HermesHSIInvalidDataException('notification email must be a valid email address');
        }
        $this->customerAlertServiceNotificationEmail = $customerAlertServiceNotificationEmail;
    }
    
    
    /**
     * @return string
     */
    public function getCustomerAlertServiceNotificationNumber(): string
    {
        return $this->customerAlertServiceNotificationNumber;
    }
    
    
    /**
     * @param string $customerAlertServiceNotificationNumber
     *
     * @throws HermesHSIInvalidDataException
     */
    public function setCustomerAlertServiceNotificationNumber(string $customerAlertServiceNotificationNumber): void
    {
        if (mb_strlen($customerAlertServiceNotificationNumber) > 20) {
            throw new HermesHSIInvalidDataException('notificationNumber maximum length is 20 characters');
        }
        $this->customerAlertServiceNotificationNumber = $customerAlertServiceNotificationNumber;
    }
    
    
    /**
     * @return string
     */
    public function getCustomerAlertServiceNotificationType(): string
    {
        return $this->customerAlertServiceNotificationType;
    }
    
    
    /**
     * @param string $customerAlertServiceNotificationType
     *
     * @throws HermesHSIInvalidDataException
     */
    public function setCustomerAlertServiceNotificationType(string $customerAlertServiceNotificationType): void
    {
        if (!in_array($customerAlertServiceNotificationType, ['SMS', 'EMAIL'], true)) {
            throw new HermesHSIInvalidDataException('customerAlertServiceNotificationType must be SMS or EMAIL');
        }
        $this->customerAlertServiceNotificationType = $customerAlertServiceNotificationType;
    }
    
    
    /**
     * @return string
     */
    public function getParcelShopDeliveryServicePsCustomerFirstName(): string
    {
        return $this->parcelShopDeliveryServicePsCustomerFirstName;
    }
    
    
    /**
     * @param string $parcelShopDeliveryServicePsCustomerFirstName
     *
     * @throws HermesHSIInvalidDataException
     */
    public function setParcelShopDeliveryServicePsCustomerFirstName(string $parcelShopDeliveryServicePsCustomerFirstName
    ): void {
        if (mb_strlen($parcelShopDeliveryServicePsCustomerFirstName) > 20) {
            throw new HermesHSIInvalidDataException('psCustomerFirstName maximum length is 20 characters');
        }
        $this->parcelShopDeliveryServicePsCustomerFirstName = $parcelShopDeliveryServicePsCustomerFirstName;
    }
    
    
    /**
     * @return string
     */
    public function getParcelShopDeliveryServicePsCustomerLastName(): string
    {
        return $this->parcelShopDeliveryServicePsCustomerLastName;
    }
    
    
    /**
     * @param string $parcelShopDeliveryServicePsCustomerLastName
     *
     * @throws HermesHSIInvalidDataException
     */
    public function setParcelShopDeliveryServicePsCustomerLastName(string $parcelShopDeliveryServicePsCustomerLastName
    ): void {
        if (mb_strlen($parcelShopDeliveryServicePsCustomerLastName) > 30) {
            throw new HermesHSIInvalidDataException('psCustomerLastName maximum length is 30 characters');
        }
        $this->parcelShopDeliveryServicePsCustomerLastName = $parcelShopDeliveryServicePsCustomerLastName;
    }
    
    
    /**
     * @return string
     */
    public function getParcelShopDeliveryServicePsId(): string
    {
        return $this->parcelShopDeliveryServicePsId;
    }
    
    
    /**
     * @param string $parcelShopDeliveryServicePsId
     *
     * @throws HermesHSIServicesIncompatibleException
     * @throws HermesHSIInvalidDataException
     */
    public function setParcelShopDeliveryServicePsId(string $parcelShopDeliveryServicePsId): void
    {
        if (!empty($parcelShopDeliveryServicePsId)) {
            if (!empty($this->statedTimeServiceTimeSlot)) {
                throw new HermesHSIServicesIncompatibleException('parcelShopDelivery cannot be used with statedTime');
            }
            if (!empty($this->cashOnDeliveryServiceAmount)) {
                throw new HermesHSIServicesIncompatibleException('parcelShopDelivery cannot be used with cashOnDelivery');
            }
            if (!empty($this->eveningDeliveryService)) {
                throw new HermesHSIServicesIncompatibleException('parcelShopDelivery cannot be used with eveningDelivery');
            }
            if (!empty($this->householdSignatureService)) {
                throw new HermesHSIServicesIncompatibleException('parcelShopDelivery cannot be used with householdSignature');
            }
            if (preg_match('/^\d{10}$/', $parcelShopDeliveryServicePsId) !== 1) {
                throw new HermesHSIInvalidDataException('parcelShopDelivery psID must be 10 digits'); // ???
            }
        }
        $this->parcelShopDeliveryServicePsId = $parcelShopDeliveryServicePsId;
    }
    
    
    /**
     * @return bool
     */
    public function isEveningDeliveryService(): bool
    {
        return $this->eveningDeliveryService;
    }
    
    
    /**
     * @param bool $eveningDeliveryService
     *
     * @throws HermesHSIServicesIncompatibleException
     */
    public function setEveningDeliveryService(bool $eveningDeliveryService): void
    {
        if ($eveningDeliveryService === true) {
            if (!empty($this->statedTimeServiceTimeSlot)) {
                throw new HermesHSIServicesIncompatibleException('eveningDelivery cannot be used with statedTime');
            }
            if (!empty($this->parcelShopDeliveryServicePsId)) {
                throw new HermesHSIServicesIncompatibleException('eveningDelivery cannot be used with parcelShopDelivery');
            }
            if (!empty($this->nextDayService)) {
                throw new HermesHSIServicesIncompatibleException('eveningDelivery cannot be used with nextDay');
            }
        }
        $this->eveningDeliveryService = $eveningDeliveryService;
    }
    
    
    /**
     * @return string
     */
    public function getIdentServiceIdentId(): string
    {
        return $this->identServiceIdentId;
    }
    
    
    /**
     * @param string $identServiceIdentId
     *
     * @throws HermesHSIInvalidDataException
     */
    public function setIdentServiceIdentId(string $identServiceIdentId): void
    {
        if (mb_strlen($identServiceIdentId) > 20) {
            throw new HermesHSIInvalidDataException('identID maximum length is 20 characters');
        }
        $identServiceIdentId       = mb_strtoupper($identServiceIdentId);
        $this->identServiceIdentId = $identServiceIdentId;
    }
    
    
    /**
     * @return string
     */
    public function getIdentServiceIdentType(): string
    {
        return $this->identServiceIdentType;
    }
    
    
    /**
     * @param string $identServiceIdentType
     *
     * @throws HermesHSIServicesIncompatibleException
     * @throws HermesHSIInvalidDataException
     */
    public function setIdentServiceIdentType(string $identServiceIdentType): void
    {
        if (!empty($identServiceIdentType)) {
            if (!empty($this->householdSignatureService)) {
                throw new HermesHSIServicesIncompatibleException('identService cannot be used with householdSignature');
            }
            if (!in_array($identServiceIdentType,
                          ['GERMAN_IDENTITY_CARD', 'GERMAN_PASSPORT', 'INTERNATIONAL_PASSPORT'])) {
                throw new HermesHSIInvalidDataException('identType must be one of GERMAN_IDENTITY_CARD, GERMAN_PASSPORT, INTERNATIONAL_PASSPORT');
            }
        }
        $this->identServiceIdentType = $identServiceIdentType;
    }
    
    
    /**
     * @return string
     */
    public function getStatedDayServiceStatedDay(): string
    {
        return $this->statedDayServiceStatedDay;
    }
    
    
    /**
     * @param string $statedDayServiceStatedDay
     *
     * @throws HermesHSIInvalidDataException
     * @throws HermesHSIServicesIncompatibleException
     */
    public function setStatedDayServiceStatedDay(string $statedDayServiceStatedDay): void
    {
        if (!empty($this->nextDayService)) {
            throw new HermesHSIServicesIncompatibleException('StatedDay cannot be used with NextDay');
        }
        if (preg_match('/^20\d{2}-[01]\d-[0123]\d$/', $statedDayServiceStatedDay) !== 1) {
            throw new HermesHSIInvalidDataException('statedDay must be in YYYY-MM-DD format');
        }
        $this->statedDayServiceStatedDay = $statedDayServiceStatedDay;
    }
    
    
    /**
     * @return bool
     */
    public function isNextDayService(): bool
    {
        return $this->nextDayService;
    }
    
    
    /**
     * @param bool $nextDayService
     *
     * @throws HermesHSIServicesIncompatibleException
     */
    public function setNextDayService(bool $nextDayService): void
    {
        if ($nextDayService === true) {
            if (!empty($this->statedDayServiceStatedDay) || !empty($this->statedTimeServiceTimeSlot)) {
                throw new HermesHSIServicesIncompatibleException('nextDay cannot be used with statedDay/statedTime');
            }
            if (!empty($this->eveningDeliveryService)) {
                throw new HermesHSIServicesIncompatibleException('nextDay cannot be used with statedDay/statedTime');
            }
        }
        $this->nextDayService = $nextDayService;
    }
    
    
    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $jsonArray = [
            'limitedQuantitiesService'  => (bool)$this->limitedQuantitiesService,
            'bulkGoodService'           => (bool)$this->bulkGoodService,
            'householdSignatureService' => (bool)$this->householdSignatureService,
            'eveningDeliveryService'    => (bool)$this->eveningDeliveryService,
            'nextDayService'            => (bool)$this->nextDayService,
        ];
        if ($this->cashOnDeliveryServiceAmount > 0 && !empty($this->cashOnDeliveryServiceCurrency)) {
            $jsonArray['cashOnDeliveryService'] = [
                'currency' => $this->cashOnDeliveryServiceCurrency,
                'amount'   => number_format($this->cashOnDeliveryServiceAmount, 2, '.', ''),
            ];
        }
        if (!empty($this->statedDayServiceStatedDay)) {
            $jsonArray['statedDayService'] = [
                'statedDay' => $this->statedDayServiceStatedDay,
            ];
            if (!empty($this->statedTimeServiceTimeSlot)) {
                $jsonArray['statedTimeService'] = [
                    'timeSlot' => $this->statedTimeServiceTimeSlot,
                ];
            }
        }
        if (!empty($this->customerAlertServiceNotificationType)) {
            if ($this->customerAlertServiceNotificationType === 'EMAIL'
                && !empty($this->customerAlertServiceNotificationEmail)) {
                $jsonArray['customerAlertService'] = [
                    'notificationType'  => $this->customerAlertServiceNotificationType,
                    'notificationEmail' => $this->customerAlertServiceNotificationEmail,
                ];
            }
            if ($this->customerAlertServiceNotificationType === 'SMS'
                && !empty($this->customerAlertServiceNotificationNumber)) {
                $jsonArray['customerAlertService'] = [
                    'notificationType'   => $this->customerAlertServiceNotificationType,
                    'notificationNumber' => $this->customerAlertServiceNotificationNumber,
                ];
            }
        }
        if (!empty($this->parcelShopDeliveryServicePsId)) {
            $jsonArray['parcelShopDeliveryService'] = [
                'psCustomerFirstName' => (string)$this->parcelShopDeliveryServicePsCustomerFirstName,
                'psCustomerLastName'  => (string)$this->parcelShopDeliveryServicePsCustomerLastName,
                'psID'                => $this->parcelShopDeliveryServicePsId,
            ];
        }
        if (!empty($this->identServiceIdentId) && !empty($this->identServiceIdentType)) {
            $jsonArray['identService'] = [
                'identID' => $this->identServiceIdentId,
                'identType' => $this->identServiceIdentType,
            ];
        }
        
        return $jsonArray;
    }
}
