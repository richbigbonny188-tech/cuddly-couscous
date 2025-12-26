<?php
/* --------------------------------------------------------------
 CommandDispatcherRegistration.php 2020-04-06
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Bootstrapper;

use Gambio\Core\Command\CommandDispatcherServiceProvider;
use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;

/**
 * Class CommandDispatcherRegistration
 * @package Gambio\Core\Application\Bootstrapper
 */
class CommandDispatcherRegistration implements Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        $application->registerProvider(CommandDispatcherServiceProvider::class);
    }
}