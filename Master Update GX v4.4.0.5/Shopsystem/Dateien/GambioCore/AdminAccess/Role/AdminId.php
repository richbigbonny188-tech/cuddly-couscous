<?php
/* --------------------------------------------------------------
   AdminId.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role;

/**
 * Interface AdminId
 *
 * @package Gambio\Core\AdminAccess\Role
 */
interface AdminId
{
    /**
     * @return int
     */
    public function value(): int;
}