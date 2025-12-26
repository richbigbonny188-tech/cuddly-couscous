<?php
/* --------------------------------------------------------------
   RoleMapper.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role\Repository;

use Gambio\Core\AdminAccess\Role\Permission;
use Gambio\Core\AdminAccess\Role\Role;
use Gambio\Core\AdminAccess\Role\RoleFactory;
use Gambio\Core\AdminAccess\Role\RoleId;
use Gambio\Core\AdminAccess\Role\RoleIds;
use Gambio\Core\AdminAccess\Role\Roles;

/**
 * Class RoleMapper
 *
 * @package Gambio\Core\AdminAccess\Role\Repository
 */
class RoleMapper
{
    /**
     * @var RoleFactory
     */
    private $factory;
    
    
    /**
     * RoleMapper constructor.
     *
     * @param RoleFactory $factory
     */
    public function __construct(RoleFactory $factory)
    {
        $this->factory = $factory;
    }
    
    
    /**
     * @param array $permissionData
     *
     * @return Permission
     */
    private function mapRolePermission(array $permissionData): Permission
    {
        return $this->factory->createPermission($permissionData['groupId'],
                                                $permissionData['readingGranted'],
                                                $permissionData['writingGranted'],
                                                $permissionData['deletingGranted']);
    }
    
    
    /**
     * @param array $roleData
     *
     * @return Role
     */
    public function mapRole(array $roleData): Role
    {
        $rolePermissions = array_map([$this, 'mapRolePermission'], $roleData['permissions']);
        
        return $this->factory->createRole($roleData['id'],
                                          $roleData['names'],
                                          $roleData['descriptions'],
                                          $this->factory->createPermissions(...$rolePermissions),
                                          $roleData['sortOrder'],
                                          $roleData['isProtected']);
    }
    
    
    /**
     * @param array $rolesData
     *
     * @return Roles
     */
    public function mapRoles(array $rolesData): Roles
    {
        $roles = array_map([$this, 'mapRole'], $rolesData);
        
        return $this->factory->createRoles(...$roles);
    }
    
    
    /**
     * @param int $roleId
     *
     * @return RoleId
     */
    public function mapRoleId(int $roleId): RoleId
    {
        return $this->factory->createRoleId($roleId);
    }
    
    
    /**
     * @param array $roleIds
     *
     * @return RoleIds
     */
    public function mapRoleIds(array $roleIds): RoleIds
    {
        $roleIds = array_map([$this, 'mapRoleId'], $roleIds);
        
        return $this->factory->createRoleIds(...$roleIds);
    }
}