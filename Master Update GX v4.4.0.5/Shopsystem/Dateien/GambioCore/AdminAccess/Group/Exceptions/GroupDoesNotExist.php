<?php
/* --------------------------------------------------------------
   GroupDoesNotExist.php 2020-05-29
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
 * Class GroupDoesNotExist
 *
 * @package Gambio\Core\AdminAccess\Group\Exceptions
 * @codeCoverageIgnore
 */
class GroupDoesNotExist extends Exception
{
    /**
     * @param int $id
     *
     * @return GroupDoesNotExist
     */
    public static function forId(int $id): GroupDoesNotExist
    {
        return new self('Access group with ID ' . $id . ' does not exist.');
    }
    
    
    /**
     * @param string $descriptor
     * @param string $type
     *
     * @return GroupDoesNotExist
     */
    public static function forDescriptorAndType(string $descriptor, string $type): GroupDoesNotExist
    {
        return new self('Access group with group item "' . $descriptor . '" of type "' . $type . '" does not exist.');
    }
}