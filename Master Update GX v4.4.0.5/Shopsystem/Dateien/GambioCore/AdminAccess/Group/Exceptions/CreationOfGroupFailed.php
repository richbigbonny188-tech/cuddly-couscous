<?php
/* --------------------------------------------------------------
   CreationOfGroupFailed.php 2020-05-29
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
 * Class CreationOfGroupFailed
 *
 * @package Gambio\Core\AdminAccess\Group\Exceptions
 * @codeCoverageIgnore
 */
class CreationOfGroupFailed extends Exception
{
    /**
     * @param Exception $exception
     *
     * @return CreationOfGroupFailed
     */
    public static function becauseOfException(Exception $exception): CreationOfGroupFailed
    {
        return new self('Could not create access groups because of previous error.', 0, $exception);
    }
}