<?php

defined ('_JEXEC') or die('Restricted access');

/**
 * Just adds missing token to cart form
 */

class plgVmPaymentTokencart extends vmPSPlugin {

	function plgVmOnCheckoutAdvertise($cart, &$payment_advertise){
		$payment_advertise[] = '<input type="hidden" name="'.vRequest::getFormToken().'" value="1" /> ';
	}
}