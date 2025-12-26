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

namespace Gambio\Core\AdminAccess\Role\Events;

use Gambio\Core\AdminAccess\Role\RoleDescriptions;
use Gambio\Core\AdminAccess\Role\RoleId;
use Gambio\Core\AdminAccess\Role\RoleNames;

/**
 * Class UpdatedNamesAndDescriptions
 *
 * @package Gambio\Core\AdminAccess\Role\Events
 */
class UpdatedNamesAndDescriptions
{
    /**
     * @var RoleId
     */
    private $roleId;
    
    /**
     * @var RoleNames
     */
    private $names;
    
    /**
     * @var RoleDescriptions
     */
    private $descriptions;
    
    
    /**
     * UpdatedNamesAndDescriptions constructor.
     *
     * @param RoleId           $roleId
     * @param RoleNames        $names
     * @param RoleDescriptions $descriptions
     */
    private function __construct(
        RoleId $roleId,
        RoleNames $names,
        RoleDescriptions $descriptions
    ) {
        $this->roleId       = $roleId;
        $this->names        = $names;
        $this->descriptions = $descriptions;
    }
    
    
    /**
     * @param RoleId           $roleId
     * @param RoleNames        $names
     * @param RoleDescriptions $descriptions
     *
     * @return UpdatedNamesAndDescriptions
     */
    public static function create(
        RoleId $roleId,
        RoleNames $names,
        RoleDescriptions $descriptions
    ): UpdatedNamesAndDescriptions {
        return new self($roleId, $names, $descriptions);
    }
    
    
    /**
     * @return RoleId
     */
    public function roleId(): RoleId
    {
        return $this->roleId;
    }
    
    
    /**
     * @return RoleNames
     */
    public function names(): RoleNames
    {
        return $this->names;
    }
    
    
    /**
     * @return RoleDescriptions
     */
    public function descriptions(): RoleDescriptions
    {
        return $this->descriptions;
    }
}