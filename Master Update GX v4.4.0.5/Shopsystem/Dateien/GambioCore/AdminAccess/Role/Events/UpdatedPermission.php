<?php
/* --------------------------------------------------------------
   UpdatedPermission.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role\Events;

use Gambio\Core\AdminAccess\Role\Permission;
use Gambio\Core\AdminAccess\Role\RoleId;

/**
 * Class UpdatedPermission
 *
 * @package Gambio\Core\AdminAccess\Role\Events
 */
class UpdatedPermission
{
    /**
     * @var RoleId
     */
    private $roleId;
    
    /**
     * @var Permission
     */
    private $permission;
    
    
    /**
     * UpdatedPermission constructor.
     *
     * @param RoleId     $roleId
     * @param Permission $permission
     */
    private function __construct(RoleId $roleId, Permission $permission)
    {
        $this->roleId     = $roleId;
        $this->permission = $permission;
    }
    
    
    /**
     * @param RoleId     $roleId
     * @param Permission $permission
     *
     * @return UpdatedPermission
     */
    public static function create(RoleId $roleId, Permission $permission): UpdatedPermission
    {
        return new self($roleId, $permission);
    }
    
    
    /**
     * @return RoleId
     */
    public function roleId(): RoleId
    {
        return $this->roleId;
    }
    
    
    /**
     * @return Permission
     */
    public function permission(): Permission
    {
        return $this->permission;
    }
}