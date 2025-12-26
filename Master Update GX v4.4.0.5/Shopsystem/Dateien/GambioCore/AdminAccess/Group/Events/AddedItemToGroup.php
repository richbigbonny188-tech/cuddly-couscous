<?php
/* --------------------------------------------------------------
   AddedItemToGroup.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Group\Events;

use Gambio\Core\AdminAccess\Group\GroupId;
use Gambio\Core\AdminAccess\Group\GroupItem;

/**
 * Class AddedItemToGroup
 *
 * @package Gambio\Core\AdminAccess\Group\Events
 */
class AddedItemToGroup
{
    /**
     * @var GroupId
     */
    private $groupId;
    
    /**
     * @var GroupItem
     */
    private $groupItem;
    
    
    /**
     * AddedItemToGroup constructor.
     *
     * @param GroupId   $groupId
     * @param GroupItem $groupItem
     */
    private function __construct(GroupId $groupId, GroupItem $groupItem)
    {
        $this->groupId   = $groupId;
        $this->groupItem = $groupItem;
    }
    
    
    /**
     * @param GroupId   $groupId
     * @param GroupItem $groupItem
     *
     * @return AddedItemToGroup
     */
    public static function create(GroupId $groupId, GroupItem $groupItem): AddedItemToGroup
    {
        return new self($groupId, $groupItem);
    }
    
    
    /**
     * @return GroupId
     */
    public function groupId(): GroupId
    {
        return $this->groupId;
    }
    
    
    /**
     * @return GroupItem
     */
    public function groupItem(): GroupItem
    {
        return $this->groupItem;
    }
}