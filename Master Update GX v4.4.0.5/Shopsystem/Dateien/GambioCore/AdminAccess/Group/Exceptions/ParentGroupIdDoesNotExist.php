<?php
/* --------------------------------------------------------------
   ParentGroupIdDoesNotExist.php 2020-05-29
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
 * Class ParentGroupIdDoesNotExist
 *
 * @package Gambio\Core\AdminAccess\Group\Exceptions
 * @codeCoverageIgnore
 */
class ParentGroupIdDoesNotExist extends Exception
{
    /**
     * @param int $id
     *
     * @return ParentGroupIdDoesNotExist
     */
    public static function forGroup(int $id): ParentGroupIdDoesNotExist
    {
        return new self('There is no parent group for the group with ID ' . $id . ' defined.');
    }
}