<?php
/* --------------------------------------------------------------
 LoadEnvironment.php 2020-08-24
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Bootstrapper;

use Gambio\Core\Application\ValueObjects\Environment;
use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;

/**
 * Class LoadEnvironment
 * @package Gambio\Core\Application\Bootstrapper
 */
class LoadEnvironment implements Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        $isDev = file_exists(__DIR__ . '/../../../.dev-environment');
        $application->registerShared(Environment::class)->addArgument($isDev);
    }
}