<?php
/* --------------------------------------------------------------
   Admin.php 2020-07-22
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Admin;

use Gambio\Core\Event\EventRaisingEntity;

/**
 * Interface Admin
 *
 * @package Gambio\Core\AdminAccess\Admin
 */
interface Admin extends EventRaisingEntity
{
    /**
     * @return int
     */
    public function id(): int;
    
    
    /**
     * @return string
     */
    public function firstName(): string;
    
    
    /**
     * @return string
     */
    public function lastName(): string;
    
    
    /**
     * @return RoleIds
     */
    public function assignedRoleIds(): RoleIds;
    
    
    /**
     * @param RoleId $roleId
     */
    public function assignRole(RoleId $roleId): void;
    
    
    /**
     * @param RoleId $roleId
     */
    public function removeRole(RoleId $roleId): void;
}