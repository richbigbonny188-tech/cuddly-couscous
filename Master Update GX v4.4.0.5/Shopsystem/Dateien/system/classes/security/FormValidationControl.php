<?php
/* --------------------------------------------------------------
  FormValidationControl.inc.php 2018-06-14
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2018 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  ---------------------------------------------------------------------------------------
*/

class FormValidationControl extends BaseClass
{
	public function __construct()
	{
	}
	

	public function validate_address(AddressModel $p_address, $p_block_packstation = false)
	{
		$t_error_array = array();

		if(ACCOUNT_GENDER == 'true')
		{
			if(!$this->_accountNamesOptional($p_address) && $p_address->get_('entry_gender') != 'm' && $p_address->get_('entry_gender') != 'f' && ((string)$p_address->get_('entry_gender') === '' && GENDER_MANDATORY === 'true'))
			{
				$t_error_array['error_gender'] = ENTRY_GENDER_ERROR;
			}
		}

		if(!$this->_accountNamesOptional($p_address) && strlen_wrapper($p_address->get_('entry_firstname')) < ENTRY_FIRST_NAME_MIN_LENGTH)
		{
			$t_error_array['error_first_name'] = sprintf(ENTRY_FIRST_NAME_ERROR, ENTRY_FIRST_NAME_MIN_LENGTH);
		}

		if(!$this->_accountNamesOptional($p_address) && strlen_wrapper($p_address->get_('entry_lastname')) < ENTRY_LAST_NAME_MIN_LENGTH)
		{
			$t_error_array['error_last_name'] = sprintf(ENTRY_LAST_NAME_ERROR, ENTRY_LAST_NAME_MIN_LENGTH);
		}

		if(ACCOUNT_COMPANY == 'true')
		{
			if(strlen_wrapper($p_address->get_('entry_company')) > 0
			   && strlen_wrapper($p_address->get_('entry_company')) < ENTRY_COMPANY_MIN_LENGTH
			)
			{
				$t_error_array['error_company'] = sprintf(ENTRY_COMPANY_ERROR, ENTRY_COMPANY_MIN_LENGTH);
			}
		}

		if(strlen_wrapper($p_address->get_('entry_street_address')) < ENTRY_STREET_ADDRESS_MIN_LENGTH)
		{
			$t_error_array['error_street'] = sprintf(ENTRY_STREET_ADDRESS_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH);
		}
		
		if(ACCOUNT_SPLIT_STREET_INFORMATION === 'true')
		{
			if(strlen_wrapper($p_address->get_('entry_house_number')) < ENTRY_HOUSENUMBER_MIN_LENGTH)
			{
				$t_error_array['error_house_number'] = sprintf(ENTRY_HOUSENUMBER_ERROR, ENTRY_HOUSENUMBER_MIN_LENGTH);
			}
		}
		
		if($p_block_packstation === true
		   && preg_match('/.*(packstation|postfiliale|filiale).*/i', $p_address->get_('entry_street_address')) == 1
		)
		{
			$t_error_array['error_street'] = ENTRY_STREET_ADDRESS_NOT_STREET;
		}

		if(strlen_wrapper($p_address->get_('entry_postcode')) < ENTRY_POSTCODE_MIN_LENGTH)
		{
			$t_error_array['error_post_code'] = sprintf(ENTRY_POST_CODE_ERROR, ENTRY_POSTCODE_MIN_LENGTH);
		}

		if(strlen_wrapper($p_address->get_('entry_city')) < ENTRY_CITY_MIN_LENGTH)
		{
			$t_error_array['error_city'] = sprintf(ENTRY_CITY_ERROR, ENTRY_CITY_MIN_LENGTH);
		}

		if(is_numeric($p_address->get_('entry_country_id')) == false)
		{
			$t_error_array['error_country'] = ENTRY_COUNTRY_ERROR;
		}
		
        $coo_country_service    = StaticGXCoreLoader::getService('Country');
        $is_state_mandatory     = $coo_country_service->isStateMandatory(new IdType($p_address->get_('entry_country_id')));
        $country                = $coo_country_service->getCountryById(new IdType($p_address->get_('entry_country_id')));
        $t_entry_state_has_zone = $coo_country_service->countryHasCountryZones($country);
        
        if(($is_state_mandatory && $t_entry_state_has_zone) || (ACCOUNT_STATE == 'true' && $t_entry_state_has_zone))
        {
            $country_zone_id     = xtc_db_prepare_input($p_address->get_('entry_zone_id'));
            $country_zone        = $coo_country_service->getCountryZoneById(new IdType($country_zone_id));
            $country_zone_exists = $coo_country_service->countryZoneExistsInCountry($country_zone, $country);
            
            if(!$country_zone_exists)
            {
                $t_error_array['error_state'] = ENTRY_STATE_ERROR_SELECT;
            }
        }
    
		return $t_error_array;
	}


	protected function _accountNamesOptional(AddressModel $address)
	{
		if(ACCOUNT_NAMES_OPTIONAL === 'true' && $address->get_('entry_company') !== '')
		{
			return true;
		}
		return false;
	}
}