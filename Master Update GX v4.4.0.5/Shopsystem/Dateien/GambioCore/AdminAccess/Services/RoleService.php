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

namespace Gambio\Core\AdminAccess\Services;

use Gambio\Core\AdminAccess\Role\Repository\RoleRepository;
use Gambio\Core\AdminAccess\Role\Role;
use Gambio\Core\AdminAccess\Role\RoleFactory;
use Gambio\Core\AdminAccess\Role\RoleIds;
use Gambio\Core\AdminAccess\Role\Roles;

/**
 * Class RoleService
 *
 * @package Gambio\Core\AdminAccess\Role\Services
 */
class RoleService implements \Gambio\Core\AdminAccess\RoleService
{
    /**
     * @var RoleRepository
     */
    private $repository;
    
    /**
     * @var RoleFactory
     */
    private $factory;
    
    
    /**
     * RoleService constructor.
     *
     * @param RoleRepository $repository
     * @param RoleFactory    $factory
     */
    public function __construct(RoleRepository $repository, RoleFactory $factory)
    {
        $this->repository = $repository;
        $this->factory    = $factory;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getRoles(): Roles
    {
        return $this->repository->getRoles();
    }
    
    
    /**
     * @inheritDoc
     */
    public function getRolesByAdmin(int $adminId): Roles
    {
        return $this->repository->getRolesByAdmin($this->factory->createAdminId($adminId));
    }
    
    
    /**
     * @inheritDoc
     */
    public function getRoleById(int $roleId): Role
    {
        return $this->repository->getRoleById($this->factory->createRoleId($roleId));
    }
    
    
    /**
     * @inheritDoc
     */
    public function createRole(array $names, array $descriptions, int $sortOrder, bool $isProtected = false): Role
    {
        $id = $this->repository->createRole($this->factory->createRoleNames($names),
                                            $this->factory->createRoleDescriptions($descriptions),
                                            $sortOrder,
                                            $isProtected);
        
        return $this->factory->createRole($id->value(),
                                          $names,
                                          $descriptions,
                                          $this->factory->createPermissions(),
                                          $sortOrder,
                                          $isProtected);
    }
    
    
    /**
     * @inheritDoc
     */
    public function storeRoles(Role ...$roles): RoleIds
    {
        return $this->repository->storeRoles(...$roles);
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteRoles(int ...$roleIds): void
    {
        $ids = array_map([$this->factory, 'createRoleId'], $roleIds);
        
        $this->repository->deleteRoles($this->factory->createRoleIds(...$ids));
    }
}