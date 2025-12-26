<?php
/* --------------------------------------------------------------
   DeletionOfGroupsFailed.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Group\Exceptions;

use Exception;

/**
 * Class DeletionOfGroupsFailed
 *
 * @package Gambio\Core\AdminAccess\Group\Exceptions
 * @codeCoverageIgnore
 */
class DeletionOfGroupsFailed extends Exception
{
    /**
     * @param Exception $exception
     *
     * @return DeletionOfGroupsFailed
     */
    public static function becauseOfException(Exception $exception): DeletionOfGroupsFailed
    {
        return new self('Could not delete access groups because of previous error.', 0, $exception);
    }
}