<?php
/* --------------------------------------------------------------
   Admins.php 2020-05-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess\Admin;

use IteratorAggregate;

/**
 * Interface Admins
 *
 * @package Gambio\Core\AdminAccess\Admin
 */
interface Admins extends IteratorAggregate
{
    /**
     * @return Admin[]
     */
    public function getIterator(): iterable;
}