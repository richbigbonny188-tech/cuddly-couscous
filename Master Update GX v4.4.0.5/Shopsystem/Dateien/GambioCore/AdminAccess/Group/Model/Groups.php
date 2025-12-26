<?php
/* --------------------------------------------------------------
   Groups.php 2020-05-29
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
use Gambio\Core\AdminAccess\Group\Group;

/**
 * Class Groups
 *
 * @package Gambio\Core\AdminAccess\Group\Models
 */
class Groups implements \Gambio\Core\AdminAccess\Group\Groups
{
    /**
     * @var Group[]
     */
    private $groups;
    
    
    /**
     * Groups constructor.
     *
     * @param Group[] $groups
     */
    private function __construct(Group ...$groups)
    {
        $this->groups = $groups;
    }
    
    
    /**
     * @param Group[] $groups
     *
     * @return Groups
     */
    public static function create(Group ...$groups): Groups
    {
        return new self(...$groups);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getIterator(): iterable
    {
        return new ArrayIterator($this->groups);
    }
}