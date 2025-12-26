<?php
/* -------------------------------------------------------------------------------------
PasswordDoubleOptContentControl.inc.php 2021-01-22
Gambio GmbH
http://www.gambio.de
Copyright (c) 2021 Gambio GmbH
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------


based on:
(c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
(c) 2002-2003 osCommerce www.oscommerce.com
(c) 2003  nextcommerce www.nextcommerce.org
(c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: password_double_opt.php,v 1.0)

Released under the GNU General Public License
--------------------------------------------------------------------------------------- */

// include needed functions
require_once(DIR_FS_INC . 'xtc_render_vvcode.inc.php');
require_once(DIR_FS_INC . 'xtc_random_charcode.inc.php');
require_once(DIR_FS_INC . 'xtc_rand.inc.php');

MainFactory::load_class('DataProcessing');

class PasswordDoubleOptContentControl extends DataProcessing
{
    public function proceed($p_language = null)
    {
        if ($p_language === null) {
            $p_language = $_SESSION['language'];
        }

        // create smarty elements
        $smarty = new Smarty;

        $gm_logo_mail = MainFactory::create_object('GMLogoManager', array("gm_logo_mail"));
        if ($gm_logo_mail->logo_use == '1') {
            $smarty->assign('gm_logo_mail', $gm_logo_mail->get_logo());
        }

        $case = 'double_opt';

        $coo_captcha = MainFactory::create_object('Captcha');
        
        /** @var PasswordDoubleOptContentView $coo_password_double_opt_view */
        $coo_password_double_opt_view = MainFactory::create_object('PasswordDoubleOptContentView');
        $this->setStep2Localization($coo_password_double_opt_view);

        if (isset($this->v_data_array['GET']['action']) && ($this->v_data_array['GET']['action'] == 'first_opt_in')) {

            $check_customer_query = xtc_db_query("select customers_firstname, customers_lastname, customers_gender, customers_email_address, customers_id, account_type from " . TABLE_CUSTOMERS . " where customers_email_address = '" . xtc_db_input($this->v_data_array['POST']['email']) . "'");
            $check_customer = xtc_db_fetch_array($check_customer_query);

            $vlcode = xtc_random_charcode(32);
            $link = xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT,
                'action=verified&customers_id=' . $check_customer['customers_id'] . '&key=' . $vlcode, 'SSL', false);

            // assign language to template for caching
            $smarty->assign('language', $p_language);
            $smarty->assign('tpl_path', DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeHtmlPath());
            $smarty->assign('logo_path',
                HTTP_SERVER . DIR_WS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeImagePath());

            if (defined('EMAIL_SIGNATURE')) {
                $smarty->assign('EMAIL_SIGNATURE_HTML', nl2br(EMAIL_SIGNATURE));
                $smarty->assign('EMAIL_SIGNATURE_TEXT', EMAIL_SIGNATURE);
            }

            $t_customers_name = $check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'];

            // assign vars
            $smarty->assign('GENDER', $check_customer['customers_gender']);
            $smarty->assign('NAME', $t_customers_name);
            $smarty->assign('EMAIL', $check_customer['customers_email_address']);
            $smarty->assign('LINK', $link);

            // dont allow cache
            $smarty->caching = false;

            // create mails
            $html_mail = fetch_email_template($smarty, 'password_verification_mail');
            $link = str_replace('&amp;', '&', $link);
            $smarty->assign('LINK', $link);
            $txt_mail = fetch_email_template($smarty, 'password_verification_mail', 'txt');

            if ($coo_captcha->is_valid($this->v_data_array['POST'], 'GM_FORGOT_PASSWORD_VVCODE')) {
                $case = 'first_opt_in';

                if ($check_customer['account_type'] === '0') {
                    xtc_db_query("update " . TABLE_CUSTOMERS . " set password_request_key = '" . $vlcode
                        . "' where customers_id = '" . $check_customer['customers_id'] . "'");
                    xtc_php_mail(EMAIL_SUPPORT_ADDRESS, EMAIL_SUPPORT_NAME, $check_customer['customers_email_address'],
                        '', '', EMAIL_SUPPORT_REPLY_ADDRESS, EMAIL_SUPPORT_REPLY_ADDRESS_NAME, '', '',
                        TEXT_EMAIL_PASSWORD_FORGOTTEN, $html_mail, $txt_mail);
                }
            } else {
                $case = 'code_error';
            }
        }

        // Verification and set new password
        if (isset($this->v_data_array['GET']['action']) && ($this->v_data_array['GET']['action'] === 'verified' ||
                ($this->v_data_array['GET']['action'] == 'save_password') &&
                ($this->v_data_array['POST']['newPassword'] !== $this->v_data_array['POST']['confirmedPassword'] || count($this->v_data_array['POST']['newPassword']) < ENTRY_PASSWORD_MIN_LENGTH))) {
            // assign language to template for caching
            $smarty->assign('language', $p_language);
            $smarty->assign('tpl_path', DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeHtmlPath());
            $smarty->assign('logo_path',
                HTTP_SERVER . DIR_WS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeImagePath());

            if (isset($this->v_data_array['POST']['newPassword'])) {
                if (strlen($this->v_data_array['POST']['newPassword']) < ENTRY_PASSWORD_MIN_LENGTH) {
                    $this->setInvalidPassword($coo_password_double_opt_view, 'INVALID_PASSWORD_LENGTH', 'text_password_min_length', ENTRY_PASSWORD_MIN_LENGTH);
                }

                if ($this->v_data_array['POST']['newPassword'] !== $this->v_data_array['POST']['confirmedPassword']) {
                    $this->setInvalidPassword($coo_password_double_opt_view, 'CONFIRMED_PASSWORD_INVALID', 'text_confirmed_password');
                }
            }

            // assign vars
            $coo_password_double_opt_view->set_('customers_id',
                                                $this->v_data_array['POST']['customers_id'] ??
                                                $this->v_data_array['GET']['customers_id']);
            $coo_password_double_opt_view->set_('key',
                                                $this->v_data_array['POST']['key'] ??
                                                $this->v_data_array['GET']['key']);
            $case = 'set_new_password';
        }

        // Save new password
        if (isset($this->v_data_array['GET']['action']) && $this->v_data_array['GET']['action'] === 'save_password'
            && $this->v_data_array['POST']['newPassword'] === $this->v_data_array['POST']['confirmedPassword'] && strlen($this->v_data_array['POST']['newPassword']) >= ENTRY_PASSWORD_MIN_LENGTH) {
            $check_customer_query = xtc_db_query("select customers_firstname, customers_lastname, customers_gender, customers_id, customers_email_address, password_request_key from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$this->v_data_array['POST']['customers_id'] . "' and password_request_key = '" . xtc_db_input($this->v_data_array['POST']['key']) . "'");
            $check_customer = xtc_db_fetch_array($check_customer_query);
            if (!xtc_db_num_rows($check_customer_query) || $this->v_data_array['POST']['key'] == "") {
                $case = 'no_account';
            } else {
                $newpass = $this->v_data_array['POST']['newPassword'];

                /** @var AuthService $authService */
                $authService = StaticGXCoreLoader::getService('Auth');
                $crypted_password = $authService->getHash(new StringType($newpass));

                // sql injection fix 16.02.2011
                xtc_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . xtc_db_input($crypted_password) . "' where customers_email_address = '" . xtc_db_input($check_customer['customers_email_address']) . "'");
                xtc_db_query("update " . TABLE_CUSTOMERS . " set password_request_key = '' where customers_id = '" . $check_customer['customers_id'] . "'");
                // assign language to template for caching
                $smarty->assign('language', $p_language);
                $smarty->assign('tpl_path', DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeHtmlPath());
                $smarty->assign('logo_path',
                    HTTP_SERVER . DIR_WS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeImagePath());

                if (defined('EMAIL_SIGNATURE')) {
                    $smarty->assign('EMAIL_SIGNATURE_HTML', nl2br(EMAIL_SIGNATURE));
                    $smarty->assign('EMAIL_SIGNATURE_TEXT', EMAIL_SIGNATURE);
                }

                $t_customers_name = $check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'];
                // assign vars
                $smarty->assign('GENDER', $check_customer['customers_gender']);
                $smarty->assign('NAME', $t_customers_name);
                $smarty->assign('EMAIL', $check_customer['customers_email_address']);
                // dont allow cache
                $smarty->caching = false;
                // create mails
                $html_mail = fetch_email_template($smarty, 'new_password_mail');
                $txt_mail = fetch_email_template($smarty, 'new_password_mail', 'txt');

                xtc_php_mail(EMAIL_SUPPORT_ADDRESS, EMAIL_SUPPORT_NAME, $check_customer['customers_email_address'], '',
                    '', EMAIL_SUPPORT_REPLY_ADDRESS, EMAIL_SUPPORT_REPLY_ADDRESS_NAME, '', '',
                    TEXT_EMAIL_PASSWORD_NEW_PASSWORD, $html_mail, $txt_mail);
                if (!isset($GLOBALS['mail_error'])) {
                    $_SESSION['gm_info_message'] = urlencode(TEXT_PASSWORD_SAVED);
                    $this->set_redirect_url(xtc_href_link(FILENAME_LOGIN, '', 'SSL', true, false));
                }
            }
        }

        $t_captcha_html = $coo_captcha->get_html();

        $coo_password_double_opt_view->set_('case', $case);
        if (isset($this->v_data_array['POST']['email'])) {
            $coo_password_double_opt_view->set_('email_address', $this->v_data_array['POST']['email']);
        }
        $coo_password_double_opt_view->set_('captcha_html', $t_captcha_html);
        $this->v_output_buffer = $coo_password_double_opt_view->get_html();

        return true;
    }
    
    
    /**
     * @param PasswordDoubleOptContentView|PasswordDoubleOptThemeContentView $contentView
     */
    protected function setStep2Localization($contentView): void
    {
        $languageTextManager = new LanguageTextManager('password_forgotten');
        
        $contentView->set_content_data('text_step2', $languageTextManager->get_text('text_step2'));
        $contentView->set_content_data('text_to_do', $languageTextManager->get_text('text_to_do'));
    }

    /**
     * @param PasswordDoubleOptContentView|PasswordDoubleOptThemeContentView $contentView
     * @param String $key
     * @param String $textPhrase
     * @param string|null $replacement
     */
    protected function setInvalidPassword($contentView, string $key, string $textPhrase, string $replacement = null): void
    {
        $languageTextManager = new LanguageTextManager('new_password');

        $contentView->set_content_data($key, $replacement ? sprintf($languageTextManager->get_text($textPhrase), $replacement) : $languageTextManager->get_text($textPhrase));
    }
}
