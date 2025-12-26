<?php
/* --------------------------------------------------------------
   DeletionOfRolesFailed.php 2020-05-29
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
 * Class DeletionOfRolesFailed
 *
 * @package Gambio\Core\AdminAccess\Role\Exceptions
 * @codeCoverageIgnore
 */
class DeletionOfRolesFailed extends Exception
{
    /**
     * @param Exception $exception
     *
     * @return DeletionOfRolesFailed
     */
    public static function becauseOfException(Exception $exception): DeletionOfRolesFailed
    {
        return new self('Could not delete access roles because of previous error.', 0, $exception);
    }
}