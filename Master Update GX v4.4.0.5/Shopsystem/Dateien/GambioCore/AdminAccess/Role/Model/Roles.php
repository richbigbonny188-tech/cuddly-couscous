<?php
/* --------------------------------------------------------------
   Roles.php 2020-05-29
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
use Gambio\Core\AdminAccess\Role\Role;

/**
 * Class Roles
 *
 * @package Gambio\Core\AdminAccess\Role\Models
 */
class Roles implements \Gambio\Core\AdminAccess\Role\Roles
{
    /**
     * @var Role[]
     */
    private $roles;
    
    
    /**
     * Role constructor.
     *
     * @param Role ...$roles
     */
    private function __construct(Role ...$roles)
    {
        $this->roles = $roles;
    }
    
    
    /**
     * @param Role ...$roles
     *
     * @return Roles
     */
    public static function create(Role ...$roles): Roles
    {
        return new self(...$roles);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getIterator(): iterable
    {
        return new ArrayIterator($this->roles);
    }
}