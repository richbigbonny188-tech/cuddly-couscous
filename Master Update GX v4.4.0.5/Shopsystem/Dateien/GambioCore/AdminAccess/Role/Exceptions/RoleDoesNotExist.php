<?php
/* --------------------------------------------------------------
   RoleDoesNotExist.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Role\Exceptions;

use Exception;

/**
 * Class RoleDoesNotExist
 *
 * @package Gambio\Core\AdminAccess\Role\Exceptions
 * @codeCoverageIgnore
 */
class RoleDoesNotExist extends Exception
{
    /**
     * @param int $id
     *
     * @return RoleDoesNotExist
     */
    public static function forId(int $id): RoleDoesNotExist
    {
        return new self('Access role with ID ' . $id . ' does not exist.');
    }
}