<?php
/* --------------------------------------------------------------
   LoggingServiceProvider.php 2020-04-21
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Logging;

use Gambio\Core\Application\DependencyInjection\AbstractBootableServiceProvider;
use Gambio\Core\Logging\Builder\TextAndJsonLoggerBuilder;

/**
 * Class LoggingServiceProvider
 *
 * @package Gambio\Core\Logging
 */
class LoggingServiceProvider extends AbstractBootableServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            LoggerBuilder::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->register(LoggerBuilder::class, TextAndJsonLoggerBuilder::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        LoggerFactory::registerApplication($this->application);
        require_once __DIR__ . '/functions.php';
    }
}