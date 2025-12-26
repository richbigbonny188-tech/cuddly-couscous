<?php
/* --------------------------------------------------------------
   GroupFactory.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Group;

/**
 * Interface GroupFactory
 *
 * @package Gambio\Core\AdminAccess\Group
 */
interface GroupFactory
{
    /**
     * @param int                $id
     * @param int|null           $parentGroupId Provide null, if no parent group is defined
     * @param array<int, string> $names
     * @param array<int, string> $descriptions
     * @param GroupItems         $items
     * @param int                $sortOrder
     * @param bool               $isProtected
     *
     * @return Group
     */
    public function createGroup(
        int $id,
        ?int $parentGroupId,
        array $names,
        array $descriptions,
        GroupItems $items,
        int $sortOrder,
        bool $isProtected
    ): Group;
    
    
    /**
     * @param Group[] $groups
     *
     * @return Groups
     */
    public function createGroups(Group ...$groups): Groups;
    
    
    /**
     * @param int $id
     *
     * @return GroupId
     */
    public function createGroupId(int $id): GroupId;
    
    
    /**
     * @param int $id
     *
     * @return ParentGroupId
     */
    public function createParentGroupId(int $id): ParentGroupId;
    
    
    /**
     * @param GroupId[] $ids
     *
     * @return GroupIds
     */
    public function createGroupIds(GroupId ...$ids): GroupIds;
    
    
    /**
     * @param string $type
     * @param string $descriptor
     *
     * @return GroupItem
     */
    public function createGroupItem(string $type, string $descriptor): GroupItem;
    
    
    /**
     * @param GroupItem[] $groupItems
     *
     * @return GroupItems
     */
    public function createGroupItems(GroupItem ...$groupItems): GroupItems;
    
    
    /**
     * @param array<int, string> $names
     *
     * @return GroupNames
     */
    public function createGroupNames(array $names): GroupNames;
    
    
    /**
     * @param array<int, string> $descriptions
     *
     * @return GroupDescriptions
     */
    public function createGroupDescriptions(array $descriptions): GroupDescriptions;
}