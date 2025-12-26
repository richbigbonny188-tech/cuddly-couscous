<?php
/* --------------------------------------------------------------
   gm_gprint_admin_column_left.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2009 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

use Gambio\Core\AdminAccess\Group\GroupItem;
use Gambio\Core\AdminAccess\PermissionService;
use Gambio\Core\AdminAccess\Role\PermissionAction;

/** @var PermissionService $adminAccessService */

if ($_SESSION['customers_status']['customers_status_id'] == '0'
    && $adminAccessService->checkAdminPermission((int)$_SESSION['customer_id'],
                                                 PermissionAction::READ,
                                                 GroupItem::PAGE_TYPE,
                                                 'gm_gprint.php')) {
    echo '<li class="leftmenu_body_item"><a class="fav_drag_item" id="BOX_GM_GPRINT" href="'
         . xtc_href_link(FILENAME_GM_GPRINT, '', 'NONSSL') . '"">GX-Customizer</a></li>';
}