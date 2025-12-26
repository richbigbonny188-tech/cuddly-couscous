<?php
/* --------------------------------------------------------------
   CreatedRole.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role\Events;

use Gambio\Core\AdminAccess\Role\RoleId;

/**
 * Class CreatedRole
 *
 * @package Gambio\Core\AdminAccess\Role\Events
 */
class CreatedRole
{
    /**
     * @var RoleId
     */
    private $roleId;
    
    
    /**
     * CreatedRole constructor.
     *
     * @param RoleId $roleId
     */
    private function __construct(RoleId $roleId)
    {
        $this->roleId = $roleId;
    }
    
    
    /**
     * @param RoleId $roleId
     *
     * @return CreatedRole
     */
    public static function create(RoleId $roleId): CreatedRole
    {
        return new self($roleId);
    }
    
    
    /**
     * @return RoleId
     */
    public function roleId(): RoleId
    {
        return $this->roleId;
    }
}