<?php
/* --------------------------------------------------------------
   RoleFactory.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role;

/**
 * Interface RoleFactory
 *
 * @package Gambio\Core\AdminAccess\Role
 */
interface RoleFactory
{
    /**
     * @param int                $id
     * @param array<int, string> $names
     * @param array<int, string> $descriptions
     * @param Permissions        $permissions
     * @param int                $sortOrder
     * @param bool               $isProtected
     *
     * @return Role
     */
    public function createRole(
        int $id,
        array $names,
        array $descriptions,
        Permissions $permissions,
        int $sortOrder,
        bool $isProtected
    ): Role;
    
    
    /**
     * @param Role[] $roles
     *
     * @return Roles
     */
    public function createRoles(Role ...$roles): Roles;
    
    
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
    
    
    /**
     * @param int  $groupId
     * @param bool $readingGranted
     * @param bool $writingGranted
     * @param bool $deletingGranted
     *
     * @return Permission
     */
    public function createPermission(
        int $groupId,
        bool $readingGranted,
        bool $writingGranted,
        bool $deletingGranted
    ): Permission;
    
    
    /**
     * @param Permission[] $permissions
     *
     * @return Permissions
     */
    public function createPermissions(Permission ...$permissions): Permissions;
    
    
    /**
     * @param int $id
     *
     * @return AdminId
     */
    public function createAdminId(int $id): AdminId;
    
    
    /**
     * @param int $id
     *
     * @return GroupId
     */
    public function createGroupId(int $id): GroupId;
    
    
    /**
     * @param string $action
     *
     * @return PermissionAction
     */
    public function createPermissionAction(string $action): PermissionAction;
    
    
    /**
     * @param array<int, string> $names
     *
     * @return RoleNames
     */
    public function createRoleNames(array $names): RoleNames;
    
    
    /**
     * @param array<int, string> $descriptions
     *
     * @return RoleDescriptions
     */
    public function createRoleDescriptions(array $descriptions): RoleDescriptions;
}