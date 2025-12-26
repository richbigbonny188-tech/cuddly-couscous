<?php
/* --------------------------------------------------------------
   Aminds.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Admin\Model;

use ArrayIterator;
use Gambio\Core\AdminAccess\Admin\Admin;

/**
 * Class Admins
 *
 * @package Gambio\Core\AdminAccess\Admin\Models
 */
class Admins implements \Gambio\Core\AdminAccess\Admin\Admins
{
    /**
     * @var Admin[]
     */
    private $admins;
    
    
    /**
     * Admin constructor.
     *
     * @param Admin ...$admins
     */
    private function __construct(Admin ...$admins)
    {
        $this->admins = $admins;
    }
    
    
    /**
     * @param Admin ...$admins
     *
     * @return Admins
     */
    public static function create(Admin ...$admins): Admins
    {
        return new self(...$admins);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getIterator(): iterable
    {
        return new ArrayIterator($this->admins);
    }
}