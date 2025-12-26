<?php
/* --------------------------------------------------------------
	PayPal3CheckoutPaymentModulesThemeContentView.inc.php 2020-10-16
	Gambio GmbH
	http://www.gambio.de
	Copyright (c) 2018 Gambio GmbH
	Released under the GNU General Public License (Version 2)
	[http://www.gnu.org/licenses/gpl-2.0.html]
	--------------------------------------------------------------
*/

/**
 * This class modifies the list of payment options displayed on checkout_payment for PayPal Plus and ECS guest flows.
 * For ECS guests, only PayPal will be available. For Plus, payments handled by the PayPal Plus paywall will be
 * filtered out.
 */
class PayPal3CheckoutPaymentModulesThemeContentView extends PayPal3CheckoutPaymentModulesThemeContentView_parent
{
    /**
     * Makes paypal3 the only choice if account was created by ECS
     * @param array $methodsArray
     */
    public function set_methods_array(array $methodsArray)
    {
        if (is_callable(['parent', 'set_methods_array'])) {
            parent::set_methods_array($methodsArray);
            $methodsArray = $this->methods_array;
        }
        $this->methods_array = $methodsArray;
        $paypalConfiguration = MainFactory::create('PayPalConfigurationStorage');
        
        if ($paypalConfiguration->get('allow_selfpickup') == false
            && isset($_SESSION['shipping'])
            && is_array($_SESSION['shipping'])
            && $_SESSION['shipping']['id'] === 'selfpickup_selfpickup') {
            return;
        }
        
        if ($_SESSION['payment'] === 'paypal3' && isset($_SESSION['paypal_payment'])
            && (isset($_SESSION['paypal_payment']['is_guest']) || $_SESSION['paypal_payment']['type'] === 'ecs')) {
            // ECS may only pay by PayPal
            $pp3MethodsArray = [];
            foreach ($methodsArray as $method) {
                if ($method['id'] === 'paypal3') {
                    $pp3MethodsArray[] = $method;
                }
            }
            if (empty($pp3MethodsArray)) {
                // this can happen if the customer is somehow not allowed to use paypal3, e.g. zone mismatch
                unset($_SESSION['payment'], $_SESSION['paypal_payment']);
                xtc_redirect(xtc_href_link('checkout_payment.php', '', 'SSL'));
            }
            $this->methods_array = $pp3MethodsArray;
        } elseif (isset($_SESSION['paypal_payment']) && $_SESSION['paypal_payment']['type'] == 'plus') {
            // don't show payment types handled by PayPalPlus (via 3rd-party interface)
            $handledByPaymentWall = $this->_getPaymentModulesHandledByPaymentWall($paypalConfiguration);
            $pp3MethodsArray      = [];
            foreach ($methodsArray as $method) {
                if (!in_array($method['id'], $handledByPaymentWall, true)) {
                    $pp3MethodsArray[] = $method;
                }
            }
            $this->methods_array = $pp3MethodsArray;
        }
    }
    
    
    protected function _getPaymentModulesHandledByPaymentWall(PayPalConfigurationStorage $paypalConfiguration)
    {
        $paymentCodes = array('cod', 'moneyorder', 'invoice', 'cash', 'eustandardtransfer');
        $paywallPaymentCodes = array();
        foreach ($paymentCodes as $paymentId) {
            if ($paypalConfiguration->get('thirdparty_payments/' . $paymentId . '/mode') == 'paywall') {
                $paywallPaymentCodes[] = $paymentId;
            }
        }

        return $paywallPaymentCodes;
    }
}