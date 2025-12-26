<?php
/* --------------------------------------------------------------
 HttpKernel.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Kernel;

use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;
use Gambio\Core\Application\Contracts\Kernel;
use RuntimeException;
use Slim\App;

/**
 * Class HttpKernel
 * @package Gambio\Core\Application\Kernel
 */
class HttpKernel implements Kernel
{
    /**
     * @var Application
     */
    private $application;
    
    
    /**
     * @inheritDoc
     */
    public function bootstrap(Application $application, Bootstrapper $bootstrapper): void
    {
        $this->application = $application;
        $bootstrapper->boot($application);
    }
    
    
    /**
     * @inheritDoc
     */
    public function run(): void
    {
        if (!$this->application) {
            throw new RuntimeException('The kernel must be bootstrapped first!');
        }
        
        if (!$this->application->has(App::class)) {
            throw new RuntimeException('Slim must be bootstrapped for the HTTP-Kernel!');
        }
        
        /** @var App $slim */
        $slim = $this->application->get(App::class);
        $slim->setBasePath(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
        $slim->run();
    }
}