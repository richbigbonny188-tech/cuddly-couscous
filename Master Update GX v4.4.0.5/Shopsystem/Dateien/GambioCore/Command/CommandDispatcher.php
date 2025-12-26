<?php
/* --------------------------------------------------------------
   CommandDispatcher.php 2020-04-30
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Command;

use Gambio\Core\Command\Interfaces\CommandHandlerProvider;
use Throwable;
use function Gambio\Core\Logging\logger;

/**
 * Class CommandDispatcher
 *
 * @package Gambio\Core\Command
 */
class CommandDispatcher implements Interfaces\CommandDispatcher
{
    /**
     * @var CommandHandlerProvider[]
     */
    private $providers = [];
    
    
    /**
     * @inheritDoc
     */
    public function registerProvider(CommandHandlerProvider $provider): void
    {
        $this->providers[] = $provider;
        $this->providers   = array_unique($this->providers, SORT_REGULAR);
    }
    
    
    /**
     * @inheritDoc
     *
     * @throws Throwable
     */
    public function dispatch(object $command): object
    {
        try {
            foreach ($this->providers as $provider) {
                foreach ($provider->getHandlerForCommand($command) as $handler) {
                    $handler($command);
                }
            }
        } catch (Throwable $error) {
            logger('commands')->error(
                'Error while dispatching command occurred.',
                ['command' => get_class($command), 'error' => $error]
            );
            throw $error;
        }
        
        return $command;
    }
}