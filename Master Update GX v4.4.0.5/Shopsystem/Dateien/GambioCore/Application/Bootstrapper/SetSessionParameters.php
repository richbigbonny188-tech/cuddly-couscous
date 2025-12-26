<?php
/* --------------------------------------------------------------
 SetSessionParameters.php 2020-02-127-17
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Bootstrapper;

use Gambio\Core\Application\ValueObjects\Path;
use Gambio\Core\Application\ValueObjects\Server;
use Gambio\Core\Application\ValueObjects\Url;
use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;
use Gambio\Core\Session\SessionNamePostfixGenerator;
use Gambio\Core\Session\SessionRepository;
use Gambio\Core\Session\SessionService;

/**
 * Class SetSessionParameters
 * @package Gambio\Core\Application\Bootstrapper
 * @codeCoverageIgnore
 */
class SetSessionParameters implements Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        /**
         * @var Path   $path
         * @var Url    $url
         * @var Server $server
         */
        $path   = $application->get(Path::class);
        $url    = $application->get(Url::class);
        $server = $application->get(Server::class);
        
        $sessionRepository = new SessionRepository(new SessionNamePostfixGenerator(), $path);
        $sessionService    = new SessionService($sessionRepository, $url, $path, $server);
        
        $sessionService->setupSession();
    }
}