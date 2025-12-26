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

namespace Gambio\Core\AdminAccess\Role;

use Gambio\Core\Event\EventRaisingEntity;

/**
 * Interface Role
 *
 * @package Gambio\Core\AdminAccess\Role
 */
interface Role extends EventRaisingEntity
{
    /**
     * @return int
     */
    public function id(): int;
    
    
    /**
     * @param int $languageId
     *
     * @return string
     */
    public function name(int $languageId): string;
    
    
    /**
     * @param int $languageId
     *
     * @return string
     */
    public function description(int $languageId): string;
    
    
    /**
     * @return int
     */
    public function sortOrder(): int;
    
    
    /**
     * @return Permissions
     */
    public function permissions(): Permissions;
    
    
    /**
     * @return bool
     */
    public function isProtected(): bool;
    
    
    /**
     * @param PermissionAction $action
     * @param GroupId          $groupId
     *
     * @return bool
     */
    public function checkPermission(PermissionAction $action, GroupId $groupId): bool;
    
    
    /**
     * @param RoleNames        $names
     * @param RoleDescriptions $descriptions
     */
    public function updateNamesAndDescriptions(
        RoleNames $names,
        RoleDescriptions $descriptions
    ): void;
    
    
    /**
     * @param int $sortOrder
     */
    public function updateSortOrder(int $sortOrder): void;
    
    
    /**
     * @param Permission $permission
     */
    public function updatePermission(Permission $permission): void;
}