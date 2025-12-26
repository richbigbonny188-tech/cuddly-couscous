<?php
/* --------------------------------------------------------------
   MainAutoloader.inc.php 2021-01-07
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

class MainAutoloader
{
	public $v_class_mapping_mode = NULL;
	public $v_frontend_classes_array = NULL;
	public $v_backend_classes_array = NULL;

	public function __construct($p_mapping_mode)
	{
		$this->v_frontend_classes_array = array(
			'AccountCheck'		=> DIR_FS_CATALOG.'includes/classes/banktransfer_validation.php',
			'breadcrumb'		=> DIR_FS_CATALOG.'includes/classes/breadcrumb.php',
			'cc_validation'		=> DIR_FS_CATALOG.'includes/classes/cc_validation.php',
			'main'				=> DIR_FS_CATALOG.'includes/classes/main.php',
			'messageStack'		=> DIR_FS_CATALOG.'includes/classes/message_stack.php',
			'order_total'		=> DIR_FS_CATALOG.'includes/classes/order_total.php',
			'shoppingCart'		=> DIR_FS_CATALOG.'includes/classes/shopping_cart.php',
			'wishList'			=> DIR_FS_CATALOG.'includes/classes/wish_list.php',
			'XMLParser'			=> DIR_FS_CATALOG.'includes/classes/xmlparserv4.php',
			'heidelpay'			=> DIR_FS_CATALOG.'includes/classes/class.heidelpay.php',
			'xtcPrice'			=> DIR_FS_CATALOG.'includes/classes/xtcPrice.php',
			'language'			=> DIR_FS_CATALOG.'includes/classes/language.php',
			'order'				=> DIR_FS_CATALOG.'includes/classes/order.php',
			'payment'			=> DIR_FS_CATALOG.'includes/classes/payment.php',
			'product'			=> DIR_FS_CATALOG.'includes/classes/product.php',
			'shipping'			=> DIR_FS_CATALOG.'includes/classes/shipping.php',
			'splitPageResults'	=> DIR_FS_CATALOG.'includes/classes/split_page_results.php',
			'vat_validation'	=> DIR_FS_CATALOG.'includes/classes/vat_validation.php',
			'InputFilter'		=> DIR_FS_CATALOG.'includes/classes/class.inputfilter.php',
			'httpClient'		=> DIR_FS_CATALOG.'includes/classes/http_client.php',
		);

		$this->v_backend_classes_array = array(
			'image_manipulation'	=> DIR_FS_CATALOG.'admin/includes/classes/image_manipulator_GD2.php',
			'xtcImport'			    => DIR_FS_CATALOG.'admin/includes/classes/import.php',
			'xtcExport'			    => DIR_FS_CATALOG.'admin/includes/classes/import.php',
			'language'		    	=> DIR_FS_CATALOG.'admin/includes/classes/language.php',
			'Messages'			    => DIR_FS_CATALOG.'admin/includes/classes/messages.php',
			'messageStack'			=> DIR_FS_CATALOG.'admin/includes/classes/message_stack.php',
			'objectInfo'			=> DIR_FS_CATALOG.'admin/includes/classes/object_info.php',
			'paymentModuleInfo'		=> DIR_FS_CATALOG.'admin/includes/classes/payment_module_info.php',
			'PclZip'		    	=> DIR_FS_CATALOG.'admin/includes/classes/pclzip.lib.php',
			'shoppingCart'			=> DIR_FS_CATALOG.'admin/includes/classes/shopping_cart.php',
			'splitPageResults'		=> DIR_FS_CATALOG.'admin/includes/classes/split_page_results.php',
			'tableBlock'			=> DIR_FS_CATALOG.'admin/includes/classes/table_block.php'
		);

		$this->v_class_mapping_mode = $p_mapping_mode;
	}

	public function load($p_class)
	{
		# set in switch/case
		$t_class_map_array = array();

		# get default class_map
		switch($this->v_class_mapping_mode)
		{
			case 'frontend':
				$t_class_map_array = $this->v_frontend_classes_array;
				break;

			case 'backend':
				$t_class_map_array = array_merge(
						$this->v_frontend_classes_array,
						$this->v_backend_classes_array
				);
                //print_r($t_class_map_array);
				break;

			default:
				trigger_error('unknown class_mapping_mode: '.$this->v_class_mapping_mode, E_USER_ERROR);
		}

		# load class
		if(isset($t_class_map_array[$p_class]))
		{
			$t_mapped_class_path = $t_class_map_array[$p_class];
			MainFactory::load_origin_class($p_class, $t_mapped_class_path);
		}
		else
		{
			# not found in class map, try system- and user-classes
			MainFactory::load_class($p_class);
		}
	}
}
