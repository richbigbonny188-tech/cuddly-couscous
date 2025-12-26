<?php
/* --------------------------------------------------------------
   RoleRepository.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role\Repository;

use Gambio\Core\AdminAccess\Role\AdminId;
use Gambio\Core\AdminAccess\Role\Events\CreatedRole;
use Gambio\Core\AdminAccess\Role\Events\DeletedRole;
use Gambio\Core\AdminAccess\Role\Exceptions\DeletionOfRolesFailed;
use Gambio\Core\AdminAccess\Role\Exceptions\RoleDoesNotExist;
use Gambio\Core\AdminAccess\Role\Exceptions\StorageOfRolesFailed;
use Gambio\Core\AdminAccess\Role\Role;
use Gambio\Core\AdminAccess\Role\RoleDescriptions;
use Gambio\Core\AdminAccess\Role\RoleId;
use Gambio\Core\AdminAccess\Role\RoleIds;
use Gambio\Core\AdminAccess\Role\RoleNames;
use Gambio\Core\AdminAccess\Role\Roles;
use Gambio\Core\Event\Abstracts\AbstractEventDispatchingRepository;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class RoleRepository
 *
 * @package Gambio\Core\AdminAccess\Role\Repositories
 */
class RoleRepository extends AbstractEventDispatchingRepository
{
    /**
     * @var RoleMapper
     */
    private $mapper;
    
    /**
     * @var RoleReader
     */
    private $reader;
    
    /**
     * @var RoleWriter
     */
    private $writer;
    
    
    /**
     * RoleRepository constructor.
     *
     * @param RoleMapper               $mapper
     * @param RoleReader               $reader
     * @param RoleWriter               $writer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        RoleMapper $mapper,
        RoleReader $reader,
        RoleWriter $writer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->mapper = $mapper;
        $this->reader = $reader;
        $this->writer = $writer;
        
        $this->setEventDispatcher($eventDispatcher);
    }
    
    
    /**
     * @return Roles
     */
    public function getRoles(): Roles
    {
        return $this->mapper->mapRoles($this->reader->getRolesData());
    }
    
    
    /**
     * @param AdminId $admin
     *
     * @return Roles
     */
    public function getRolesByAdmin(AdminId $admin): Roles
    {
        return $this->mapper->mapRoles($this->reader->getRolesDataByAdmin($admin));
    }
    
    
    /**
     * @param RoleNames        $names
     * @param RoleDescriptions $descriptions
     * @param int              $sortOrder
     * @param bool             $isProtected
     *
     * @return RoleId
     */
    public function createRole(
        RoleNames $names,
        RoleDescriptions $descriptions,
        int $sortOrder,
        bool $isProtected = false
    ): RoleId {
        $role = $this->writer->createRole($names, $descriptions, $sortOrder, $isProtected);
        $id   = $this->mapper->mapRoleId($role);
        
        $this->dispatchEvent(CreatedRole::create($id));
        
        return $id;
    }
    
    
    /**
     * @param RoleId $roleId
     *
     * @return Role
     *
     * @throws RoleDoesNotExist
     */
    public function getRoleById(RoleId $roleId): Role
    {
        return $this->mapper->mapRole($this->reader->getRoleDataById($roleId));
    }
    
    
    /**
     * @param Role ...$roles
     *
     * @return RoleIds
     *
     * @throws StorageOfRolesFailed
     */
    public function storeRoles(Role ...$roles): RoleIds
    {
        $ids = $this->mapper->mapRoleIds($this->writer->storeRoles(...$roles));
        foreach ($roles as $index => $role) {
            $this->dispatchEntityEvents($role);
        }
        
        return $ids;
    }
    
    
    /**
     * @param RoleIds $roleIds
     *
     * @throws DeletionOfRolesFailed
     */
    public function deleteRoles(RoleIds $roleIds): void
    {
        $this->writer->deleteRoles($roleIds);
        foreach ($roleIds as $roleId) {
            $this->dispatchEvent(DeletedRole::create($roleId));
        }
    }
}