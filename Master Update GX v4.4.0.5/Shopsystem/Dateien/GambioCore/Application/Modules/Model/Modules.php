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

namespace Gambio\Core\Application\Modules\Model;

use Gambio\Core\Application\Modules\Module;
use IteratorAggregate;
use Traversable;

/**
 * Interface Modules
 * @package Gambio\Core\Contracts\ModuleFoo
 */
interface Modules extends IteratorAggregate
{
    /**
     * @return Traversable|Module[]
     */
    public function getIterator();
}