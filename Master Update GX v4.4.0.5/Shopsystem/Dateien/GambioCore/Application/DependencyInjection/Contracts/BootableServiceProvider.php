<?php
/* --------------------------------------------------------------
 BootableServiceProvider.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\DependencyInjection\Contracts;

/**
 * Interface BootableServiceProvider
 * @package Gambio\Core\Contracts\Application\DependencyInjection
 */
interface BootableServiceProvider extends ServiceProvider
{
    /**
     * Performs bootstrapping tasks right after the service provider
     * is registered to the application.
     */
    public function boot(): void;
}