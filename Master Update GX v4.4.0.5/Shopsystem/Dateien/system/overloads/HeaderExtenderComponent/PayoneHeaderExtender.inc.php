<?php
/* --------------------------------------------------------------
	PayPalHeaderExtender.inc.php 2018-11-14
	Gambio GmbH
	http://www.gambio.de
	Copyright (c) 2016 Gambio GmbH
	Released under the GNU General Public License (Version 2)
	[http://www.gnu.org/licenses/gpl-2.0.html]
	--------------------------------------------------------------
*/

/**
 * This HeaderExtender inserts required Javascript and styles for Payone.
 */
class PayoneHeaderExtender extends PayoneHeaderExtender_parent
{
    public function proceed()
    {
        parent::proceed();
        $isCheckoutPage = strpos($_SERVER['SCRIPT_NAME'], 'shopping_cart.php') !== false;
        $isCheckoutPage = $isCheckoutPage || strpos($_SERVER['SCRIPT_NAME'], 'checkout_') !== false;
        if ($isCheckoutPage && $this->_payone_is_enabled()) {
            $output_array = array();

            $output_array['payonejs'] = sprintf('<script src="%s"></script>',
                xtc_href_link('ext/payone/js/client_api.js', '', 'SSL', true, true, false, true, true));
            $output_array['payonestyles'] = sprintf('<link href="%s" rel="stylesheet" type="text/css">',
                xtc_href_link(StaticGXCoreLoader::getThemeControl()->getThemeCssPath() . 'payone.css', '', 'SSL',
                    true, true, false, true, true));

            if (!is_array($this->v_output_buffer)) {
                $this->v_output_buffer = array();
            }
            $this->v_output_buffer = array_merge($this->v_output_buffer, $output_array);
        }
    }

    protected function _payone_is_enabled()
    {
        $payoneInstalled = strpos(MODULE_PAYMENT_INSTALLED, 'payone') !== false;
        return $payoneInstalled;
    }

}
