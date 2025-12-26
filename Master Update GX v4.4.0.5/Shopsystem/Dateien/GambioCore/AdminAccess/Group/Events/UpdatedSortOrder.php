<?php
/* --------------------------------------------------------------
   UpdatedSortOrder.php 2020-05-29
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

/**
 * Class UpdatedSortOrder
 *
 * @package Gambio\Core\AdminAccess\Group\Events
 */
class UpdatedSortOrder
{
    /**
     * @var GroupId
     */
    private $groupId;
    
    /**
     * @var int
     */
    private $sortOrder;
    
    
    /**
     * UpdatedGroupNamesAndDescriptions constructor.
     *
     * @param GroupId $groupId
     * @param int     $sortOrder
     */
    private function __construct(GroupId $groupId, int $sortOrder)
    {
        $this->groupId   = $groupId;
        $this->sortOrder = $sortOrder;
    }
    
    
    /**
     * @param GroupId $groupId
     * @param int     $sortOrder
     *
     * @return UpdatedSortOrder
     */
    public static function create(GroupId $groupId, int $sortOrder): UpdatedSortOrder
    {
        return new self($groupId, $sortOrder);
    }
    
    
    /**
     * @return GroupId
     */
    public function groupId(): GroupId
    {
        return $this->groupId;
    }
    
    
    /**
     * @return int
     */
    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
}