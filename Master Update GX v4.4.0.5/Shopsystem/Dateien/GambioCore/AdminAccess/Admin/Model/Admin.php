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

namespace Gambio\Core\AdminAccess\Admin\Model;

use Gambio\Core\AdminAccess\Admin\AdminId;
use Gambio\Core\AdminAccess\Admin\Events\AssignedRoleToAdmin;
use Gambio\Core\AdminAccess\Admin\Events\RemovedRoleFromAdmin;
use Gambio\Core\AdminAccess\Admin\RoleId;
use Gambio\Core\AdminAccess\Admin\RoleIds;
use Gambio\Core\Event\Abstracts\AbstractEventRaisingEntity;

/**
 * Class Admin
 *
 * @package Gambio\Core\AdminAccess\Admin\Models
 */
class Admin extends AbstractEventRaisingEntity implements \Gambio\Core\AdminAccess\Admin\Admin
{
    /**
     * @var AdminId
     */
    private $id;
    
    /**
     * @var string
     */
    private $firstName;
    
    /**
     * @var string
     */
    private $lastName;
    
    /**
     * @var RoleIds
     */
    private $assignedRoleIds;
    
    
    /**
     * Admin constructor.
     *
     * @param AdminId $id
     * @param string  $firstName
     * @param string  $lastName
     * @param RoleIds $assignedRoleIds
     */
    private function __construct(AdminId $id, string $firstName, string $lastName, RoleIds $assignedRoleIds)
    {
        $this->id              = $id;
        $this->firstName       = $firstName;
        $this->lastName        = $lastName;
        $this->assignedRoleIds = $assignedRoleIds;
    }
    
    
    /**
     * @param AdminId $id
     * @param string  $firstName
     * @param string  $lastName
     * @param RoleIds $assignedRoleIds
     *
     * @return Admin
     */
    public static function create(
        AdminId $id,
        string $firstName,
        string $lastName,
        RoleIds $assignedRoleIds
    ): Admin {
        return new self($id, $firstName, $lastName, $assignedRoleIds);
    }
    
    
    /**
     * @inheritDoc
     */
    public function id(): int
    {
        return $this->id->value();
    }
    
    
    /**
     * @inheritDoc
     */
    public function firstName(): string
    {
        return $this->firstName;
    }
    
    
    /**
     * @inheritDoc
     */
    public function lastName(): string
    {
        return $this->lastName;
    }
    
    
    /**
     * @inheritDoc
     */
    public function assignedRoleIds(): RoleIds
    {
        return $this->assignedRoleIds;
    }
    
    
    /**
     * @inheritDoc
     */
    public function assignRole(RoleId $roleId): void
    {
        $this->assignedRoleIds = $this->assignedRoleIds->withRoleId($roleId);
        
        $this->raiseEvent(AssignedRoleToAdmin::create($this->id, $roleId));
    }
    
    
    /**
     * @inheritDoc
     */
    public function removeRole(RoleId $roleId): void
    {
        $this->assignedRoleIds = $this->assignedRoleIds->withoutRoleId($roleId);
        
        $this->raiseEvent(RemovedRoleFromAdmin::create($this->id, $roleId));
    }
}