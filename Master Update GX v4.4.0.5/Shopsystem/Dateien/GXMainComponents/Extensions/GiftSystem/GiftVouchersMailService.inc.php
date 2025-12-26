<?php
/* --------------------------------------------------------------
   GiftVouchersMailService.inc.php 2020-12-28
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/
declare(strict_types=1);

require_once DIR_FS_INC . 'xtc_php_mail.inc.php';

class GiftVouchersMailService
{
    /**
     * @var GiftVouchersService
     */
    protected $giftVouchersService;
    /**
     * @var xtcPrice_ORIGIN
     */
    protected $xtPrice;
    
    
    public function __construct(GiftVouchersService $giftVouchersService)
    {
        $this->giftVouchersService = $giftVouchersService;
        MainFactory::load_origin_class('xtcPrice');
        $this->xtPrice = new xtcPrice(DEFAULT_CURRENCY, DEFAULT_CUSTOMERS_STATUS_ID_GUEST);
    }
    
    
    /**
     * @param string $couponCode
     * @param string $toEmail
     * @param string $toName
     * @param string $message
     * @param string $subject
     *
     * @return string
     * @throws InvalidCouponCodeException
     */
    public function sendMail(string $couponCode, string $toEmail, string $toName, string $message, string $subject): string
    {
        $amount = $this->giftVouchersService->getAmountByCouponCode($couponCode);
        $amountFormatted = $this->xtPrice->xtcFormat($amount, true);
        
        // initiate template engine for mail
        $smarty = new Smarty();
        
        // assign language to template for caching
        $smarty->assign('language', $_SESSION['language']);
        $smarty->caching = false;
        
        // set dirs manual
        $smarty->template_dir = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeHtmlPath();
        $smarty->config_dir   = DIR_FS_CATALOG . 'lang';
        
        $themeControl = StaticGXCoreLoader::getThemeControl();
        $smarty->assign('tpl_path', DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeHtmlPath());
        $smarty->assign('logo_path', HTTP_SERVER . DIR_WS_CATALOG . $themeControl->getThemeImagePath());
        
        $smarty->assign('AMMOUNT', $amountFormatted);
        $smarty->assign('MESSAGE', $message);
        $smarty->assign('GIFT_ID', $couponCode);
        $smarty->assign('WEBSITE', HTTP_SERVER . DIR_WS_CATALOG);
        
        if (defined('SEARCH_ENGINE_FRIENDLY_URLS') && SEARCH_ENGINE_FRIENDLY_URLS === 'true') {
            $link = HTTP_SERVER . DIR_WS_CATALOG . 'gv_redeem.php' . '/gv_no,' . $couponCode;
        } else {
            $link = HTTP_SERVER . DIR_WS_CATALOG . 'gv_redeem.php' . '?gv_no=' . $couponCode;
        }
        
        $gm_logo_mail = MainFactory::create_object('GMLogoManager', ['gm_logo_mail']);
        if ($gm_logo_mail->logo_use === '1') {
            $smarty->assign('gm_logo_mail', $gm_logo_mail->get_logo());
        }
        if (defined('EMAIL_SIGNATURE')) {
            $smarty->assign('EMAIL_SIGNATURE_HTML', nl2br(EMAIL_SIGNATURE));
            $smarty->assign('EMAIL_SIGNATURE_TEXT', EMAIL_SIGNATURE);
        }
        
        $link = str_replace('&amp;', '&', $link);
        $smarty->assign('GIFT_LINK', $link);
        
        $html_mail = fetch_email_template($smarty, 'send_gift', 'html');
        $txt_mail  = fetch_email_template($smarty, 'send_gift', 'txt');
        
        xtc_php_mail(EMAIL_BILLING_ADDRESS,
                     EMAIL_BILLING_NAME,
                     $toEmail,
                     $toName,
                     '',
                     EMAIL_BILLING_REPLY_ADDRESS,
                     EMAIL_BILLING_REPLY_ADDRESS_NAME,
                     '',
                     '',
                     $subject,
                     $html_mail,
                     $txt_mail);
        
        return $couponCode;
    }
    
    
    /**
     * @param IdType $couponId
     * @param string $toEmail
     */
    public function storeCouponEmailTrack(IdType $couponId, string $toEmail): void
    {
        /** @var CI_DB_query_builder $db */
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $db->insert('coupon_email_track',
                    [
                        'coupon_id'        => $couponId->asInt(),
                        'customer_id_sent' => 0,
                        'sent_firstname'   => 'Admin',
                        'emailed_to'       => $toEmail,
                        'date_sent'        => date('Y-m-d H:i:s'),
                    ]);
        
    }
    
}
