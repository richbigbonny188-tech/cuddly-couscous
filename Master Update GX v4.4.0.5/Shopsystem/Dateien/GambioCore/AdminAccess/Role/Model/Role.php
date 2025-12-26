<?php
/* --------------------------------------------------------------
   Role.php 2020-07-22
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role\Model;

use Gambio\Core\AdminAccess\Role\Events\UpdatedNamesAndDescriptions;
use Gambio\Core\AdminAccess\Role\Events\UpdatedPermission;
use Gambio\Core\AdminAccess\Role\Events\UpdatedSortOrder;
use Gambio\Core\AdminAccess\Role\GroupId;
use Gambio\Core\AdminAccess\Role\Permission;
use Gambio\Core\AdminAccess\Role\PermissionAction;
use Gambio\Core\AdminAccess\Role\Permissions;
use Gambio\Core\AdminAccess\Role\RoleDescriptions;
use Gambio\Core\AdminAccess\Role\RoleId;
use Gambio\Core\AdminAccess\Role\RoleNames;
use Gambio\Core\Event\Abstracts\AbstractEventRaisingEntity;

/**
 * Class Role
 *
 * @package Gambio\Core\AdminAccess\Role\Models
 */
class Role extends AbstractEventRaisingEntity implements \Gambio\Core\AdminAccess\Role\Role
{
    /**
     * @var RoleId
     */
    private $id;
    
    /**
     * @var RoleNames
     */
    private $names;
    
    /**
     * @var RoleDescriptions
     */
    private $descriptions;
    
    /**
     * @var Permissions
     */
    private $permissions;
    
    /**
     * @var int
     */
    private $sortOrder;
    
    /**
     * @var bool
     */
    private $isProtected;
    
    
    /**
     * Role constructor.
     *
     * @param RoleId           $id
     * @param RoleNames        $names
     * @param RoleDescriptions $descriptions
     * @param Permissions      $permissions
     * @param int              $sortOrder
     * @param bool             $isProtected
     */
    private function __construct(
        RoleId $id,
        RoleNames $names,
        RoleDescriptions $descriptions,
        Permissions $permissions,
        int $sortOrder,
        bool $isProtected
    ) {
        $this->id           = $id;
        $this->names        = $names;
        $this->descriptions = $descriptions;
        $this->permissions  = $permissions;
        $this->sortOrder    = $sortOrder;
        $this->isProtected  = $isProtected;
    }
    
    
    /**
     * @param RoleId           $id
     * @param RoleNames        $names
     * @param RoleDescriptions $descriptions
     * @param Permissions      $permissions
     * @param int              $sortOrder
     * @param bool             $isProtected
     *
     * @return Role
     */
    public static function create(
        RoleId $id,
        RoleNames $names,
        RoleDescriptions $descriptions,
        Permissions $permissions,
        int $sortOrder,
        bool $isProtected
    ): Role {
        return new self($id, $names, $descriptions, $permissions, $sortOrder, $isProtected);
    }
    
    
    /**
     * @inheritDoc
     */
    public function id(): int
    {
        return $this->id->value();
    }
    
    
    /**
     * @inheritDoc
     */
    public function name(int $languageId): string
    {
        return $this->names->getName($languageId);
    }
    
    
    /**
     * @inheritDoc
     */
    public function description(int $languageId): string
    {
        return $this->descriptions->getDescription($languageId);
    }
    
    
    /**
     * @inheritDoc
     */
    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
    
    
    /**
     * @inheritDoc
     */
    public function permissions(): Permissions
    {
        return $this->permissions;
    }
    
    
    /**
     * @inheritDoc
     */
    public function isProtected(): bool
    {
        return $this->isProtected;
    }
    
    
    /**
     * @inheritDoc
     */
    public function checkPermission(PermissionAction $action, GroupId $groupId): bool
    {
        $permission = $this->permissions->getPermissionByGroupId($groupId);
        
        switch ($action->value()) {
            case PermissionAction::READ:
                return ($permission !== null) ? $permission->readingGranted() : false;
            case PermissionAction::WRITE:
                return ($permission !== null) ? $permission->writingGranted() : false;
            case PermissionAction::DELETE:
                return ($permission !== null) ? $permission->deletingGranted() : false;
            default:
                return false;
        }
    }
    
    
    /**
     * @inheritDoc
     */
    public function updateNamesAndDescriptions(
        RoleNames $names,
        RoleDescriptions $descriptions
    ): void {
        $this->names        = $names;
        $this->descriptions = $descriptions;
        
        $this->raiseEvent(UpdatedNamesAndDescriptions::create($this->id, $names, $descriptions));
    }
    
    
    /**
     * @inheritDoc
     */
    public function updateSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
        
        $this->raiseEvent(UpdatedSortOrder::create($this->id, $sortOrder));
    }
    
    
    /**
     * @inheritDoc
     */
    public function updatePermission(Permission $permission): void
    {
        $this->permissions = $this->permissions->updatePermission($permission);
        
        $this->raiseEvent(UpdatedPermission::create($this->id, $permission));
    }
}