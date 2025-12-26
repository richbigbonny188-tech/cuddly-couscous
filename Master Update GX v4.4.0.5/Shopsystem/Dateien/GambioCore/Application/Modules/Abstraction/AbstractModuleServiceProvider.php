<?php
/* --------------------------------------------------------------
 ServiceProviderAdapter.php 2020-03-30
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules\Abstraction;

/**
 * Class AbstractModuleServiceProvider
 * @package    Gambio\Core\Application\Modules\Abstraction
 * @deprecated Should not be used anymore and only exists for hub connector compatibility.
 */
abstract class AbstractModuleServiceProvider
    extends \Gambio\Core\Application\Modules\App\DependencyInjection\AbstractModuleServiceProvider
{
}