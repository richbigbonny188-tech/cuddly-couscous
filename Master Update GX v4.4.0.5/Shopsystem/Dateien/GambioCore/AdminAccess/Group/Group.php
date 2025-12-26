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

namespace Gambio\Core\AdminAccess\Group;

use Gambio\Core\AdminAccess\Group\Exceptions\ParentGroupIdDoesNotExist;
use Gambio\Core\Event\EventRaisingEntity;

/**
 * Interface Group
 *
 * @package Gambio\Core\AdminAccess\Group
 */
interface Group extends EventRaisingEntity
{
    /**
     * @return int
     */
    public function id(): int;
    
    
    /**
     * @return int
     *
     * @throws ParentGroupIdDoesNotExist If there is no parent group defined.
     */
    public function parentGroupId(): int;
    
    
    /**
     * @param int $languageId
     *
     * @return string
     */
    public function name(int $languageId): string;
    
    
    /**
     * @param int $languageId
     *
     * @return string
     */
    public function description(int $languageId): string;
    
    
    /**
     * @return GroupItems
     */
    public function groupItems(): GroupItems;
    
    
    /**
     * @return int
     */
    public function sortOrder(): int;
    
    
    /**
     * @return bool
     */
    public function isProtected(): bool;
    
    
    /**
     * @param GroupItem $groupItem
     */
    public function addItem(GroupItem $groupItem): void;
    
    
    /**
     * @param GroupItem $groupItem
     */
    public function removeItem(GroupItem $groupItem): void;
    
    
    /**
     * @param GroupNames        $names
     * @param GroupDescriptions $descriptions
     */
    public function updateNamesAndDescriptions(
        GroupNames $names,
        GroupDescriptions $descriptions
    ): void;
    
    
    /**
     * @param int $sortOrder
     */
    public function updateSortOrder(int $sortOrder): void;
}