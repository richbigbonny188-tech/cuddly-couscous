/* gm_gprint_order.js <?php
#   --------------------------------------------------------------
#   gm_gprint_order.js 2018-06-15
#   Gambio GmbH
#   http://www.gambio.de
#   Copyright (c) 2018 Gambio GmbH
#   Released under the GNU General Public License (Version 2)
#   [http://www.gnu.org/licenses/gpl-2.0.html]
#   --------------------------------------------------------------
?>*/

<?php
if($_SESSION['customers_status']['customers_status_id'] === '0' && $this->v_data_array['GET']['mode'] == 'order')
{
?>

var coo_order_surfaces_manager = null;

<?php
include_once(get_usermod(DIR_FS_CATALOG . 'gm/javascript/gm_gprint_functions.js'));
include_once(get_usermod(DIR_FS_CATALOG . 'gm/javascript/GMGPrintOrderSurfacesManager.js'));
include_once(get_usermod(DIR_FS_CATALOG . 'gm/javascript/GMGPrintOrderSurfaces.js'));
include_once(get_usermod(DIR_FS_CATALOG . 'gm/javascript/GMGPrintOrderElements.js'));
?>

$(document).ready(function()
{
	var t_order_surfaces_groups_id = '0';
	var t_order_sets = Object();

	$('.attributes-container').on('click','.show-details',function()
	{
		t_order_surfaces_groups_id = $(this).attr('id');
		t_order_surfaces_groups_id = t_order_surfaces_groups_id.replace(/show_order_surfaces_groups_id_/g, '');

		coo_order_surfaces_manager = new GMGPrintOrderSurfacesManager(t_order_surfaces_groups_id);
		coo_order_surfaces_manager.load_surfaces_group(t_order_surfaces_groups_id);

		t_order_sets[t_order_surfaces_groups_id] = coo_order_surfaces_manager;

	});
});

<?php
}
?>