<?php
/* --------------------------------------------------------------
 EventDispatcherServiceProvider.php 2020-04-21
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Event;

use Gambio\Core\Application\DependencyInjection\AbstractBootableServiceProvider;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class EventDispatcherServiceProvider
 *
 * @package Gambio\Core\Event
 */
class EventDispatcherServiceProvider extends AbstractBootableServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            EventListenerProvider::class,
            EventDispatcherInterface::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->registerShared(EventDispatcherInterface::class, EventDispatcher::class);
        $this->application->registerShared(EventListenerProvider::class)->addArgument($this->application);
    }
    
    
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        $this->application->inflect(EventDispatcherInterface::class)->invokeMethod(
            'registerProvider',
            [EventListenerProvider::class]
        );
    }
}