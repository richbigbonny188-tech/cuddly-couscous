<?php
/* --------------------------------------------------------------
   AdminFactory.php 2020-08-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Admin\Model;

use Gambio\Core\AdminAccess\Admin\Admin;
use Gambio\Core\AdminAccess\Admin\AdminId;
use Gambio\Core\AdminAccess\Admin\AdminIds;
use Gambio\Core\AdminAccess\Admin\Admins;
use Gambio\Core\AdminAccess\Admin\RoleId;
use Gambio\Core\AdminAccess\Admin\RoleIds;

/**
 * Class AdminFactory
 *
 * @package Gambio\Core\AdminAccess\Admin\Models
 */
class AdminFactory implements \Gambio\Core\AdminAccess\Admin\AdminFactory
{
    /**
     * @inheritDoc
     */
    public function createAdmin(int $id, string $firstName, string $lastName, array $assignedRoleIds): Admin
    {
        $roleIds = $this->createRoleIds(...array_map([$this, 'createRoleId'], $assignedRoleIds));
        
        return \Gambio\Core\AdminAccess\Admin\Model\Admin::create($this->createAdminId($id),
                                                                  $firstName,
                                                                  $lastName,
                                                                  $roleIds);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createAdmins(Admin ...$admins): Admins
    {
        return \Gambio\Core\AdminAccess\Admin\Model\Admins::create(...$admins);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createAdminId(int $id): AdminId
    {
        return \Gambio\Core\AdminAccess\Admin\Model\AdminId::create($id);
    }
    
    
    /**
     * @inheritDoc
     */
    public function createAdminIds(AdminId ...$ids): AdminIds
    {
        return \Gambio\Core\AdminAccess\Admin\Model\AdminIds::create(...$ids);
    }
    
    
    /**
     * @param int $id
     *
     * @return RoleId
     */
    public function createRoleId(int $id): RoleId
    {
        return \Gambio\Core\AdminAccess\Admin\Model\RoleId::create($id);
    }
    
    
    /**
     * @param RoleId[] $ids
     *
     * @return RoleIds
     */
    public function createRoleIds(RoleId ...$ids): RoleIds
    {
        return \Gambio\Core\AdminAccess\Admin\Model\RoleIds::create(...$ids);
    }
}