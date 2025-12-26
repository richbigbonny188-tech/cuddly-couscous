<?php
/* --------------------------------------------------------------
   PermissionService.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess;

use Gambio\Core\AdminAccess\Group\Exceptions\GroupDoesNotExist;
use Gambio\Core\AdminAccess\Role\Exceptions\RoleDoesNotExist;
use Gambio\Core\AdminAccess\Role\Exceptions\StorageOfRolesFailed;

/**
 * Interface PermissionService
 *
 * @package Gambio\Core\AdminAccess\Role
 */
interface PermissionService
{
    /**
     * Checks the permission of an admin for a access group item, based on the provided admin ID, action (read,
     * write or delete), group item type and descriptor.
     *
     * @param int    $adminId
     * @param string $action
     * @param string $groupItemType
     * @param string $groupItemDescriptor
     *
     * @return bool
     */
    public function checkAdminPermission(
        int $adminId,
        string $action,
        string $groupItemType,
        string $groupItemDescriptor
    ): bool;
    
    
    /**
     * Set role permission for a group based on its type and descriptor.
     *
     * @param int    $roleId
     * @param string $groupItemType
     * @param string $groupItemDescriptor
     * @param bool   $readPermission
     * @param bool   $writingPermission
     * @param bool   $deletingPermission
     *
     * @throws GroupDoesNotExist
     * @throws RoleDoesNotExist
     * @throws StorageOfRolesFailed
     */
    public function setRolePermissionsForGroup(
        int $roleId,
        string $groupItemType,
        string $groupItemDescriptor,
        bool $readPermission,
        bool $writingPermission,
        bool $deletingPermission
    ): void;
}