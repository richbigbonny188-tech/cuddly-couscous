<?php

/*--------------------------------------------------------------------------------------------------
    CookiesConsentSso.php 2019-12-19
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2019 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

/**
 * Class CookiesConsentSso
 */
class CookiesConsentSso extends CookiesConsentSso_parent
{
    protected function setupCookies(): void
    {
        $purpose = new CookieConsentPurposeDTO(2,'SingleSignOn.single_sing_on_purpose_title', 'SingleSignOn.single_sing_on_purpose_description', 'gambio/singleSignOn');
        
        parent::setupCookies();
        $ssoConfiguration = MainFactory::create('SingleSignonConfigurationStorage');
        if ((bool)$ssoConfiguration->get('services/amazon/active') === true) {
            CookiesConsentSsoStore::setAmazon($this->addCookie(new CookieConfigurationDTO(-1,
                                                                                          'Amazon SSO',
                                                                                          '',
                                                                                          [],
                                                                                          [],
                                                                                          $purpose)));
        }
        if ((bool)$ssoConfiguration->get('services/facebook/active') === true) {
            CookiesConsentSsoStore::setFacebook($this->addCookie(new CookieConfigurationDTO(-2,
                                                                                            'Facebook SSO',
                                                                                            '',
                                                                                            [],
                                                                                            [],
                                                                                            $purpose)));
        }
        if ((bool)$ssoConfiguration->get('services/paypal/active') === true) {
            CookiesConsentSsoStore::setPayPal($this->addCookie(new CookieConfigurationDTO(-3,
                                                                                          'PayPal SSO',
                                                                                          '',
                                                                                          [],
                                                                                          [],
                                                                                          $purpose)));
        }
        if ((bool)$ssoConfiguration->get('services/google/active') === true) {
            CookiesConsentSsoStore::setGoogle($this->addCookie(new CookieConfigurationDTO(-4,
                                                                                          'Google SSO',
                                                                                          '',
                                                                                          [],
                                                                                          [],
                                                                                          $purpose)));
        }
    }
    
    
}