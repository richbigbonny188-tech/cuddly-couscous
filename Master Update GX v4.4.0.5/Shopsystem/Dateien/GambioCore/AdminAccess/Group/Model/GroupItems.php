<?php
/* --------------------------------------------------------------
   GroupItems.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Group\Model;

use ArrayIterator;
use Gambio\Core\AdminAccess\Group\GroupItem;

/**
 * Class GroupItems
 *
 * @package Gambio\Core\AdminAccess\Group\Models
 */
class GroupItems implements \Gambio\Core\AdminAccess\Group\GroupItems
{
    /**
     * @var GroupItem[]
     */
    private $groupItems;
    
    
    /**
     * GroupItems constructor.
     *
     * @param GroupItem[] $groupItems
     */
    private function __construct(GroupItem ...$groupItems)
    {
        $this->groupItems = [];
        foreach ($groupItems as $groupItem) {
            $hash                    = md5($groupItem->type() . '-' . $groupItem->descriptor());
            $this->groupItems[$hash] = $groupItem;
        }
    }
    
    
    /**
     * @param GroupItem[] $groupItems
     *
     * @return GroupItems
     */
    public static function create(GroupItem ...$groupItems): GroupItems
    {
        return new self(...$groupItems);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getIterator(): iterable
    {
        return new ArrayIterator($this->groupItems);
    }
    
    
    /**
     * @inheritDoc
     */
    public function withItem(GroupItem $groupItem): \Gambio\Core\AdminAccess\Group\GroupItems
    {
        $items        = $this->groupItems;
        $hash         = md5($groupItem->type() . '-' . $groupItem->descriptor());
        $items[$hash] = $groupItem;
        
        return new self(...array_values($items));
    }
    
    
    /**
     * @inheritDoc
     */
    public function withoutItem(GroupItem $groupItem): \Gambio\Core\AdminAccess\Group\GroupItems
    {
        $items = $this->groupItems;
        $hash  = md5($groupItem->type() . '-' . $groupItem->descriptor());
        unset($items[$hash]);
        
        return new self(...$items);
    }
}