<?php
/* --------------------------------------------------------------
   GroupRepository.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Group\Repository;

use Gambio\Core\AdminAccess\Group\Events\CreatedGroup;
use Gambio\Core\AdminAccess\Group\Events\DeletedGroup;
use Gambio\Core\AdminAccess\Group\Exceptions\DeletionOfGroupsFailed;
use Gambio\Core\AdminAccess\Group\Exceptions\GroupDoesNotExist;
use Gambio\Core\AdminAccess\Group\Exceptions\StorageOfGroupsFailed;
use Gambio\Core\AdminAccess\Group\Group;
use Gambio\Core\AdminAccess\Group\GroupDescriptions;
use Gambio\Core\AdminAccess\Group\GroupId;
use Gambio\Core\AdminAccess\Group\GroupIds;
use Gambio\Core\AdminAccess\Group\GroupItem;
use Gambio\Core\AdminAccess\Group\GroupNames;
use Gambio\Core\AdminAccess\Group\Groups;
use Gambio\Core\Event\Abstracts\AbstractEventDispatchingRepository;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class GroupRepository
 *
 * @package Gambio\Core\AdminAccess\Group\Repositories
 */
class GroupRepository extends AbstractEventDispatchingRepository
{
    /**
     * @var GroupMapper
     */
    private $mapper;
    
    /**
     * @var GroupReader
     */
    private $reader;
    
    /**
     * @var GroupWriter
     */
    private $writer;
    
    
    /**
     * GroupRepository constructor.
     *
     * @param GroupMapper              $mapper
     * @param GroupReader              $reader
     * @param GroupWriter              $writer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        GroupMapper $mapper,
        GroupReader $reader,
        GroupWriter $writer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->mapper = $mapper;
        $this->reader = $reader;
        $this->writer = $writer;
        
        $this->setEventDispatcher($eventDispatcher);
    }
    
    
    /**
     * @return Groups
     */
    public function getGroups(): Groups
    {
        return $this->mapper->mapGroups($this->reader->getGroupsData());
    }
    
    
    /**
     * @param GroupId $groupId
     *
     * @return Group
     *
     * @throws GroupDoesNotExist
     */
    public function getGroupById(GroupId $groupId): Group
    {
        return $this->mapper->mapGroup($this->reader->getGroupDataById($groupId));
    }
    
    
    /**
     * @param GroupItem $groupItem
     *
     * @return Group
     *
     * @throws GroupDoesNotExist
     */
    public function getGroupByItem(GroupItem $groupItem): Group
    {
        return $this->mapper->mapGroup($this->reader->getGroupDataByItem($groupItem));
    }
    
    
    /**
     * @param GroupNames        $names
     * @param GroupDescriptions $descriptions
     * @param int               $sortOrder
     * @param bool              $isProtected
     * @param int|null          $parentGroupId
     *
     * @return GroupId
     */
    public function createGroup(
        GroupNames $names,
        GroupDescriptions $descriptions,
        int $sortOrder,
        bool $isProtected = false,
        ?int $parentGroupId = null
    ): GroupId {
        $group = $this->writer->createGroup($names, $descriptions, $sortOrder, $isProtected, $parentGroupId);
        $id    = $this->mapper->mapGroupId($group);
        
        $this->dispatchEvent(CreatedGroup::create($id));
        
        return $id;
    }
    
    
    /**
     * @param Group ...$groups
     *
     * @return GroupIds
     *
     * @throws StorageOfGroupsFailed
     */
    public function storeGroups(Group ...$groups): GroupIds
    {
        $ids = $this->mapper->mapGroupIds($this->writer->storeGroups(...$groups));
        foreach ($groups as $index => $group) {
            $this->dispatchEntityEvents($group);
        }
        
        return $ids;
    }
    
    
    /**
     * @param GroupIds $groupIds
     *
     * @throws DeletionOfGroupsFailed
     */
    public function deleteGroups(GroupIds $groupIds): void
    {
        $this->writer->deleteGroups($groupIds);
        foreach ($groupIds as $groupId) {
            $this->dispatchEvent(DeletedGroup::create($groupId));
        }
    }
}