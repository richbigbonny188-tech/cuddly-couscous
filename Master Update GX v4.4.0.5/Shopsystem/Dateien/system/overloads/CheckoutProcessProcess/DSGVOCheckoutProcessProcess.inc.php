<?php

/* --------------------------------------------------------------
	DSGVOCheckoutProcessProcess.inc.php 2020-10-22
	Gambio GmbH
	http://www.gambio.de
	Copyright (c) 2016 Gambio GmbH
	Released under the GNU General Public License (Version 2)
	[http://www.gnu.org/licenses/gpl-2.0.html]
	--------------------------------------------------------------
*/

/**
 * Class representing the checkout process overload for the DSGVO
 */
class DSGVOCheckoutProcessProcess extends DSGVOCheckoutProcessProcess_parent {
    /**
     * Proceed
     */
    public function proceed() {
        parent::proceed();

        $languageId = new IdType($_SESSION['languages_id']);
        $isIpTrackingConfirmed = isset($_POST['gm_log_ip']) && $_POST['gm_log_ip'] === 'save';
        $configKey = new NonEmptyStringType('GM_LOG_IP');
        $agreementWriteService = StaticGXCoreLoader::getService('AgreementWrite');
        $agreementCustomer = $agreementWriteService->createCustomer(
            new StringType($this->coo_order->customer['firstname'] . ' ' . $this->coo_order->customer['lastname']),
            MainFactory::create('AgreementCustomerEmail', isset($this->coo_order->customer['email_address'])? $this->coo_order->customer['email_address'] : '')
        );

        AgreementStoreHelper::store(
            $languageId,
            LegalTextType::AGB,
            $agreementCustomer,
            $configKey
        );

        AgreementStoreHelper::store(
            $languageId,
            LegalTextType::WITHDRAWAL,
            $agreementCustomer,
            $configKey
        );

        if ($isIpTrackingConfirmed) {
            AgreementStoreHelper::store(
                $languageId,
                LegalTextType::CONFIRM_LOG_IP,
                $agreementCustomer,
                $configKey
            );
        }
    
        if (isset($_SESSION['abandonment_download']) && $_SESSION['abandonment_download'] === 'true') {
            AgreementStoreHelper::store(
                $languageId,
                LegalTextType::DOWNLOAD_WITHDRAWAL,
                $agreementCustomer,
                $configKey
            );
            
    
        }
    
        if (isset($_SESSION['abandonment_service']) && $_SESSION['abandonment_service'] === 'true') {
            AgreementStoreHelper::store(
                $languageId,
                LegalTextType::SERVICE_WITHDRAWAL,
                $agreementCustomer,
                $configKey
            );
        }
    
    
    }
}