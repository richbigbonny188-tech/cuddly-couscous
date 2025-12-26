<?php
/* --------------------------------------------------------------
 Modules.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules\Model\Collections;

use ArrayIterator;
use Gambio\Core\Application\Modules\Module;
use Gambio\Core\Application\Modules\Model\Modules as ModulesCollection;

/**
 * Class Modules
 * @package Gambio\Core\Framework\Module
 */
class Modules implements ModulesCollection
{
    /**
     * @var Module[]
     */
    private $modules;
    
    
    /**
     * Modules constructor.
     *
     * @param Module ...$modules
     */
    public function __construct(Module ...$modules)
    {
        $this->modules = $modules;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getIterator(): iterable
    {
        return new ArrayIterator($this->modules);
    }
}