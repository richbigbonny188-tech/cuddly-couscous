<?php
/* --------------------------------------------------------------
   RoleService.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess;

use Gambio\Core\AdminAccess\Role\Exceptions\DeletionOfRolesFailed;
use Gambio\Core\AdminAccess\Role\Exceptions\RoleDoesNotExist;
use Gambio\Core\AdminAccess\Role\Exceptions\StorageOfRolesFailed;
use Gambio\Core\AdminAccess\Role\Role;
use Gambio\Core\AdminAccess\Role\RoleIds;
use Gambio\Core\AdminAccess\Role\Roles;

/**
 * Interface RoleService
 *
 * @package Gambio\Core\AdminAccess\Role
 */
interface RoleService
{
    /**
     * Returns all available access roles.
     *
     * @return Roles
     */
    public function getRoles(): Roles;
    
    
    /**
     * Returns all access roles assigned to a specific admin, based on the provided admin ID.
     *
     * @param int $adminId
     *
     * @return Roles
     */
    public function getRolesByAdmin(int $adminId): Roles;
    
    
    /**
     * Returns a specific access role based on the provided role ID.
     *
     * @param int $roleId
     *
     * @return Role
     *
     * @throws RoleDoesNotExist
     */
    public function getRoleById(int $roleId): Role;
    
    
    /**
     * Creates a new access role based on the provided names, descriptions, sort order and protection status.
     * The provided names and description arrays need to map language ID (key) and name or description (value).
     *
     * @param array<int, string> $names
     * @param array<int, string> $descriptions
     * @param int                $sortOrder
     * @param bool               $isProtected
     *
     * @return Role
     */
    public function createRole(array $names, array $descriptions, int $sortOrder, bool $isProtected = false): Role;
    
    
    /**
     * Stores (creates or updates) all provided access roles and returns their role IDs.
     *
     * @param Role ...$roles
     *
     * @return RoleIds
     *
     * @throws StorageOfRolesFailed
     */
    public function storeRoles(Role ...$roles): RoleIds;
    
    
    /**
     * Deletes all access roles based on the provided role IDs.
     *
     * @param int[] $roleIds
     *
     * @throws DeletionOfRolesFailed
     */
    public function deleteRoles(int ...$roleIds): void;
}