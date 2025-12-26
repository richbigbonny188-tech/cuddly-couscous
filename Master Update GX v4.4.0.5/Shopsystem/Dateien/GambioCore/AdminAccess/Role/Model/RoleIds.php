<?php
/* --------------------------------------------------------------
   RoleIds.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role\Model;

use ArrayIterator;
use Gambio\Core\AdminAccess\Role\RoleId;

/**
 * Class RoleIds
 *
 * @package Gambio\Core\AdminAccess\Role\Models
 */
class RoleIds implements \Gambio\Core\AdminAccess\Role\RoleIds
{
    /**
     * @var RoleId[]
     */
    private $ids;
    
    
    /**
     * RoleId constructor.
     *
     * @param RoleId ...$ids
     */
    private function __construct(RoleId ...$ids)
    {
        $this->ids = [];
        foreach ($ids as $id) {
            $this->ids[$id->value()] = $id;
        }
    }
    
    
    /**
     * @param RoleId ...$ids
     *
     * @return RoleIds
     */
    public static function create(RoleId ...$ids): RoleIds
    {
        return new self(...$ids);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getIterator(): iterable
    {
        return new ArrayIterator($this->ids);
    }
}