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

namespace Gambio\Core\AdminAccess\Services;

use Gambio\Core\AdminAccess\Group\Exceptions\GroupDoesNotExist;
use Gambio\Core\AdminAccess\Group\Group;
use Gambio\Core\AdminAccess\Group\GroupFactory;
use Gambio\Core\AdminAccess\Group\GroupIds;
use Gambio\Core\AdminAccess\Group\GroupItem;
use Gambio\Core\AdminAccess\Group\Groups;
use Gambio\Core\AdminAccess\Group\Repository\GroupRepository;
use RuntimeException;

/**
 * Class GroupService
 *
 * @package Gambio\Core\AdminAccess\Group\Services
 */
class GroupService implements \Gambio\Core\AdminAccess\GroupService
{
    private const UNKNOWN_ITEM_DESCRIPTOR = 'unknown-admin-access-item';
    
    /**
     * @var GroupRepository
     */
    private $repository;
    
    /**
     * @var GroupFactory
     */
    private $factory;
    
    
    /**
     * GroupService constructor.
     *
     * @param GroupRepository $repository
     * @param GroupFactory    $factory
     */
    public function __construct(GroupRepository $repository, GroupFactory $factory)
    {
        $this->repository = $repository;
        $this->factory    = $factory;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getGroups(): Groups
    {
        return $this->repository->getGroups();
    }
    
    
    /**
     * @inheritDoc
     */
    public function getGroupById(int $groupId): Group
    {
        return $this->repository->getGroupById($this->factory->createGroupId($groupId));
    }
    
    
    /**
     * @inheritDoc
     */
    public function getGroupByTypeAndDescriptor(string $type, string $descriptor): Group
    {
        return $this->repository->getGroupByItem($this->factory->createGroupItem($type, $descriptor));
    }
    
    
    /**
     * @inheritDoc
     *
     * @note If this logic needs to be extended, e.g. for another type, then it should be refactored and some
     *       strategy pattern (or so) should be implemented. Currently I would assume, that this logic would
     *       become simpler, because the types `PAGE`, `CONTROLLER`, and `AJAX_HANDLER` will be removed.
     */
    public function findGroupByTypeAndDescriptor(string $type, string $descriptor): Group
    {
        try {
            return $this->repository->getGroupByItem($this->factory->createGroupItem($type, $descriptor));
        } catch (GroupDoesNotExist $e) {
            $moreGenericDescriptor = rtrim($descriptor, '/');
            if ($type === GroupItem::PAGE_TYPE || $type === GroupItem::AJAX_HANDLER_TYPE
                || (strrpos($moreGenericDescriptor, '/') > 0) === false) {
                return $this->getGroupForUnknownItemsByType($type);
            }
            $moreGenericDescriptor = substr($moreGenericDescriptor, 0, strrpos($moreGenericDescriptor, '/'));
            
            return $this->findGroupByTypeAndDescriptor($type, $moreGenericDescriptor);
        }
    }
    
    
    /**
     * @inheritDoc
     */
    public function getGroupForUnknownItemsByType(string $type): Group
    {
        try {
            return $this->repository->getGroupByItem($this->factory->createGroupItem($type,
                                                                                     self::UNKNOWN_ITEM_DESCRIPTOR));
        } catch (GroupDoesNotExist $e) {
            throw new RuntimeException('Missing Admin Access group for unknown items of type "' . $type . '".');
        }
    }
    
    
    /**
     * @inheritDoc
     */
    public function createGroup(
        array $names,
        array $descriptions,
        int $sortOrder,
        bool $isProtected = false,
        ?int $parentGroupId = null
    ): Group {
        $id = $this->repository->createGroup($this->factory->createGroupNames($names),
                                             $this->factory->createGroupDescriptions($descriptions),
                                             $sortOrder,
                                             $isProtected,
                                             $parentGroupId);
        
        return $this->factory->createGroup($id->value(),
                                           $parentGroupId,
                                           $names,
                                           $descriptions,
                                           $this->factory->createGroupItems(),
                                           $sortOrder,
                                           $isProtected);
    }
    
    
    /**
     * @inheritDoc
     */
    public function storeGroups(Group ...$groups): GroupIds
    {
        return $this->repository->storeGroups(...$groups);
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteGroups(int ...$groupIds): void
    {
        $ids = array_map([$this->factory, 'createGroupId'], $groupIds);
        
        $this->repository->deleteGroups($this->factory->createGroupIds(...$ids));
    }
}