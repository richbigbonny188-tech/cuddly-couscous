<?php
/* --------------------------------------------------------------
 StartSession.php 2020-03-04
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

/**
 * Class StartSession
 * @package Gambio\Core\Application\Bootstrapper
 * @codeCoverageIgnore
 */
class StartSession implements Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        session_start();
    }
}