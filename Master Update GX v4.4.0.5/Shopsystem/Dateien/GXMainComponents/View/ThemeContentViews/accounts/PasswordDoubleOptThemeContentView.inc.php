<?php
/**
 * PasswordDoubleOptThemeContentView.inc.php 2020-4-2
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

class PasswordDoubleOptThemeContentView extends ThemeContentView
{
    protected $case;
    protected $email_address = '';
    protected $captcha_html;
    protected $customers_id  = '';
    protected $key           = '';
    protected $text_manager;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->set_flat_assigns(true);
        $this->text_manager = new LanguageTextManager('new_password');
    }
    
    
    protected function set_validation_rules()
    {
        // SET VALIDATION RULES
        $this->validation_rules_array['case']          = ['type' => 'string'];
        $this->validation_rules_array['email_address'] = ['type' => 'string'];
        $this->validation_rules_array['captcha_html']  = ['type' => 'string'];
    }
    
    
    public function prepare_data()
    {
        $t_uninitialized_array = $this->get_uninitialized_variables(['case', 'captcha_html']);
        if (empty($t_uninitialized_array)) {
            $this->content_array['VALIDATION_ACTIVE']           = gm_get_conf('GM_FORGOT_PASSWORD_VVCODE');
            $this->content_array['FORM_ACTION_URL']             = xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT,
                                                                                'action=first_opt_in',
                                                                                'SSL');
            $this->content_array['FORM_METHOD']                 = 'post';
            $this->content_array['FORM_ID']                     = 'sign';
            $this->content_array['INPUT_NEW_PASSWORD_NAME']     = 'newPassword';
            $this->content_array['INPUT_CONFIRM_PASSWORD_NAME'] = 'confirmedPassword';
            $this->content_array['PASSWORD_LENGTH_TEXT']        = sprintf($this->text_manager->get_text('text_password_min_length'), ENTRY_PASSWORD_MIN_LENGTH);
            $this->content_array['MIN_PASSWORD_LENGTH']         = ENTRY_PASSWORD_MIN_LENGTH;
            $this->content_array['INPUT_EMAIL_NAME']            = 'email';
            $this->content_array['INPUT_EMAIL_VALUE']           = htmlentities_wrapper($this->email_address);
            $this->content_array['GM_CAPTCHA']                  = $this->captcha_html;
            $this->content_array['CUSTOMERS_ID']                = $this->customers_id;
            $this->content_array['KEY']                         = $this->key;
            
            switch ($this->case) {
                case 'first_opt_in':
                    $this->content_array['info_message'] = TEXT_LINK_MAIL_SENDED;
                    $this->set_content_template('account_password_set_new_password.html');
                    
                    break;
                
                case 'code_error':
                    $this->content_array['info_message'] = TEXT_CODE_ERROR;
                    $this->set_content_template('account_password_double_opt_in.html');
                    break;
                
                case 'no_account':
                    $this->content_array['info_message'] = TEXT_NO_ACCOUNT;
                    $this->set_content_template('account_password_set_new_password.html');
                    break;
                
                case 'double_opt':
                    $this->content_array['info_message'] = TEXT_PASSWORD_FORGOTTEN;
                    $this->set_content_template('account_password_double_opt_in.html');
                    break;
                
                case 'set_new_password':
                    $this->content_array['FORM_ACTION_URL'] = xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT,
                                                                            'action=save_password',
                                                                            'SSL');
                    $this->content_array['FORM_METHOD']     = 'post';
                    $this->content_array['FORM_ID']         = 'sign';
                    
                    $this->content_array['info_message'] = TEXT_PASSWORD_FORGOTTEN;
                    $this->set_content_template('account_password_forgot_password.html');
                    break;
            }
        } else {
            trigger_error("Variable(s) " . implode(', ', $t_uninitialized_array) . " do(es) not exist in class "
                          . get_class($this) . " or is/are null",
                          E_USER_ERROR);
        }
    }
}
