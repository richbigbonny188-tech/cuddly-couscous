<?php
/* --------------------------------------------------------------
 SearchModules.php 2020-09-14
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules\App\Commands;

use Gambio\Core\Application\Modules\Module;
use Gambio\Core\Application\Modules\Model\Modules;
use Gambio\Core\Application\Modules\Model\Collections\Modules as ModulesCollection;

/**
 * Class SearchModules
 * @package Gambio\Core\Framework\Module\Commands
 */
class SearchModules
{
    /**
     * @var array
     */
    private $modules = [];
    
    
    /**
     * Adds a new module.
     *
     * @param Module $module
     */
    public function addModule(Module $module): void
    {
        $this->modules[] = $module;
    }
    
    
    /**
     * Returns a list of all collected modules.
     *
     * @return Modules
     */
    public function modules(): Modules
    {
        return new ModulesCollection(...$this->modules);
    }
}