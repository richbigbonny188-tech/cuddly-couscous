<?php
/* --------------------------------------------------------------
   GroupService.php 2020-10-05
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess;

use Gambio\Core\AdminAccess\Group\Exceptions\DeletionOfGroupsFailed;
use Gambio\Core\AdminAccess\Group\Exceptions\GroupDoesNotExist;
use Gambio\Core\AdminAccess\Group\Exceptions\StorageOfGroupsFailed;
use Gambio\Core\AdminAccess\Group\Group;
use Gambio\Core\AdminAccess\Group\GroupIds;
use Gambio\Core\AdminAccess\Group\Groups;

/**
 * Interface GroupService
 *
 * @package Gambio\Core\AdminAccess\Group
 */
interface GroupService
{
    /**
     * Returns all available access groups.
     *
     * @return Groups
     */
    public function getGroups(): Groups;
    
    
    /**
     * Returns a specific access group based on the provided group ID.
     *
     * @param int $groupId
     *
     * @return Group
     *
     * @throws GroupDoesNotExist
     */
    public function getGroupById(int $groupId): Group;
    
    
    /**
     * Returns a specific access group based on the provided group item type and descriptor.
     *
     * @param string $type
     * @param string $descriptor
     *
     * @return Group
     *
     * @throws GroupDoesNotExist
     */
    public function getGroupByTypeAndDescriptor(string $type, string $descriptor): Group;
    
    
    /**
     * Returns the best-matching access group based on the provided group item type and descriptor.
     *
     * If there is no group for a specific route or controller (e.g. `/admin/route/specific` or `controller/action`),
     * then it's possible that a group will be returned, which belongs to a more generic route or controller
     * (e.g. `/admin/route` or `controller`).
     *
     * If absolutely no group matches, this service will return the group for unknown items.
     *
     * @param string $type
     * @param string $descriptor
     *
     * @return Group
     */
    public function findGroupByTypeAndDescriptor(string $type, string $descriptor): Group;
    
    
    /**
     * Returns a the access group for unknown items based on the provided group item type.
     *
     * @param string $type
     *
     * @return Group
     */
    public function getGroupForUnknownItemsByType(string $type): Group;
    
    
    /**
     * Creates a new access group based on the provided names, descriptions, sort order and protection status.
     * The provided names and description arrays need to map language ID (key) and name or description (value).
     *
     * @param array<int, string> $names
     * @param array<int, string> $descriptions
     * @param int                $sortOrder
     * @param bool               $isProtected
     * @param int|null           $parentGroupId Provide null, if there is no parent group.
     *
     * @return Group
     */
    public function createGroup(
        array $names,
        array $descriptions,
        int $sortOrder,
        bool $isProtected = false,
        ?int $parentGroupId = null
    ): Group;
    
    
    /**
     * Stores (updates) all provided access groups and returns their group IDs.
     *
     * @param Group ...$groups
     *
     * @return GroupIds
     *
     * @throws StorageOfGroupsFailed
     */
    public function storeGroups(Group ...$groups): GroupIds;
    
    
    /**
     * Deletes all access groups based on the provided group IDs.
     *
     * @param int[] $groupIds
     *
     * @throws DeletionOfGroupsFailed
     */
    public function deleteGroups(int ...$groupIds): void;
}