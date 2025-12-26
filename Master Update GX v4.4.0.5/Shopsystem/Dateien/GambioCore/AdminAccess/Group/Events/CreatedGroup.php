<?php
/* --------------------------------------------------------------
   CreatedGroup.php 2020-05-29
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
 * Class CreatedGroup
 *
 * @package Gambio\Core\AdminAccess\Group\Events
 */
class CreatedGroup
{
    /**
     * @var GroupId
     */
    private $groupId;
    
    
    /**
     * CreatedGroup constructor.
     *
     * @param GroupId $groupId
     */
    private function __construct(GroupId $groupId)
    {
        $this->groupId = $groupId;
    }
    
    
    /**
     * @param GroupId $groupId
     *
     * @return CreatedGroup
     */
    public static function create(GroupId $groupId): CreatedGroup
    {
        return new self($groupId);
    }
    
    
    /**
     * @return GroupId
     */
    public function groupId(): GroupId
    {
        return $this->groupId;
    }
}