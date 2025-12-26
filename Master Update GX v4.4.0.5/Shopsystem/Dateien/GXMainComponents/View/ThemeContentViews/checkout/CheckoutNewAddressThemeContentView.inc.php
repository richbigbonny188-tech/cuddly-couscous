<?php
/* --------------------------------------------------------------
  CheckoutNewAddressThemeContentView.inc.php 2018-11-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2018 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------


  based on:
  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
  (c) 2002-2003 osCommerce(checkout_new_address.php,v 1.3 2003/05/19); www.oscommerce.com
  (c) 2003	 nextcommerce (checkout_new_address.php,v 1.8 2003/08/17); www.nextcommerce.org
  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: checkout_new_address.php 1239 2005-09-24 20:09:56Z mz $)

  Released under the GNU General Public License
  --------------------------------------------------------------------------------------- */

// include needed functions
require_once(DIR_FS_INC . 'xtc_get_country_list.inc.php');
require_once(DIR_FS_INC . 'xtc_get_zone_name.inc.php');

class CheckoutNewAddressThemeContentView extends ThemeContentView
{
    protected $coo_address;
    protected $error_array           = [];
    protected $zones_array           = [];
    protected $privacy_html          = '';
    protected $show_privacy_checkbox = '0';
    protected $privacy_accepted      = '0';
    
    
    function __construct()
    {
        parent::__construct();
        
        $this->set_content_template('checkout_new_address.html');
        $this->set_flat_assigns(true);
        $this->set_caching_enabled(false);
    }
    
    
    protected function set_validation_rules()
    {
        // GENERAL VALIDATION RULES
        $this->validation_rules_array['coo_address'] = [
            'type'        => 'object',
            'object_type' => 'AddressModel'
        ];
        $this->validation_rules_array['error_array'] = ['type' => 'array'];
        $this->validation_rules_array['zones_array'] = ['type' => 'array'];
    }
    
    
    function prepare_data()
    {
        $t_uninitialized_array = $this->get_uninitialized_variables(['coo_address']);
        if (empty($t_uninitialized_array)) {
            $t_error_array = $this->error_array;
            foreach ($t_error_array AS $t_error => $t_error_text) {
                $this->content_array[$t_error] = $t_error_text;
                $GLOBALS['messageStack']->add('create_account', $t_error_text);
            }
            
            $this->add_data();
        } else {
            trigger_error("Variable(s) " . implode(', ',
                                                   $t_uninitialized_array) . " do(es) not exist in class "
                          . get_class($this) . " or is/are null",
                          E_USER_ERROR);
        }
    }
    
    
    protected function add_data()
    {
        if (!isset($this->content_array['form_data'])) {
            $this->content_array['form_data'] = [];
        }
        
        $this->add_gender();
        $this->add_firstname();
        $this->add_lastname();
        $this->add_company();
        $this->add_street_address();
        $this->add_additional_info();
        $this->add_suburb();
        $this->add_postcode();
        $this->add_city();
        $this->add_country();
        $this->add_state();
        $this->add_b2b_status();
        $this->add_privacy();
    }
    
    
    protected function add_gender()
    {
        if (ACCOUNT_GENDER == 'true') {
            $t_male                        = ($this->coo_address->get_('entry_gender') == 'm') ? true : false;
            $t_female                      = ($this->coo_address->get_('entry_gender') == 'f') ? true : false;
            $this->content_array['gender'] = '1';
            
            if (!isset($this->content_array['form_data']['gender'])) {
                $this->content_array['form_data']['gender'] = [];
            }
            if (!isset($this->content_array['form_data']['gender']['m'])) {
                $this->content_array['form_data']['gender']['m'] = [];
            }
            if (!isset($this->content_array['form_data']['gender']['f'])) {
                $this->content_array['form_data']['gender']['f'] = [];
            }
            
            $this->content_array['form_data']['gender']['name']         = 'gender';
            $this->content_array['form_data']['gender']['m']['value']   = 'm';
            $this->content_array['form_data']['gender']['f']['value']   = 'f';
            $this->content_array['form_data']['gender']['m']['checked'] = '0';
            $this->content_array['form_data']['gender']['f']['checked'] = '0';
            
            if ($t_male) {
                $this->content_array['form_data']['gender']['m']['checked'] = '1';
            }
            
            if ($t_female) {
                $this->content_array['form_data']['gender']['f']['checked'] = '1';
            }
            
            if (GENDER_MANDATORY === 'true') {
                $this->content_array['form_data']['gender']['required'] = 1;
            }
        }
    }
    
    
    protected function add_firstname()
    {
        if (!isset($this->content_array['form_data']['firstname'])) {
            $this->content_array['form_data']['firstname'] = [];
        }
        $this->content_array['form_data']['firstname']['name']     = 'firstname';
        $this->content_array['form_data']['firstname']['value']    = $this->coo_address->get_('entry_firstname');
        $this->content_array['form_data']['firstname']['required'] = 0;
        
        if ((int)ENTRY_FIRST_NAME_MIN_LENGTH > 0) {
            $this->content_array['form_data']['firstname']['required'] = 1;
        }
    }
    
    
    protected function add_lastname()
    {
        if (!isset($this->content_array['form_data']['lastname'])) {
            $this->content_array['form_data']['lastname'] = [];
        }
        $this->content_array['form_data']['lastname']['name']     = 'lastname';
        $this->content_array['form_data']['lastname']['value']    = $this->coo_address->get_('entry_lastname');
        $this->content_array['form_data']['lastname']['required'] = 0;
        
        if ((int)ENTRY_LAST_NAME_MIN_LENGTH > 0) {
            $this->content_array['form_data']['lastname']['required'] = 1;
        }
    }
    
    
    protected function add_company()
    {
        if (ACCOUNT_COMPANY == 'true') {
            $this->content_array['company'] = '1';
            
            if (!isset($this->content_array['form_data']['company'])) {
                $this->content_array['form_data']['company'] = [];
            }
            $this->content_array['form_data']['company']['name']     = 'company';
            $this->content_array['form_data']['company']['value']    = $this->coo_address->get_('entry_company');
            $this->content_array['form_data']['company']['required'] = 0;
        } else {
            $this->content_array['company'] = '0';
        }
    }
    
    
    protected function add_street_address()
    {
        if (!isset($this->content_array['form_data']['street_address'])) {
            $this->content_array['form_data']['street_address'] = [];
        }
        $this->content_array['form_data']['street_address']['name']     = 'street_address';
        $this->content_array['form_data']['street_address']['value']    = $this->coo_address->get_('entry_street_address');
        $this->content_array['form_data']['street_address']['required'] = 0;
        
        if ((int)ENTRY_STREET_ADDRESS_MIN_LENGTH > 0) {
            $this->content_array['form_data']['street_address']['required'] = 1;
        }
        
        if (ACCOUNT_SPLIT_STREET_INFORMATION == 'true') {
            $this->content_array['split_street_information'] = '1';
            
            if (!isset($this->content_array['form_data']['house_number'])) {
                $this->content_array['form_data']['house_number'] = [];
            }
            $this->content_array['form_data']['house_number']['name']     = 'house_number';
            $this->content_array['form_data']['house_number']['value']    = $this->coo_address->get_('entry_house_number');
            $this->content_array['form_data']['house_number']['required'] = 0;
        } else {
            $this->content_array['split_street_information'] = '0';
        }
    }
    
    
    protected function add_additional_info()
    {
        if (ACCOUNT_ADDITIONAL_INFO == 'true') {
            $this->content_array['additional_address_info'] = '1';
            
            if (!isset($this->content_array['form_data']['additional_address_info'])) {
                $this->content_array['form_data']['additional_address_info'] = [];
            }
            $this->content_array['form_data']['additional_address_info']['name']     = 'additional_address_info';
            $this->content_array['form_data']['additional_address_info']['value']    = $this->coo_address->get_('entry_additional_info');
            $this->content_array['form_data']['additional_address_info']['required'] = 0;
        } else {
            $this->content_array['additional_address_info'] = '0';
        }
    }
    
    
    protected function add_suburb()
    {
        if (ACCOUNT_SUBURB == 'true') {
            $this->content_array['suburb'] = '1';
            
            if (!isset($this->content_array['form_data']['suburb'])) {
                $this->content_array['form_data']['suburb'] = [];
            }
            $this->content_array['form_data']['suburb']['name']     = 'suburb';
            $this->content_array['form_data']['suburb']['value']    = $this->coo_address->get_('entry_suburb');
            $this->content_array['form_data']['suburb']['required'] = 0;
        } else {
            $this->content_array['suburb'] = '0';
        }
    }
    
    
    protected function add_postcode()
    {
        if (!isset($this->content_array['form_data']['postcode'])) {
            $this->content_array['form_data']['postcode'] = [];
        }
        $this->content_array['form_data']['postcode']['name']     = 'postcode';
        $this->content_array['form_data']['postcode']['value']    = $this->coo_address->get_('entry_postcode');
        $this->content_array['form_data']['postcode']['required'] = 0;
        
        if ((int)ENTRY_POSTCODE_MIN_LENGTH > 0) {
            $this->content_array['form_data']['postcode']['required'] = 1;
        }
    }
    
    
    protected function add_city()
    {
        if (!isset($this->content_array['form_data']['city'])) {
            $this->content_array['form_data']['city'] = [];
        }
        $this->content_array['form_data']['city']['name']     = 'city';
        $this->content_array['form_data']['city']['value']    = $this->coo_address->get_('entry_city');
        $this->content_array['form_data']['city']['required'] = 0;
        
        if ((int)ENTRY_CITY_MIN_LENGTH > 0) {
            $this->content_array['form_data']['city']['required'] = 1;
        }
    }
    
    
    protected function add_state()
    {
        $this->content_array['form_data']['state']['name']     = 'state';
        $this->content_array['form_data']['state']['type']     = 'selection';
        $this->content_array['form_data']['state']['required'] = 0;
        
        if ($this->coo_address->get_('entry_country_id') > 0) {
            $this->content_array['form_data']['state']['value'] = $this->coo_address->get_('entry_zone_id');
        }
        
        if (ACCOUNT_STATE === 'true' && (int)ENTRY_STATE_MIN_LENGTH > 0) {
            $this->content_array['form_data']['state']['required'] = 1;
        }
    }
    
    
    protected function add_country()
    {
        if ($this->coo_address->get_('entry_country_id') > 0) {
            $t_selected = htmlentities_wrapper($this->coo_address->get_('entry_country_id'));
        } else {
            $t_selected = STORE_COUNTRY;
        }
        
        $this->content_array['form_data']['country']['name']     = 'country';
        $this->content_array['form_data']['country']['value']    = $t_selected;
        $this->content_array['form_data']['country']['required'] = 1;
        
        $this->content_array['countries_data'] = xtc_get_countriesList();
    }
    
    
    protected function add_b2b_status()
    {
        $t_default_value                                            = (ACCOUNT_DEFAULT_B2B_STATUS === 'true' ? 1 : 0);
        $this->content_array['show_b2b_status']                     = (ACCOUNT_B2B_STATUS === 'true' ? 1 : 0);
        $this->content_array['form_data']['b2b_status']             = [];
        $this->content_array['form_data']['b2b_status']['name']     = 'b2b_status';
        $this->content_array['form_data']['b2b_status']['checked']  = ($this->coo_address->get_('customer_b2b_status')
                                                                       !== null ? $this->coo_address->get_('customer_b2b_status') : $t_default_value);
        $this->content_array['form_data']['b2b_status']['required'] = 1;
    }
    
    
    protected function add_privacy()
    {
        $this->content_array['GM_PRIVACY_LINK'] = $this->privacy_html;
        
        $this->content_array['show_privacy_checkbox'] = $this->show_privacy_checkbox;
        $this->content_array['privacy_accepted']      = (int)$this->privacy_accepted;
    }
}
