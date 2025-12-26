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

namespace Gambio\Core\AdminAccess\Group;

use IteratorAggregate;

/**
 * Interface GroupItems
 *
 * @package Gambio\Core\AdminAccess\Group
 */
interface GroupItems extends IteratorAggregate
{
    /**
     * @return GroupItem[]
     */
    public function getIterator(): iterable;
    
    
    /**
     * @param GroupItem $groupItem
     *
     * @return GroupItems
     */
    public function withItem(GroupItem $groupItem): GroupItems;
    
    
    /**
     * @param GroupItem $groupItem
     *
     * @return GroupItems
     */
    public function withoutItem(GroupItem $groupItem): GroupItems;
}