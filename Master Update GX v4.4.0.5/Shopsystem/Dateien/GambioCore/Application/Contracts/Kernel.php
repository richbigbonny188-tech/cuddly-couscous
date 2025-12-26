<?php
/* --------------------------------------------------------------
 Kernel.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Contracts;

/**
 * Interface Kernel
 * @package Gambio\Core\Contracts\Application
 */
interface Kernel
{
    /**
     * Bootstraps the application.
     *
     * The bootstrapper is usually composed of other bootstrapper. They perform all tasks
     * to setup the application core stack.
     *
     * @param Application  $application
     * @param Bootstrapper $bootstrapper
     */
    public function bootstrap(Application $application, Bootstrapper $bootstrapper): void;
    
    
    /**
     * Runs the application.
     */
    public function run(): void;
}
