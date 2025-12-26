<?php
/* --------------------------------------------------------------
   AbstractEventDispatchingRepository.php 2020-07-22
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Event\Abstracts;

use Gambio\Core\Event\EventDispatchingRepository;
use Gambio\Core\Event\EventRaisingEntity;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class AbstractEventDispatchingRepository
 *
 * @package Gambio\Core\Event\Abstracts
 */
class AbstractEventDispatchingRepository implements EventDispatchingRepository
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    
    
    /**
     * @inheritDoc
     */
    public function dispatchEntityEvents(EventRaisingEntity $entity): void
    {
        foreach ($entity->releaseEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
    
    
    /**
     * @inheritDoc
     */
    public function dispatchEvent(object $event): void
    {
        $this->eventDispatcher->dispatch($event);
    }
    
    
    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    protected function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}