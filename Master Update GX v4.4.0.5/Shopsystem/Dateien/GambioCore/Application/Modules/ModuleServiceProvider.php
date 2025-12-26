<?php
/* --------------------------------------------------------------
 ModuleServiceProvider.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules;

use Gambio\Core\Application\DependencyInjection\AbstractBootableServiceProvider;
use Gambio\Core\Application\ValueObjects\Url;
use Gambio\Core\Command\Interfaces\CommandDispatcher;

/**
 * Class ModuleServiceProvider
 * @package Gambio\Core\Framework\Module
 */
class ModuleServiceProvider extends AbstractBootableServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            Services\ModuleFinder::class
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        $this->application->inflect(ModuleAction::class)->invokeMethod('initModuleAction', [Url::class]);
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->registerShared(Services\ModuleFinder::class, App\ModuleFinder::class)->addArgument(
            CommandDispatcher::class
        );
    }
}