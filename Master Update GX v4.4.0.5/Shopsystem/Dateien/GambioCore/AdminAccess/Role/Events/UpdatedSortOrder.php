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

namespace Gambio\Core\AdminAccess\Role\Events;

use Gambio\Core\AdminAccess\Role\RoleId;

/**
 * Class UpdatedSortOrder
 *
 * @package Gambio\Core\AdminAccess\Role\Events
 */
class UpdatedSortOrder
{
    /**
     * @var RoleId
     */
    private $roleId;
    
    /**
     * @var int
     */
    private $sortOrder;
    
    
    /**
     * UpdatedSortOrder constructor.
     *
     * @param RoleId $roleId
     * @param int    $sortOrder
     */
    private function __construct(RoleId $roleId, int $sortOrder)
    {
        $this->roleId    = $roleId;
        $this->sortOrder = $sortOrder;
    }
    
    
    /**
     * @param RoleId $roleId
     * @param int    $sortOrder
     *
     * @return UpdatedSortOrder
     */
    public static function create(RoleId $roleId, int $sortOrder): UpdatedSortOrder
    {
        return new self($roleId, $sortOrder);
    }
    
    
    /**
     * @return RoleId
     */
    public function roleId(): RoleId
    {
        return $this->roleId;
    }
    
    
    /**
     * @return int
     */
    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
}