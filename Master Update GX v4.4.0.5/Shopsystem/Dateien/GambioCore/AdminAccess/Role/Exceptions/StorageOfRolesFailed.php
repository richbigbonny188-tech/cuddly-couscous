<?php
/* --------------------------------------------------------------
   StorageOfRolesFailed.php 2020-05-29
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
 * Class StorageOfRolesFailed
 *
 * @package Gambio\Core\AdminAccess\Role\Exceptions
 * @codeCoverageIgnore
 */
class StorageOfRolesFailed extends Exception
{
    /**
     * @param Exception $exception
     *
     * @return StorageOfRolesFailed
     */
    public static function becauseOfException(Exception $exception): StorageOfRolesFailed
    {
        return new self('Could not store access roles because of previous error.', 0, $exception);
    }
}