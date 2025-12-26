<?php
/* --------------------------------------------------------------
   AdminFactory.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Admin;

/**
 * Interface AdminFactory
 *
 * @package Gambio\Core\AdminAccess\Admin
 */
interface AdminFactory
{
    /**
     * @param int    $id
     * @param string $firstName
     * @param string $lastName
     * @param int[]  $assignedRoleIds
     *
     * @return Admin
     */
    public function createAdmin(int $id, string $firstName, string $lastName, array $assignedRoleIds): Admin;
    
    
    /**
     * @param Admin[] $admins
     *
     * @return Admins
     */
    public function createAdmins(Admin ...$admins): Admins;
    
    
    /**
     * @param int $id
     *
     * @return AdminId
     */
    public function createAdminId(int $id): AdminId;
    
    
    /**
     * @param AdminId[] $ids
     *
     * @return AdminIds
     */
    public function createAdminIds(AdminId ...$ids): AdminIds;
    
    
    /**
     * @param int $id
     *
     * @return RoleId
     */
    public function createRoleId(int $id): RoleId;
    
    
    /**
     * @param RoleId[] $ids
     *
     * @return RoleIds
     */
    public function createRoleIds(RoleId ...$ids): RoleIds;
}