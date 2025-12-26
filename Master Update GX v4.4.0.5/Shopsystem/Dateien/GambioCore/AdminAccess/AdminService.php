<?php
/* --------------------------------------------------------------
   AdminService.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess;

use Gambio\Core\AdminAccess\Admin\Admin;
use Gambio\Core\AdminAccess\Admin\AdminIds;
use Gambio\Core\AdminAccess\Admin\Admins;
use Gambio\Core\AdminAccess\Admin\Exceptions\AdminDoesNotExist;
use Gambio\Core\AdminAccess\Admin\Exceptions\StorageOfAdminsFailed;

/**
 * Interface AdminService
 *
 * @package Gambio\Core\AdminAccess\Admin
 */
interface AdminService
{
    /**
     * Returns all available admins.
     *
     * @return Admins
     */
    public function getAdmins(): Admins;
    
    
    /**
     * Returns a specific admin based on the provided admin ID.
     *
     * @param int $id
     *
     * @return Admin
     *
     * @throws AdminDoesNotExist
     */
    public function getAdminById(int $id): Admin;
    
    
    /**
     * Stores (creates or updates) provided admins and return their IDs.
     *
     * @param Admin ...$admins
     *
     * @return AdminIds
     *
     * @throws StorageOfAdminsFailed
     */
    public function storeAdmins(Admin ...$admins): AdminIds;
}