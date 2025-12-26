<?php
/* --------------------------------------------------------------
   UserId.php 2020-10-05
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Auth;

/**
 * Interface UserId
 *
 * @package Gambio\Core\Auth
 */
interface UserId
{
    /**
     * @return int
     */
    public function userId(): int;
}