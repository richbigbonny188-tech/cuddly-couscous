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

namespace Gambio\Core\AdminAccess\Group\Model;

use Gambio\Core\AdminAccess\Group\Group;
use Gambio\Core\AdminAccess\Group\GroupDescriptions;
use Gambio\Core\AdminAccess\Group\GroupId;
use Gambio\Core\AdminAccess\Group\GroupIds;
use Gambio\Core\AdminAccess\Group\GroupItem;
use Gambio\Core\AdminAccess\Group\GroupItems;
use Gambio\Core\AdminAccess\Group\GroupNames;
use Gambio\Core\AdminAccess\Group\Groups;
use Gambio\Core\AdminAccess\Group\ParentGroupId;

/**
 * Class GroupFactory
 *
 * @package Gambio\Core\AdminAccess\Group\Models
 */
class GroupFactory implements \Gambio\Core\AdminAccess\Group\GroupFactory
{
    /**
     * @inheritDoc
     */
    public function createGroup(
        int $id,
        ?int $parentGroupId,
        array $names,
        array $descriptions,
        GroupItems $items,
        int $sortOrder,
        bool $isProtected
    ): Group {
        if ($parentGroupId === null) {
            return \Gambio\Core\AdminAccess\Group\Model\Group::createWithoutParent($this->createGroupId($id),
                                                                                    $this->createGroupNames($names),
                                                                                    $this->createGroupDescriptions($descriptions),
                                                                                    $items,
                                                                                    $sortOrder,
                                                                                    $isProtected);
        }
        
        return \Gambio\Core\AdminAccess\Group\Model\Group::createWithParent($this->createGroupId($id),
                                                                             $this->createParentGroupId($parentGroupId),
                                                                             $this->createGroupNames($names),
                                                                             $this->createGroupDescriptions($descriptions),
                                                                             $items,
                                                                             $sortOrder,
                                                                             $isProtected);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createGroups(Group ...$groups): Groups
    {
        return \Gambio\Core\AdminAccess\Group\Model\Groups::create(...$groups);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createGroupId(int $id): GroupId
    {
        return \Gambio\Core\AdminAccess\Group\Model\GroupId::create($id);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createParentGroupId(int $id): ParentGroupId
    {
        return \Gambio\Core\AdminAccess\Group\Model\ParentGroupId::create($id);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createGroupIds(GroupId ...$ids): GroupIds
    {
        return \Gambio\Core\AdminAccess\Group\Model\GroupIds::create(...$ids);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createGroupItem(string $type, string $descriptor): GroupItem
    {
        return \Gambio\Core\AdminAccess\Group\Model\GroupItem::create(GroupItemType::create($type), $descriptor);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createGroupItems(GroupItem ...$groupItems): GroupItems
    {
        return \Gambio\Core\AdminAccess\Group\Model\GroupItems::create(...$groupItems);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createGroupNames(array $names): GroupNames
    {
        return \Gambio\Core\AdminAccess\Group\Model\GroupNames::create($names);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createGroupDescriptions(array $descriptions): GroupDescriptions
    {
        return \Gambio\Core\AdminAccess\Group\Model\GroupDescriptions::create($descriptions);
    }
}