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

namespace Gambio\Core\AdminAccess\Role;

use IteratorAggregate;

/**
 * Interface Permissions
 *
 * @package Gambio\Core\AdminAccess\Role
 */
interface Permissions extends IteratorAggregate
{
    /**
     * @return Permission[]
     */
    public function getIterator(): iterable;
    
    
    /**
     * @param Permission $permission
     *
     * @return Permissions
     */
    public function updatePermission(Permission $permission): Permissions;
    
    
    /**
     * @param GroupId $groupId
     *
     * @return Permission|null
     */
    public function getPermissionByGroupId(GroupId $groupId): ?Permission;
}