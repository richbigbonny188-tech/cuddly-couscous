<?php
/* --------------------------------------------------------------
 EventDispatcherRegistration.php 2020-03-10
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Bootstrapper;

use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;
use Gambio\Core\Event\EventDispatcherServiceProvider;

/**
 * Class EventDispatcherRegistration
 * @package Gambio\Core\Application\Bootstrapper
 *
 *  Event dispatcher registration.
 *  The nice thing about a special bootstrapper for the EventDispatcher is that if we run it very early,
 *  later bootstrapper can use the dispatcher
 *
 */
class EventDispatcherRegistration implements Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        $application->registerProvider(EventDispatcherServiceProvider::class);
    }
}