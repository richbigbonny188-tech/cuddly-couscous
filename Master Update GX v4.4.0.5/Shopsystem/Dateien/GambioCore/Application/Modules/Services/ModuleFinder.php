<?php
/* --------------------------------------------------------------
 ModuleFinder.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules\Services;

use Gambio\Core\Application\Modules\Model\Modules;

/**
 * Interface ModuleFinder
 * @package Gambio\Core\Contracts\ModuleFoo
 */
interface ModuleFinder
{
    /**
     * Returns a list of available modules.
     *
     * @return Modules
     */
    public function getModules(): Modules;
    
    
    /**
     * Returns a list of service provider names.
     *
     * @return string[]
     */
    public function getServiceProviderList(): array;
    
    
    /**
     * Returns a list of paths to autoloader.
     *
     * @return string[]
     */
    public function getAutoloaderPaths(): array;
}