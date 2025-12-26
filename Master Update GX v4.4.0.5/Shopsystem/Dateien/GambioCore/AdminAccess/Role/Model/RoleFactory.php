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

namespace Gambio\Core\AdminAccess\Role\Model;

use Gambio\Core\AdminAccess\Role\AdminId;
use Gambio\Core\AdminAccess\Role\GroupId;
use Gambio\Core\AdminAccess\Role\Permission;
use Gambio\Core\AdminAccess\Role\PermissionAction;
use Gambio\Core\AdminAccess\Role\Permissions;
use Gambio\Core\AdminAccess\Role\Role;
use Gambio\Core\AdminAccess\Role\RoleDescriptions;
use Gambio\Core\AdminAccess\Role\RoleId;
use Gambio\Core\AdminAccess\Role\RoleIds;
use Gambio\Core\AdminAccess\Role\RoleNames;
use Gambio\Core\AdminAccess\Role\Roles;

/**
 * Class RoleFactory
 *
 * @package Gambio\Core\AdminAccess\Role\Models
 */
class RoleFactory implements \Gambio\Core\AdminAccess\Role\RoleFactory
{
    /**
     * @inheritDoc
     */
    public function createRole(
        int $id,
        array $names,
        array $descriptions,
        Permissions $permissions,
        int $sortOrder,
        bool $isProtected
    ): Role {
        return \Gambio\Core\AdminAccess\Role\Model\Role::create($this->createRoleId($id),
                                                                $this->createRoleNames($names),
                                                                $this->createRoleDescriptions($descriptions),
                                                                $permissions,
                                                                $sortOrder,
                                                                $isProtected);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createRoles(Role ...$roles): Roles
    {
        return \Gambio\Core\AdminAccess\Role\Model\Roles::create(...$roles);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createRoleId(int $id): RoleId
    {
        return \Gambio\Core\AdminAccess\Role\Model\RoleId::create($id);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createRoleIds(RoleId ...$ids): RoleIds
    {
        return \Gambio\Core\AdminAccess\Role\Model\RoleIds::create(...$ids);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createPermission(
        int $groupId,
        bool $readingGranted,
        bool $writingGranted,
        bool $deletingGranted
    ): Permission {
        return \Gambio\Core\AdminAccess\Role\Model\Permission::create($this->createGroupId($groupId),
                                                                      $readingGranted,
                                                                      $writingGranted,
                                                                      $deletingGranted);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createPermissions(Permission ...$permissions): Permissions
    {
        return \Gambio\Core\AdminAccess\Role\Model\Permissions::create(...$permissions);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createAdminId(int $id): AdminId
    {
        return \Gambio\Core\AdminAccess\Role\Model\AdminId::create($id);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createGroupId(int $id): GroupId
    {
        return \Gambio\Core\AdminAccess\Role\Model\GroupId::create($id);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createPermissionAction(string $action): PermissionAction
    {
        return \Gambio\Core\AdminAccess\Role\Model\PermissionAction::create($action);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createRoleNames(array $names): RoleNames
    {
        return \Gambio\Core\AdminAccess\Role\Model\RoleNames::create($names);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createRoleDescriptions(array $descriptions): RoleDescriptions
    {
        return \Gambio\Core\AdminAccess\Role\Model\RoleDescriptions::create($descriptions);
    }
}