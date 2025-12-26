<?php
/* --------------------------------------------------------------
 AbstractModuleBootableServiceProvider.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules\App\DependencyInjection;

use Gambio\Core\Application\DependencyInjection\AbstractBootableServiceProvider;
use Gambio\Core\Application\Modules\DependencyInjection\ModuleBootableServiceProvider;

/**
 * Class AbstractModuleBootableServiceProvider
 * @package Gambio\Core\Framework\Module\DependencyInjection
 */
abstract class AbstractModuleBootableServiceProvider extends AbstractBootableServiceProvider
    implements ModuleBootableServiceProvider
{
}