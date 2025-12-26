<?php
/* --------------------------------------------------------------
  PriceOfferContentViewOverload.inc.php 2021-02-02
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2021 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
 */

/**
 * Class PriceOfferContentViewOverload
 *
 * This overload is mainly used, in order to create agreement data and store it in the database.
 * That data contains information about the confirmed agreement of the customer like the customers name and email.
 */
class PriceOfferContentViewOverload extends PriceOfferContentViewOverload_parent
{
	protected function add_data()
	{
		parent::add_data();
		
		if(isset($this->content_array['VVCODE_ERROR']) == false && isset($this->content_array['ERROR']) == false
		   && empty($this->v_env_post_array['email']) == false)
		{
			$languageId = new IdType($_SESSION['languages_id']);
			$configKey  = new NonEmptyStringType('LOG_IP_FOUND_CHEAPER');
			
			$agreementWriteService = StaticGXCoreLoader::getService('AgreementWrite');
			$agreementCustomer     = $agreementWriteService->createCustomer(new StringType($this->v_env_post_array['name']),
			                                                                new CustomerEmail($this->v_env_post_array['email']));
			
			AgreementStoreHelper::store($languageId, LegalTextType::PRIVACY, $agreementCustomer, $configKey);
		}
	}
}