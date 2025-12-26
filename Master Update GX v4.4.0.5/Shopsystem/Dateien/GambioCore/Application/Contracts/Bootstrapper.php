<?php
/* --------------------------------------------------------------
 Bootstrapper.php 2020-09-11
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
 * Interface Bootstrapper
 * @package Gambio\Core\Contracts\Application
 */
interface Bootstrapper
{
    /**
     * Application bootstrapping.
     *
     * The kernel expects exactly one bootstrapper. Usually, this bootstrapper
     * is composed of several other bootstrapper.
     *
     * Bootstrapper prepare parts of the application, for example they are responsible to register
     * service providers, but they also load environment information and make them available in the application.
     *
     * @param Application $application
     */
    public function boot(Application $application): void;
}