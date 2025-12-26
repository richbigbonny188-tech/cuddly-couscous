<?php
/* --------------------------------------------------------------
 Application.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Contracts;

use Gambio\Core\Application\DependencyInjection\Contracts\Registry;
use Psr\Container\ContainerInterface;

/**
 * Interface Application
 * @package Gambio\Core\Contracts\Application
 */
interface Application extends ContainerInterface, Registry
{
    /**
     * Main function.
     *
     * Similar the the main functions of programming languages, this method is intended
     * to call once centrally. It is responsible to perform all tasks of the application.
     *
     * @param Kernel       $kernel
     * @param Bootstrapper $bootstrapper
     */
    public static function main(Kernel $kernel, Bootstrapper $bootstrapper): void;
    
    
    /**
     * Registers a new service provider to the application.
     *
     * @param string $serviceProvider
     */
    public function registerProvider(string $serviceProvider): void;
    
    
    /**
     * Attaches an event listener to an event class.
     *
     * @param string $eventClass
     * @param string $listener
     */
    public function attachEventListener(string $eventClass, string $listener): void;
    
    
    /**
     * Attaches a command handler to a command class.
     *
     * @param string $commandClass
     * @param string $handler
     */
    public function attachCommandHandler(string $commandClass, string $handler): void;
}
