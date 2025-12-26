<?php
/* --------------------------------------------------------------
   GroupIds.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Group\Model;

use ArrayIterator;
use Gambio\Core\AdminAccess\Group\GroupId;

/**
 * Class GroupIds
 *
 * @package Gambio\Core\AdminAccess\Group\Models
 */
class GroupIds implements \Gambio\Core\AdminAccess\Group\GroupIds
{
    /**
     * @var GroupId[]
     */
    private $ids;
    
    
    /**
     * GroupIds constructor.
     *
     * @param GroupId ...$ids
     */
    private function __construct(GroupId ...$ids)
    {
        $this->ids = $ids;
    }
    
    
    /**
     * @param GroupId ...$ids
     *
     * @return GroupIds
     */
    public static function create(GroupId ...$ids): GroupIds
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