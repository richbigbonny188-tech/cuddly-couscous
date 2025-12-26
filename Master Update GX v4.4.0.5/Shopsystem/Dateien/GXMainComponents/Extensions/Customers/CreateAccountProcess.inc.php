<?php
/* --------------------------------------------------------------
   CreateAccountProcess.inc.php 2021-01-27
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2017 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

require_once DIR_FS_INC . 'xtc_write_user_info.inc.php';

MainFactory::load_class('AbstractCreateAccountProcess');


/**
 * Class CreateAccountProcess
 *
 * @category   System
 * @package    Extensions
 * @subpackage Customers
 */
class CreateAccountProcess extends AbstractCreateAccountProcess
{
    /**
     * @var string $giftCode
     */
    protected $giftCode = '';
    
    
    protected function _saveRegistree()
    {
        $this->_prepareCustomerArray();
        $addressBlock = $this->_createAddressBlock();
        
        /** @var AuthService $authService */
        $authService = StaticGXCoreLoader::getService('Auth');
        try {
            $hashedPassword   = $authService->getHash(new StringType($this->customerCollection->getValue('password')));
            $customerPassword = MainFactory::create('CustomerHashedPassword',
                                                    new NonEmptyStringType($hashedPassword));
            $isGuest          = false;
        } catch (InvalidArgumentException $e) { // no 'password' in $customerCollection => guest account
            $customerPassword = MainFactory::create('CustomerGuestPassword', '', true);
            $isGuest          = true;
        }
        
        $customer = $this->customerWriteService->createNewRegistree(MainFactory::create('CustomerEmail',
                                                                                        $this->customerCollection->getValue('email_address')),
                                                                    $customerPassword,
                                                                    MainFactory::create('CustomerDateOfBirth',
                                                                                        $this->_formatDateOfBirth($this->customerCollection->getValue('dob'))),
                                                                    MainFactory::create('CustomerVatNumber',
                                                                                        $this->customerCollection->getValue('vat')),
                                                                    MainFactory::create('CustomerCallNumber',
                                                                                        $this->customerCollection->getValue('telephone')),
                                                                    MainFactory::create('CustomerCallNumber',
                                                                                        $this->customerCollection->getValue('fax')),
                                                                    $addressBlock,
                                                                    $this->customerCollection->getValue('addon_values'));
        
        $this->customerCollection->setValue('customer_id', $customer->getId());
        $this->customerCollection->setValue('default_address_id', $customer->getDefaultAddress()->getId());
        $this->customerCollection->setValue('zone_id', $customer->getDefaultAddress()->getCountryZone()->getId());
        $this->customerCollection->setValue('account_type', $isGuest ? '1' : '0');
    }
    
    
    /**
     * @deprecated guest accounts can be created by _saveRegistree() if $customerCollection does not contain a password
     */
    protected function _saveGuest()
    {
        $this->_prepareCustomerArray();
        $addressBlock = $this->_createAddressBlock();
        $customer     = $this->customerWriteService->createNewGuest(MainFactory::create('CustomerEmail',
                                                                                        $this->customerCollection->getValue('email_address')),
                                                                    MainFactory::create('CustomerDateOfBirth',
                                                                                        $this->_formatDateOfBirth($this->customerCollection->getValue('dob'))),
                                                                    MainFactory::create('CustomerVatNumber',
                                                                                        $this->customerCollection->getValue('vat')),
                                                                    MainFactory::create('CustomerCallNumber',
                                                                                        $this->customerCollection->getValue('telephone')),
                                                                    MainFactory::create('CustomerCallNumber',
                                                                                        $this->customerCollection->getValue('fax')),
                                                                    $addressBlock,
                                                                    $this->customerCollection->getValue('addon_values'));
        
        $this->customerCollection->setValue('customer_id', $customer->getId());
        $this->customerCollection->setValue('default_address_id', $customer->getDefaultAddress()->getId());
        $this->customerCollection->setValue('zone_id', $customer->getDefaultAddress()->getCountryZone()->getId());
        $this->customerCollection->setValue('account_type', '1');
    }
    
    
    protected function _prepareCustomerArray()
    {
        $countryZones = $this->countryService->findCountryZonesByCountryId(new IdType($this->customerCollection->getValue('country')));
        
        $this->customerCollection->setValue('entry_state_has_zones', false);
        
        if (!empty($countryZones)) {
            $this->customerCollection->setValue('entry_state_has_zones', true);
        }
        
        $zonesArray = [];
        
        /* @var CustomerCountryZone $countryZone */
        foreach ($countryZones as $countryZone) {
            if ($countryZone->getName() === $this->customerCollection->getValue('state')) {
                $this->customerCollection->setValue('state', $countryZone->getId());
            }
            
            $zonesArray[] = [
                'id'   => $countryZone->getId(),
                'text' => $countryZone->getName()
            ];
        }
        
        if (!empty($zonesArray)) {
            $this->customerCollection->setValue('zones_array', $zonesArray);
        }
    }
    
    
    /**
     * @return bool true if account will be created as guest account
     */
    protected function _validateRegistree()
    {
        /** @var CustomerRegistrationInputValidatorService $inputValidatorService */
        $inputValidatorService = StaticGXCoreLoader::getService('RegistrationInputValidator');
        
        $inputArray = $this->customerCollection->getArray();
        $isGuest    = in_array(ACCOUNT_OPTIONS, ['guest', 'both'], true)
                      && empty($inputArray['password'])
                      && empty($inputArray['confirmation']);
        if ($isGuest) {
            $inputValidatorService->validateGuestDataByArray($inputArray);
        } else {
            $inputValidatorService->validateCustomerDataByArray($inputArray);
        }
        
        if (gm_get_conf('GM_CREATE_ACCOUNT_VVCODE') === 'true'
            && !$inputValidatorService->validateCaptcha($this->customerCollection->getArray())) {
            $exception = MainFactory::create('InvalidCustomerDataException', 'captcha is not valid');
            $exception->setErrorMessageCollection($inputValidatorService->getErrorMessageCollection());
            
            throw $exception;
        }
        
        if ($inputValidatorService->getErrorStatus()) {
            $exception = MainFactory::create('InvalidCustomerDataException', 'customer data is not valid');
            $exception->setErrorMessageCollection($inputValidatorService->getErrorMessageCollection());
            
            throw $exception;
        }
        
        return $isGuest;
    }
    
    
    /**
     * @throws InvalidCustomerDataException
     */
    protected function _validateGuest()
    {
        /** @var CustomerRegistrationInputValidatorService $inputValidatorService */
        $inputValidatorService = StaticGXCoreLoader::getService('RegistrationInputValidator');
        
        $inputValidatorService->validateGuestDataByArray($this->customerCollection->getArray());
        
        if ($inputValidatorService->getErrorStatus()) {
            $exception = MainFactory::create('InvalidCustomerDataException', 'customer data is not valid');
            $exception->setErrorMessageCollection($inputValidatorService->getErrorMessageCollection());
            
            throw $exception;
        }
    }
    
    
    /**
     * @return AddressBlock
     */
    protected function _createAddressBlock()
    {
        /** @var CountryService $countryService */
        $coo_country_service = StaticGXCoreLoader::getService('Country');
        $isStateMandatory    = $coo_country_service->isStateMandatory(new IdType($this->customerCollection->getValue('country')));
        $country             = $coo_country_service->getCountryById(new IdType($this->customerCollection->getValue('country')));
        $entryStateHasZones  = $coo_country_service->countryHasCountryZones($country);
        
        if ($isStateMandatory || (ACCOUNT_STATE === 'true' && $entryStateHasZones)) {
            
            $countryZoneId     = xtc_db_prepare_input($this->customerCollection->getValue('state'));
            $countryZone       = $coo_country_service->getCountryZoneById(new IdType($countryZoneId));
            $countryZoneExists = $coo_country_service->countryZoneExistsInCountry($countryZone, $country);
            
            if (!$countryZoneExists) {
                $countryZone = MainFactory::create('CustomerCountryZone',
                                                   new IdType(0),
                                                   MainFactory::create('CustomerCountryZoneName', ''),
                                                   MainFactory::create('CustomerCountryZoneIsoCode', ''));
            }
        } else {
            $countryZone = MainFactory::create('CustomerCountryZone',
                                               new IdType(0),
                                               MainFactory::create('CustomerCountryZoneName', ''),
                                               MainFactory::create('CustomerCountryZoneIsoCode', ''));
        }
        
        $addressBlock = MainFactory::create('AddressBlock',
                                            MainFactory::create('CustomerGender',
                                                                $this->customerCollection->getValue('gender') ? : ''),
                                            MainFactory::create('CustomerFirstname',
                                                                $this->customerCollection->getValue('firstname')),
                                            MainFactory::create('CustomerLastname',
                                                                $this->customerCollection->getValue('lastname')),
                                            MainFactory::create('CustomerCompany',
                                                                $this->customerCollection->getValue('company')),
                                            MainFactory::create('CustomerB2BStatus',
                                                                (boolean)(int)$this->customerCollection->getValue('b2b_status')),
                                            MainFactory::create('CustomerStreet',
                                                                $this->customerCollection->getValue('street_address')),
                                            MainFactory::create('CustomerHouseNumber',
                                                                $this->customerCollection->getValue('house_number')),
                                            MainFactory::create('CustomerAdditionalAddressInfo',
                                                                $this->customerCollection->getValue('additional_address_info')),
                                            MainFactory::create('CustomerSuburb',
                                                                $this->customerCollection->getValue('suburb')),
                                            MainFactory::create('CustomerPostcode',
                                                                $this->customerCollection->getValue('postcode')),
                                            MainFactory::create('CustomerCity',
                                                                $this->customerCollection->getValue('city')),
                                            $this->countryService->getCountryById(new IdType($this->customerCollection->getValue('country'))),
                                            $countryZone);
        
        return $addressBlock;
    }
    
    
    protected function _proceedTracking()
    {
        xtc_write_user_info($this->customerCollection->getValue('customer_id'));
        
        if (isset($_SESSION['tracking']['refID'])) {
            $query = "SELECT * FROM " . TABLE_CAMPAIGNS . "
						WHERE campaigns_refID = '" . xtc_db_input($_SESSION['tracking']['refID']) . "'";
            
            $result = xtc_db_query($query);
            if (xtc_db_num_rows($result) > 0) {
                $campaign = xtc_db_fetch_array($result);
                $refID    = $campaign['campaigns_id'];
                
                xtc_db_perform(TABLE_CUSTOMERS,
                               ['refferers_id' => $refID],
                               'update',
                               'customers_id = ' . (int)$this->customerCollection->getValue('customer_id'));
                
                $leads = $campaign['campaigns_leads'] + 1;
                xtc_db_perform(TABLE_CAMPAIGNS,
                               ['campaigns_leads' => $leads],
                               'update',
                               'campaigns_id = ' . $refID);
            }
        }
    }
    
    
    protected function _proceedVoucher()
    {
        if (filter_var(@constant('ACTIVATE_GIFT_SYSTEM'), FILTER_VALIDATE_BOOLEAN) === true) {
            /** @var GiftVouchersConfigurationStorage $giftVouchersConfiguration */
            $giftVouchersConfiguration = MainFactory::create('GiftVouchersConfigurationStorage');
            /** @var GiftVouchersService $giftVouchersService */
            $giftVouchersService = MainFactory::create('GiftVouchersService', $giftVouchersConfiguration);
            /** @var GiftVouchersMailService $giftVouchersMailService */
            $giftVouchersMailService = MainFactory::create('GiftVouchersMailService', $giftVouchersService);
    
            if ((float)@constant('NEW_SIGNUP_GIFT_VOUCHER_AMOUNT') > 0) {
                $voucherAmount        = new DecimalType((float)constant('NEW_SIGNUP_GIFT_VOUCHER_AMOUNT'));
                $coupon               = $giftVouchersService->createGiftVoucher($voucherAmount);
                $customerEmailAddress = $this->customerCollection->getValue('email_address');
                $giftVouchersMailService->storeCouponEmailTrack($coupon->getCouponId(), $customerEmailAddress);
                $this->giftCode = $coupon->getCouponCode()->asString();
            }
        }
    }
    
    
    protected function _login()
    {
        if (SESSION_RECREATE == 'True') {
            xtc_session_recreate();
        }
        
        $_SESSION['customer_id']                 = $this->customerCollection->getValue('customer_id');
        $_SESSION['customer_first_name']         = $this->customerCollection->getValue('firstname');
        $_SESSION['customer_last_name']          = $this->customerCollection->getValue('lastname');
        $_SESSION['customer_default_address_id'] = $this->customerCollection->getValue('default_address_id');
        $_SESSION['customer_country_id']         = $this->customerCollection->getValue('country');
        $_SESSION['customer_zone_id']            = $this->customerCollection->getValue('zone_id');
        $_SESSION['customer_vat_id']             = $this->customerCollection->getValue('vat');
        $_SESSION['account_type']                = $this->customerCollection->getValue('account_type');
        
        // write customers status in session
        require DIR_WS_INCLUDES . 'write_customers_status.php';
        
        // restore cart contents
        $_SESSION['cart']->restore_contents();
    }
    
    
    /**
     * @param GMLogoManager $logoManager
     */
    protected function _proceedMail(GMLogoManager $logoManager)
    {
        $this->_sendMail($this->_buildMailDataArray($logoManager));
    }
    
    
    /**
     * @param GMLogoManager $logoManager
     *
     * @return array
     */
    protected function _buildMailDataArray(GMLogoManager $logoManager)
    {
        $mailDataArray = [];
        
        // build the message content
        $name = $this->customerCollection->getValue('firstname') . ' '
                . $this->customerCollection->getValue('lastname');
        
        // load data into array
        $content = [
            'MAIL_NAME'          => htmlspecialchars_wrapper($name),
            'MAIL_REPLY_ADDRESS' => EMAIL_SUPPORT_REPLY_ADDRESS,
            'MAIL_GENDER'        => htmlspecialchars_wrapper($this->customerCollection->getValue('gender'))
        ];
        
        // assign data to smarty
        $mailDataArray['language']     = $_SESSION['language'];
        $mailDataArray['logo_path']    = HTTP_SERVER . DIR_WS_CATALOG . StaticGXCoreLoader::getThemeControl()
                ->getThemeImagePath();
        $mailDataArray['content']      = $content;
        $mailDataArray['GENDER']       = $this->customerCollection->getValue('gender');
        $mailDataArray['NAME']         = $name;
        $mailDataArray['mail_address'] = $this->customerCollection->getValue('email_address');
        
        if ($logoManager->logo_use == '1') {
            $mailDataArray['gm_logo_mail'] = $logoManager->get_logo();
        }
        
        if ($this->giftCode !== '') {
            $xtcPrice                      = new xtcPrice($_SESSION['currency'],
                                                          $_SESSION['customers_status']['customers_status_id']);
            $mailDataArray['SEND_GIFT']    = 'true';
            $mailDataArray['GIFT_AMMOUNT'] = $xtcPrice->xtcFormat(NEW_SIGNUP_GIFT_VOUCHER_AMOUNT, true);
            $mailDataArray['GIFT_CODE']    = $this->giftCode;
            $mailDataArray['GIFT_LINK']    = xtc_href_link(FILENAME_GV_REDEEM,
                                                           'gv_no=' . $this->giftCode,
                                                           'NONSSL',
                                                           false);
        }
        
        $couponCollection = $this->_getSignUpCouponCollection();
        
        if (!$couponCollection->isEmpty()) {
            $mailDataArray['SEND_COUPON'] = 'true';
            $mailDataArray['COUPON_DESC'] = $couponCollection->getValue('coupon_description');
            $mailDataArray['COUPON_CODE'] = $couponCollection->getValue('coupon_code');
        }
        
        if (defined('EMAIL_SIGNATURE')) {
            $mailDataArray['EMAIL_SIGNATURE_HTML'] = nl2br(EMAIL_SIGNATURE);
            $mailDataArray['EMAIL_SIGNATURE_TEXT'] = EMAIL_SIGNATURE;
        }
        
        return $mailDataArray;
    }
    
    
    /**
     * @param array $mailDataArray
     */
    protected function _sendMail(array $mailDataArray)
    {
        $smarty = new Smarty;
        
        if (is_array($mailDataArray) && count($mailDataArray) > 0) {
            foreach ($mailDataArray as $key => $content) {
                $smarty->assign($key, $content);
            }
        }
        
        $smarty->caching = 0;
        $htmlMail        = fetch_email_template($smarty, 'create_account_mail');
        
        if (ACTIVATE_GIFT_SYSTEM == 'true' && NEW_SIGNUP_GIFT_VOUCHER_AMOUNT > 0) {
            $smarty->assign('GIFT_LINK', str_replace('&amp;', '&', $mailDataArray['GIFT_LINK']));
        }
        
        $smarty->caching = 0;
        $txtMail         = fetch_email_template($smarty, 'create_account_mail', 'txt');
        
        if (SEND_EMAILS == 'true') {
            xtc_php_mail(EMAIL_SUPPORT_ADDRESS,
                         EMAIL_SUPPORT_NAME,
                         $mailDataArray['mail_address'],
                         $mailDataArray['NAME'],
                         EMAIL_SUPPORT_FORWARDING_STRING,
                         EMAIL_SUPPORT_REPLY_ADDRESS,
                         EMAIL_SUPPORT_REPLY_ADDRESS_NAME,
                         '',
                         '',
                         EMAIL_SUPPORT_SUBJECT,
                         $htmlMail,
                         $txtMail);
        }
    }
    
    
    /**
     * @return KeyValueCollection
     */
    protected function _getSignUpCouponCollection()
    {
        $couponArray = [];
        
        $query = 'SELECT
						c.coupon_id,
						c.coupon_code,
						d.coupon_description
					FROM
						' . TABLE_COUPONS . ' c,
						' . TABLE_COUPONS_DESCRIPTION . ' d
					WHERE
						c.coupon_code = "' . xtc_db_input(NEW_SIGNUP_DISCOUNT_COUPON) . '" AND
						c.coupon_id = d.coupon_id AND
						d.language_id = ' . (int)$_SESSION['languages_id'] . '
					LIMIT 1';
        
        $result = xtc_db_query($query);
        
        if (xtc_db_num_rows($result)) {
            $couponArray = xtc_db_fetch_array($result);
        }
        
        return MainFactory::create('KeyValueCollection', $couponArray);
    }
    
    
    /**
     * @param string $p_dateOfBirth
     *
     * @return string YYYY-MM-DD or ''
     */
    protected function _formatDateOfBirth($p_dateOfBirth)
    {
        $dateOfBirth = xtc_date_raw($p_dateOfBirth);
        
        if (strlen($dateOfBirth) === 8) {
            $dateOfBirth = substr($dateOfBirth, 0, 4) . '-' . substr($dateOfBirth, 4, 2) . '-' . substr($dateOfBirth,
                                                                                                        6,
                                                                                                        2);
        }
        
        return $dateOfBirth;
    }
}
