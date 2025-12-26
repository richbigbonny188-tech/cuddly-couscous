<?php
/* --------------------------------------------------------------
   EventDispatchingRepository.php 2020-07-22
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
 * Interface EventDispatchingRepository
 *
 * @package Gambio\Core\Event
 */
interface EventDispatchingRepository
{
    /**
     * @param EventRaisingEntity $entity
     */
    public function dispatchEntityEvents(EventRaisingEntity $entity): void;
    
    
    /**
     * @param object $event
     */
    public function dispatchEvent(object $event): void;
}