<?php
/* --------------------------------------------------------------
   StorageOfAdminsFailed.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Admin\Exceptions;

use Exception;

/**
 * Class StorageOfAdminsFailed
 *
 * @package Gambio\Core\AdminAccess\Admin\Exceptions
 * @codeCoverageIgnore
 */
class StorageOfAdminsFailed extends Exception
{
    /**
     * @param Exception $exception
     *
     * @return StorageOfAdminsFailed
     */
    public static function becauseOfException(Exception $exception): StorageOfAdminsFailed
    {
        return new self('Could not store admins because of previous error.', 0, $exception);
    }
}