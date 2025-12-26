<?php

/* --------------------------------------------------------------
   AgreementStoreHelper.inc.php 2020-10-22
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2018 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class AgreementStoreHelper
 *
 * @category   System
 * @package    Agreement
 */
class AgreementStoreHelper
{
    /**
     * Store an agreement
     *
     * @param IdType             $languageId        Language ID
     * @param string             $legalTextType     Legal text type. Use the LegalTextType class constants.
     * @param AgreementCustomer  $agreementCustomer Agreement customer object
     * @param NonEmptyStringType $configKey         GM configuration key
     */
    public static function store(
        IdType $languageId,
        $legalTextType,
        AgreementCustomer $agreementCustomer,
        NonEmptyStringType $configKey
    ) {
        /**
         * @var $agreementWriteService  AgreementWriteService
         * @var $legalTextVersionHelper LegalTextVersionHelper
         */
        
        $agreementWriteService = StaticGXCoreLoader::getService('AgreementWrite');
        $agreement             = $agreementWriteService->create();
        $legalTextVersion      = self::getLegalTextVersionByTextType($legalTextType, $languageId->asInt());
        
        $agreement->setText(new NonEmptyStringType(self::getConfirmationTextByTextType($legalTextType,
                                                                                       $languageId->asInt())))
            ->setLegalTextVersion(new StringType($legalTextVersion))
            ->setContentGroup(new IdType(self::getContentGroupByTextType($legalTextType)))
            ->setLanguageId($languageId)
            ->setCustomer($agreementCustomer);
        
        if (gm_get_conf($configKey->asString()) == true) {
            $agreement->setIpAddress(new StringType($_SERVER['REMOTE_ADDR']));
        }
        
        $agreementWriteService->store($agreement);
    }
    
    
    /**
     * Returns the content group id by the provided legal text type.
     *
     * @param string $textType Use the LegalTextType class constants.
     *
     * @return int Content group.
     */
    private static function getContentGroupByTextType($textType)
    {
        switch ($textType) {
            case LegalTextType::PRIVACY:
                return 2;
                break;
            case LegalTextType::AGB:
                return 3;
                break;
            case LegalTextType::WITHDRAWAL:
            case LegalTextType::SERVICE_WITHDRAWAL:
            case LegalTextType::DOWNLOAD_WITHDRAWAL:
                return 3889895;
                break;
            case LegalTextType::CONFIRM_LOG_IP:
            case LegalTextType::COOKIE:
                return 0;
                break;
            case LegalTextType::TRANSPORT:
                return 3210123;
                break;
            default:
                throw new InvalidArgumentException('Provided text type is not valid');
        }
    }
    
    
    /**
     * Returns the content group id by the provided legal text type.
     *
     * @param string $textType   Use the LegalTextType class constants.
     * @param string $languageId Language ID.
     *
     * @return int
     */
    private static function getConfirmationTextByTextType($textType, $languageId)
    {
        $languageTextManagerPrivacy              = MainFactory::create(LanguageTextManager::class,
                                                                       'general',
                                                                       $languageId);
        $languageTextManagerCheckoutPayment      = MainFactory::create(LanguageTextManager::class,
                                                                       'checkout_payment',
                                                                       $languageId);
        $languageTextManagerCheckoutConfirmation = MainFactory::create(LanguageTextManager::class,
                                                                       'checkout_confirmation',
                                                                       $languageId);
        $languageTextManagerWithdrawal          = MainFactory::create(LanguageTextManager::class,
                                                                      'withdrawal',
                                                                      $languageId);
        
        switch ($textType) {
            case LegalTextType::PRIVACY:
                return strip_tags($languageTextManagerPrivacy->get_text('ENTRY_SHOW_PRIVACY_REGISTRATION'));

            case LegalTextType::AGB:
                return $languageTextManagerCheckoutPayment->get_text('text_accept_agb');

            case LegalTextType::WITHDRAWAL:
                return $languageTextManagerCheckoutPayment->get_text('text_accept_withdrawal');

            case LegalTextType::DOWNLOAD_WITHDRAWAL:
                return $languageTextManagerWithdrawal->get_text('text_abandonment_download');

            case LegalTextType::SERVICE_WITHDRAWAL:
                return $languageTextManagerWithdrawal->get_text('text_abandonment_service');

            case LegalTextType::CONFIRM_LOG_IP:
                return $languageTextManagerCheckoutConfirmation->get_text('text_confirm_log_ip') . ' '
                       . $_SERVER['REMOTE_ADDR'];

            case LegalTextType::COOKIE:
                return gm_get_content('GM_COOKIE_CONTENT', $languageId);

            case LegalTextType::TRANSPORT:
                return $languageTextManagerCheckoutPayment->get_text('text_accept_transport_conditions') . ' ('
                       . $languageTextManagerCheckoutPayment->get_text('title_shipping_company') . ')';
            default:
                throw new InvalidArgumentException('Provided text type is not valid');
        }
    }

    
    /**
     * Returns the content group id by the provided legal text type.
     *
     * @param string $textType   Use the LegalTextType class constants.
     * @param int    $languageId Language ID.
     *
     * @return string Legal text version.
     */
    private static function getLegalTextVersionByTextType($textType, $languageId)
    {
        $legalTextVersionHelper = MainFactory::create(LegalTextVersionHelper::class,
                                                      StaticGXCoreLoader::getDatabaseQueryBuilder());
        switch ($textType) {
            case LegalTextType::PRIVACY:
                return $legalTextVersionHelper->getPrivacyTextVersionByLanguageId(new IdType($languageId));
                break;
            case LegalTextType::AGB:
                return $legalTextVersionHelper->getAGBTextVersionByLanguageId(new IdType($languageId));
                break;
            case LegalTextType::WITHDRAWAL:
            case LegalTextType::DOWNLOAD_WITHDRAWAL:
            case LegalTextType::SERVICE_WITHDRAWAL:
                return $legalTextVersionHelper->getWithdrawalTextVersionByLanguageId(new IdType($languageId));
                break;
            case LegalTextType::CONFIRM_LOG_IP:
            case LegalTextType::COOKIE:
                return '';
                break;
            case LegalTextType::TRANSPORT:
                return $legalTextVersionHelper->getTransportTextVersionByLanguageId(new IdType($languageId));
            default:
                throw new InvalidArgumentException('Provided text type is not valid');
        }
    }
}