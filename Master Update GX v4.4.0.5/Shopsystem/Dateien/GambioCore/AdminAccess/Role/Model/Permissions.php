<?php
/* --------------------------------------------------------------
   Permissions.php 2020-07-21
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role\Model;

use ArrayIterator;
use Gambio\Core\AdminAccess\Role\GroupId;
use Gambio\Core\AdminAccess\Role\Permission;

/**
 * Class Permissions
 *
 * @package Gambio\Core\AdminAccess\Role\Models
 */
class Permissions implements \Gambio\Core\AdminAccess\Role\Permissions
{
    /**
     * @var Permission[]
     */
    private $permissions;
    
    
    /**
     * Permission constructor.
     *
     * @param Permission ...$permissions
     */
    private function __construct(Permission ...$permissions)
    {
        $this->permissions = [];
        foreach ($permissions as $permission) {
            $this->permissions[$permission->groupId()] = $permission;
        }
    }
    
    
    /**
     * @param Permission ...$permissions
     *
     * @return Permissions
     */
    public static function create(Permission ...$permissions): Permissions
    {
        return new self(...$permissions);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getIterator(): iterable
    {
        return new ArrayIterator($this->permissions);
    }
    
    
    /**
     * @inheritDoc
     */
    public function updatePermission(Permission $permission): \Gambio\Core\AdminAccess\Role\Permissions
    {
        $permissions                         = $this->permissions;
        $permissions[$permission->groupId()] = $permission;
        
        return new self(...$permissions);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getPermissionByGroupId(GroupId $groupId): ?Permission
    {
        return $this->permissions[$groupId->value()] ?? null;
    }
}