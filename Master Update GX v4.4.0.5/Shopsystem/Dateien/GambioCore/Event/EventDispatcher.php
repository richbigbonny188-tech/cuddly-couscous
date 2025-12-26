<?php
/* --------------------------------------------------------------
   EventDispatcher.php 2020-01-30
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Throwable;
use function Gambio\Core\Logging\logger;

/**
 * Class EventDispatcher
 *
 * @package Gambio\Core\Event
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var ListenerProviderInterface[]
     */
    private $providers = [];
    
    
    /**
     * @param ListenerProviderInterface $provider
     *
     * @return $this
     */
    public function registerProvider(ListenerProviderInterface $provider): self
    {
        $this->providers[] = $provider;
        $this->providers   = array_unique($this->providers, SORT_REGULAR);
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     *
     * @throws Throwable
     */
    public function dispatch(object $event)
    {
        try {
            foreach ($this->providers as $provider) {
                foreach ($provider->getListenersForEvent($event) as $listener) {
                    if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                        return $event;
                    }
                    $listener($event);
                }
            }
        } catch (Throwable $error) {
            logger('events')->error(
                'Error while dispatching event occurred.',
                ['event' => get_class($event), 'error' => $error]
            );
            throw $error;
        }
        
        return $event;
    }
}