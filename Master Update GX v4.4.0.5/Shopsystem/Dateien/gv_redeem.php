<?php
/* --------------------------------------------------------------
  gv_redeem.php 2020-11-24
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2014 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------


  based on:
  (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
  (c) 2002-2003 osCommerce (gv_redeem.php,v 1.3.2.1 2003/04/18); www.oscommerce.com
  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: gv_redeem.php 1034 2005-07-15 15:21:43Z mz $)

  Released under the GNU General Public License
  -----------------------------------------------------------------------------------------
  Third Party contribution:

  Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
  http://www.oscommerce.com/community/contributions,282
  Copyright (c) Strider | Strider@oscworks.com
  Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
  Copyright (c) Andre ambidex@gmx.net
  Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org


  Released under the GNU General Public License
  --------------------------------------------------------------------------------------- */

require_once('includes/application_top.php');

$isGiftSystemActive = strtolower(@constant('ACTIVATE_GIFT_SYSTEM')) === 'true';
$voucherCode = !empty($_GET['gv_no']) ? $_GET['gv_no'] : '';
if (preg_match('/^[a-f0-9]{5,16}$/', $voucherCode) !== 1) {
    $voucherCode = '';
}

if ($isGiftSystemActive === false || empty($voucherCode)) {
    xtc_redirect(xtc_href_link(FILENAME_DEFAULT, '', ''));
}

$db = StaticGXCoreLoader::getDatabaseQueryBuilder();
$couponDetails = $db->get_where('coupons',
                                ['coupon_type' => 'G', 'coupon_code' => $voucherCode, 'coupon_active' => 'Y'])
    ->row_array();

if (empty($couponDetails)) {
    $_SESSION['info_message'] = ERROR_NO_INVALID_REDEEM_GV;
    xtc_redirect(FILENAME_SHOPPING_CART);
}

$_SESSION['gift_vouchers'] = $_SESSION['gift_vouchers'] ?? [];
$_SESSION['gift_vouchers'][$couponDetails['coupon_id']] = [
    'coupon_id' => $couponDetails['coupon_id'],
    'amount'    => $couponDetails['coupon_amount'],
];

$_SESSION['info_message'] = REDEEMED_AMOUNT . $GLOBALS['xtPrice']->xtcFormat($couponDetails['coupon_amount'], true, 0, true);
$_SESSION['info_message'] .= '<img src="success.gif" style="display:none">';
xtc_redirect(FILENAME_SHOPPING_CART);
