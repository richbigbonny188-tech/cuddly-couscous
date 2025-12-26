<?php
/* --------------------------------------------------------------
   UpdatedNamesAndDescriptions.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Group\Events;

use Gambio\Core\AdminAccess\Group\GroupDescriptions;
use Gambio\Core\AdminAccess\Group\GroupId;
use Gambio\Core\AdminAccess\Group\GroupNames;

/**
 * Class UpdatedNamesAndDescriptions
 *
 * @package Gambio\Core\AdminAccess\Group\Events
 */
class UpdatedNamesAndDescriptions
{
    /**
     * @var GroupId
     */
    private $groupId;
    
    /**
     * @var GroupNames
     */
    private $names;
    
    /**
     * @var GroupDescriptions
     */
    private $descriptions;
    
    
    /**
     * UpdatedNamesAndDescriptions constructor.
     *
     * @param GroupId           $groupId
     * @param GroupNames        $names
     * @param GroupDescriptions $descriptions
     */
    private function __construct(GroupId $groupId, GroupNames $names, GroupDescriptions $descriptions)
    {
        $this->groupId      = $groupId;
        $this->names        = $names;
        $this->descriptions = $descriptions;
    }
    
    
    /**
     * @param GroupId           $groupId
     * @param GroupNames        $names
     * @param GroupDescriptions $descriptions
     *
     * @return UpdatedNamesAndDescriptions
     */
    public static function create(
        GroupId $groupId,
        GroupNames $names,
        GroupDescriptions $descriptions
    ): UpdatedNamesAndDescriptions {
        return new self($groupId, $names, $descriptions);
    }
    
    
    /**
     * @return GroupId
     */
    public function groupId(): GroupId
    {
        return $this->groupId;
    }
    
    
    /**
     * @return GroupNames
     */
    public function names(): GroupNames
    {
        return $this->names;
    }
    
    
    /**
     * @return GroupDescriptions
     */
    public function descriptions(): GroupDescriptions
    {
        return $this->descriptions;
    }
}