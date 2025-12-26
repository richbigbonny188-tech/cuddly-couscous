<?php
/* --------------------------------------------------------------
   GroupMapper.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Group\Repository;

use Gambio\Core\AdminAccess\Group\Group;
use Gambio\Core\AdminAccess\Group\GroupFactory;
use Gambio\Core\AdminAccess\Group\GroupId;
use Gambio\Core\AdminAccess\Group\GroupIds;
use Gambio\Core\AdminAccess\Group\GroupItem;
use Gambio\Core\AdminAccess\Group\Groups;

/**
 * Class GroupMapper
 *
 * @package Gambio\Core\AdminAccess\Group\Repository
 */
class GroupMapper
{
    /**
     * @var GroupFactory
     */
    private $factory;
    
    
    /**
     * GroupMapper constructor.
     *
     * @param GroupFactory $factory
     */
    public function __construct(GroupFactory $factory)
    {
        $this->factory = $factory;
    }
    
    
    /**
     * @param array $itemData
     *
     * @return GroupItem
     */
    private function mapGroupItem(array $itemData): GroupItem
    {
        return $this->factory->createGroupItem($itemData['type'], $itemData['descriptor']);
    }
    
    
    /**
     * @param array $groupData
     *
     * @return Group
     */
    public function mapGroup(array $groupData): Group
    {
        $groupItems = array_map([$this, 'mapGroupItem'], $groupData['items']);
        
        return $this->factory->createGroup($groupData['id'],
                                           $groupData['parentGroupId'],
                                           $groupData['names'],
                                           $groupData['descriptions'],
                                           $this->factory->createGroupItems(...$groupItems),
                                           $groupData['sortOrder'],
                                           $groupData['isProtected']);
    }
    
    
    /**
     * @param array $groupsData
     *
     * @return Groups
     */
    public function mapGroups(array $groupsData): Groups
    {
        $groups = array_map([$this, 'mapGroup'], $groupsData);
        
        return $this->factory->createGroups(...$groups);
    }
    
    
    /**
     * @param int $groupId
     *
     * @return GroupId
     */
    public function mapGroupId(int $groupId): GroupId
    {
        return $this->factory->createGroupId($groupId);
    }
    
    
    /**
     * @param array $groupIds
     *
     * @return GroupIds
     */
    public function mapGroupIds(array $groupIds): GroupIds
    {
        $groupIds = array_map([$this, 'mapGroupId'], $groupIds);
        
        return $this->factory->createGroupIds(...$groupIds);
    }
}