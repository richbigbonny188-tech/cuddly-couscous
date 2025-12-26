<?php
/* --------------------------------------------------------------
 CommandDispatcherServiceProvider.php 2020-04-21
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Command;

use Gambio\Core\Application\DependencyInjection\AbstractBootableServiceProvider;

/**
 * Class CommandDispatcherServiceProvider
 * @package Gambio\Core\Command
 */
class CommandDispatcherServiceProvider extends AbstractBootableServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            Interfaces\CommandDispatcher::class,
            Interfaces\CommandHandlerProvider::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->registerShared(Interfaces\CommandDispatcher::class, CommandDispatcher::class);
        $this->application->registerShared(Interfaces\CommandHandlerProvider::class, CommandHandlerProvider::class)
                          ->addArgument(
                              $this->application
                          );
    }
    
    
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        $this->application->inflect(Interfaces\CommandDispatcher::class)->invokeMethod(
            'registerProvider',
            [
                Interfaces\CommandHandlerProvider::class
            ]
        );
    }
}