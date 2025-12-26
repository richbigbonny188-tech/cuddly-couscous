<?php
/* --------------------------------------------------------------
 CommandDispatcher.php 2020-04-06
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
 * Interface CommandDispatcher
 * @package Gambio\Core\Command\Interfaces
 */
interface CommandDispatcher
{
    /**
     * Registers a new command handler provider.e
     *
     * @param CommandHandlerProvider $provider
     */
    public function registerProvider(CommandHandlerProvider $provider): void;
    
    
    /**
     * Dispatches the command.
     *
     * @param object $command
     *
     * @return object
     */
    public function dispatch(object $command): object;
}