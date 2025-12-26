<?php
/* --------------------------------------------------------------
   Group.php 2020-07-22
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Group\Model;

use Gambio\Core\AdminAccess\Group\Events\AddedItemToGroup;
use Gambio\Core\AdminAccess\Group\Events\RemovedItemFromGroup;
use Gambio\Core\AdminAccess\Group\Events\UpdatedNamesAndDescriptions;
use Gambio\Core\AdminAccess\Group\Events\UpdatedSortOrder;
use Gambio\Core\AdminAccess\Group\Exceptions\ParentGroupIdDoesNotExist;
use Gambio\Core\AdminAccess\Group\GroupDescriptions;
use Gambio\Core\AdminAccess\Group\GroupId;
use Gambio\Core\AdminAccess\Group\GroupItem;
use Gambio\Core\AdminAccess\Group\GroupItems;
use Gambio\Core\AdminAccess\Group\GroupNames;
use Gambio\Core\AdminAccess\Group\ParentGroupId;
use Gambio\Core\Event\Abstracts\AbstractEventRaisingEntity;

/**
 * Class Group
 *
 * @package Gambio\Core\AdminAccess\Group\Models
 */
class Group extends AbstractEventRaisingEntity implements \Gambio\Core\AdminAccess\Group\Group
{
    /**
     * @var GroupId
     */
    private $id;
    
    /**
     * @var ParentGroupId|null
     */
    private $parent;
    
    /**
     * @var GroupNames
     */
    private $names;
    
    /**
     * @var GroupDescriptions
     */
    private $descriptions;
    
    /**
     * @var GroupItems
     */
    private $items;
    
    /**
     * @var int
     */
    private $sortOrder;
    
    /**
     * @var bool
     */
    private $isProtected;
    
    
    /**
     * Group constructor.
     *
     * @param GroupId            $id
     * @param ParentGroupId|null $parent
     * @param GroupNames         $names
     * @param GroupDescriptions  $descriptions
     * @param GroupItems         $items
     * @param int                $sortOrder
     * @param bool               $isProtected
     */
    private function __construct(
        GroupId $id,
        ?ParentGroupId $parent,
        GroupNames $names,
        GroupDescriptions $descriptions,
        GroupItems $items,
        int $sortOrder,
        bool $isProtected
    ) {
        $this->id           = $id;
        $this->parent       = $parent;
        $this->names        = $names;
        $this->descriptions = $descriptions;
        $this->items        = $items;
        $this->sortOrder    = $sortOrder;
        $this->isProtected  = $isProtected;
    }
    
    
    /**
     * @param GroupId           $id
     * @param ParentGroupId     $parent
     * @param GroupNames        $names
     * @param GroupDescriptions $descriptions
     * @param GroupItems        $items
     * @param int               $sortOrder
     * @param bool              $isProtected
     *
     * @return Group
     */
    public static function createWithParent(
        GroupId $id,
        ParentGroupId $parent,
        GroupNames $names,
        GroupDescriptions $descriptions,
        GroupItems $items,
        int $sortOrder,
        bool $isProtected
    ): Group {
        return new self($id, $parent, $names, $descriptions, $items, $sortOrder, $isProtected);
    }
    
    
    /**
     * @param GroupId           $id
     * @param GroupNames        $names
     * @param GroupDescriptions $descriptions
     * @param GroupItems        $items
     * @param int               $sortOrder
     * @param bool              $isProtected
     *
     * @return Group
     */
    public static function createWithoutParent(
        GroupId $id,
        GroupNames $names,
        GroupDescriptions $descriptions,
        GroupItems $items,
        int $sortOrder,
        bool $isProtected
    ): Group {
        return new self($id, null, $names, $descriptions, $items, $sortOrder, $isProtected);
    }
    
    
    /**
     * @inheritDoc
     */
    public function id(): int
    {
        return $this->id->value();
    }
    
    
    /**
     * @inheritDoc
     */
    public function parentGroupId(): int
    {
        if ($this->parent === null) {
            throw ParentGroupIdDoesNotExist::forGroup($this->id());
        }
        
        return $this->parent->value();
    }
    
    
    /**
     * @inheritDoc
     */
    public function name(int $languageId): string
    {
        return $this->names->getName($languageId);
    }
    
    
    /**
     * @inheritDoc
     */
    public function description(int $languageId): string
    {
        return $this->descriptions->getDescription($languageId);
    }
    
    
    /**
     * @inheritDoc
     */
    public function groupItems(): GroupItems
    {
        return $this->items;
    }
    
    
    /**
     * @inheritDoc
     */
    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
    
    
    /**
     * @inheritDoc
     */
    public function isProtected(): bool
    {
        return $this->isProtected;
    }
    
    
    /**
     * @param GroupNames        $names
     * @param GroupDescriptions $descriptions
     */
    public function updateNamesAndDescriptions(
        GroupNames $names,
        GroupDescriptions $descriptions
    ): void {
        $this->names        = $names;
        $this->descriptions = $descriptions;
        
        $this->raiseEvent(UpdatedNamesAndDescriptions::create($this->id, $this->names, $this->descriptions));
    }
    
    
    /**
     * @param int $sortOrder
     */
    public function updateSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
        
        $this->raiseEvent(UpdatedSortOrder::create($this->id, $this->sortOrder));
    }
    
    
    /**
     * @inheritDoc
     */
    public function addItem(GroupItem $groupItem): void
    {
        $this->items = $this->items->withItem($groupItem);
        
        $this->raiseEvent(AddedItemToGroup::create($this->id, $groupItem));
    }
    
    
    /**
     * @inheritDoc
     */
    public function removeItem(GroupItem $groupItem): void
    {
        $this->items = $this->items->withoutItem($groupItem);
        
        $this->raiseEvent(RemovedItemFromGroup::create($this->id, $groupItem));
    }
}