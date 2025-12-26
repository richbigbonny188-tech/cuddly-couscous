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

namespace Gambio\Core\Application\Modules\App;

use Gambio\Core\Command\Interfaces\CommandDispatcher;
use Gambio\Core\Application\Modules\App\Commands\SearchAutoloader;
use Gambio\Core\Application\Modules\App\Commands\SearchModules;
use Gambio\Core\Application\Modules\App\Commands\SearchServiceProvider;
use Gambio\Core\Application\Modules\Model\Modules;
use Gambio\Core\Application\Modules\Services\ModuleFinder as Finder;

/**
 * Class ModuleFinder
 * @package Gambio\Core\Framework\Module\Services
 */
class ModuleFinder implements Finder
{
    /**
     * @var CommandDispatcher
     */
    private $dispatcher;
    
    
    /**
     * ModuleFinder constructor.
     *
     * @param CommandDispatcher $dispatcher
     */
    public function __construct(CommandDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getModules(): Modules
    {
        $command = new SearchModules();
        $this->dispatcher->dispatch($command);
        
        return $command->modules();
    }
    
    
    /**
     * @inheritDoc
     */
    public function getServiceProviderList(): array
    {
        $command = new SearchServiceProvider();
        $this->dispatcher->dispatch($command);
        
        return $command->serviceProviderList();
    }
    
    
    /**
     * @inheritDoc
     */
    public function getAutoloaderPaths(): array
    {
        $command = new SearchAutoloader();
        $this->dispatcher->dispatch($command);
        
        return $command->autoloaderPaths();
    }
}
