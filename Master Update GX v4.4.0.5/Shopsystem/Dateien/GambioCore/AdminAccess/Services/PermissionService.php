<?php
/* --------------------------------------------------------------
   PermissionService.php 2020-10-05
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Services;

use Gambio\Core\AdminAccess\GroupService;
use Gambio\Core\AdminAccess\Role\RoleFactory;
use Gambio\Core\AdminAccess\RoleService as RoleServiceInterface;

/**
 * Class PermissionService
 *
 * @package Gambio\Core\AdminAccess\Role\Services
 */
class PermissionService implements \Gambio\Core\AdminAccess\PermissionService
{
    private const MAIN_ADMIN_ID = 1;
    
    /**
     * @var GroupService
     */
    private $groupService;
    
    /**
     * @var RoleServiceInterface
     */
    private $roleService;
    
    /**
     * @var RoleFactory
     */
    private $factory;
    
    
    /**
     * PermissionService constructor.
     *
     * @param GroupService         $groupService
     * @param RoleServiceInterface $roleService
     * @param RoleFactory          $factory
     */
    public function __construct(
        GroupService $groupService,
        RoleServiceInterface $roleService,
        RoleFactory $factory
    ) {
        $this->groupService = $groupService;
        $this->roleService  = $roleService;
        $this->factory      = $factory;
    }
    
    
    /**
     * @inheritDoc
     */
    public function checkAdminPermission(
        int $adminId,
        string $action,
        string $groupItemType,
        string $groupItemDescriptor
    ): bool {
        if ($adminId === self::MAIN_ADMIN_ID) {
            return true;
        }
        
        $group     = $this->groupService->findGroupByTypeAndDescriptor($groupItemType, $groupItemDescriptor);
        $roles     = $this->roleService->getRolesByAdmin($adminId);
        $actionObj = $this->factory->createPermissionAction($action);
        $groupId   = $this->factory->createGroupId($group->id());
        foreach ($roles as $role) {
            if ($role->checkPermission($actionObj, $groupId)) {
                return true;
            }
        }
        
        return false;
    }
    
    
    /**
     * @inheritDoc
     */
    public function setRolePermissionsForGroup(
        int $roleId,
        string $groupItemType,
        string $groupItemDescriptor,
        bool $readPermission,
        bool $writingPermission,
        bool $deletingPermission
    ): void {
        $group      = $this->groupService->getGroupByTypeAndDescriptor($groupItemType, $groupItemDescriptor);
        $role       = $this->roleService->getRoleById($roleId);
        $permission = $this->factory->createPermission($group->id(),
                                                       $readPermission,
                                                       $writingPermission,
                                                       $deletingPermission);
        $role->updatePermission($permission);
        
        $this->roleService->storeRoles($role);
    }
}