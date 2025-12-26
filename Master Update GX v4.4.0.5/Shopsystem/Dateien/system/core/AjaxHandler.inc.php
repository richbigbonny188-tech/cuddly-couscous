<?php
/* --------------------------------------------------------------
   AjaxHandler.inc.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2017 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Gambio\Core\AdminAccess\Group\GroupItem;
use Gambio\Core\AdminAccess\PermissionService;
use Gambio\Core\AdminAccess\Role\PermissionAction;

class AjaxHandler
{

	 /*** Attributes: ***/

	var $v_header_buffer_array = array();
	var $v_output_buffer;
	var $v_data_array = array();

	public function __construct()
	{
	
	}
	
	function get_permission_status($p_customers_id=NULL)
	{
		$t_msg = 'need to overwrite this method [get_permission_status($p_customers_id=NULL)] and return true or false';
		trigger_error($t_msg, E_USER_ERROR);
		
		#false by default for security reasons
		return false;
	}
	
	
	/**
	 * Checks the reading permissions for an admin.
	 *
	 * @param $ajaxHandler
	 * @param $customerId
	 *
     * @return bool
     */
    protected function _checkAdminReadingPermission($ajaxHandler, $customerId)
    {
        /** @var PermissionService $adminAccessService */
        $adminAccessService = LegacyDependencyContainer::getInstance()->get(PermissionService::class);
        
        return $adminAccessService->checkAdminPermission((int)$customerId,
                                                         PermissionAction::READ,
                                                         GroupItem::AJAX_HANDLER_TYPE,
                                                         $ajaxHandler);
    }

	function add_header($p_header)
	{
		$this->v_header_buffer_array[] = $p_header;
	}

	function set_data($p_key, $p_value)
	{
		$this->v_data_array[$p_key] = $p_value;
	}

	function proceed()
	{
		# method abstract
		return true;
	}

	function get_response()
	{
		foreach($this->v_header_buffer_array as $t_header_item)
		{
			header($t_header_item);
		}
		$t_output = $this->v_output_buffer;
		return $t_output;
	}



} // end of AjaxHandler