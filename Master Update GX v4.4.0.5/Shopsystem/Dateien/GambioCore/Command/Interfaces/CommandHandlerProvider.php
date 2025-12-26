<?php
/* --------------------------------------------------------------
 CommandHandlerProvider.php 2020-04-28
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Command\Interfaces;

/**
 * Interface CommandHandlerProvider
 * @package Gambio\Core\Command\Interfaces
 * @codeCoverageIgnore
 */
interface CommandHandlerProvider
{
    /**
     * Attaches a new command handler.
     *
     * @param string $commandClass
     * @param string $handler Full qualified class name of the listener, which must be available by the DI container.
     */
    public function attachHandler(string $commandClass, string $handler): void;
    
    
    /**
     * Returns a handler for the given command.
     *
     * @param object $command
     *
     * @return iterable
     */
    public function getHandlerForCommand(object $command): iterable;
}