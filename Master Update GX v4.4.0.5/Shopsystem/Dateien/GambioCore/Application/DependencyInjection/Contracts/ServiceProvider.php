<?php
/* --------------------------------------------------------------
 ServiceProvider.php 2020-09-11
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
 * Interface ServiceProvider
 * @package Gambio\Core\Contracts\Application\DependencyInjection
 */
interface ServiceProvider
{
    /**
     * List of types that the service provider provides.
     *
     * @return array
     */
    public function provides(): array;
    
    
    /**
     * Registers all of the type definitions and how to construct them.
     */
    public function register(): void;
}