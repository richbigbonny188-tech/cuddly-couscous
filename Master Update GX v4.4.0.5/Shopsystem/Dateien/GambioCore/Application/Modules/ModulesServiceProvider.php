<?php
/* --------------------------------------------------------------
 ModulesServiceProvider.php 2020-06-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules;

use Gambio\Core\Application\Modules\GxModules\GxModulesCache;
use Gambio\Core\Application\Modules\GxModules\GxModulesPaths;
use Gambio\Core\Application\Modules\GxModules\InstalledGxModulesPaths;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;
use Gambio\Core\Application\ValueObjects\Path;
use Gambio\Core\Cache\CacheFactory;
use Gambio\Core\Configuration\ConfigurationFinder;

/**
 * Class ModulesServiceProvider
 * @package Gambio\Core\Application\Modules
 */
class ModulesServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            // gx modules
            GxModulesCache::class,
            GxModulesPaths::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->registerShared(GxModulesCache::class)->addArguments(
            [
                CacheFactory::class,
                InstalledGxModulesPaths::class,
                GxModulesPaths::class,
            ]
        );
        $this->application->registerShared(GxModulesPaths::class)->addArgument(Path::class);
        $this->application->registerShared(InstalledGxModulesPaths::class)->addArguments(
            [
                ConfigurationFinder::class,
                GxModulesPaths::class
            ]
        );
    }
}