<?php
/* --------------------------------------------------------------
   EventRaisingEntity.php 2020-07-22
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Event;

/**
 * Interface EventRaisingEntity
 *
 * @package Gambio\Core\Event
 */
interface EventRaisingEntity
{
    /**
     * @return object[]
     */
    public function releaseEvents(): array;
}