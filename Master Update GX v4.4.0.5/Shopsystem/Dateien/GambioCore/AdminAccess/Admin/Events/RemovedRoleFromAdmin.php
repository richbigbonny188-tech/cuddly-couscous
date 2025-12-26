<?php
/* --------------------------------------------------------------
   RemovedRoleFromAdmin.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Admin\Events;

use Gambio\Core\AdminAccess\Admin\AdminId;
use Gambio\Core\AdminAccess\Admin\RoleId;

/**
 * Class RemovedRoleFromAdmin
 *
 * @package Gambio\Core\AdminAccess\Admin\Events
 */
class RemovedRoleFromAdmin
{
    /**
     * @var AdminId
     */
    private $adminId;
    
    /**
     * @var RoleId
     */
    private $roleId;
    
    
    /**
     * RemovedRoleFromAdmin constructor.
     *
     * @param AdminId $adminId
     * @param RoleId  $roleId
     */
    private function __construct(AdminId $adminId, RoleId $roleId)
    {
        $this->adminId = $adminId;
        $this->roleId  = $roleId;
    }
    
    
    /**
     * @param AdminId $adminId
     * @param RoleId  $roleId
     *
     * @return RemovedRoleFromAdmin
     */
    public static function create(AdminId $adminId, RoleId $roleId): RemovedRoleFromAdmin
    {
        return new self($adminId, $roleId);
    }
    
    
    /**
     * @return AdminId
     */
    public function adminId(): AdminId
    {
        return $this->adminId;
    }
    
    
    /**
     * @return RoleId
     */
    public function roleId(): RoleId
    {
        return $this->roleId;
    }
}