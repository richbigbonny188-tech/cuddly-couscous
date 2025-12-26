<?php
/* --------------------------------------------------------------
 Application.php 2022-01-28
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2022 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application;

use BadMethodCallException;
use Gambio\Core\Command\Interfaces\CommandHandlerProvider;
use Gambio\Core\Event\EventListenerProvider;
use Gambio\Core\Application\Contracts\Bootstrapper;
use Gambio\Core\Application\Contracts\Kernel;
use Gambio\Core\Application\DependencyInjection\Contracts\Container;
use Gambio\Core\Application\DependencyInjection\Contracts\Definition;
use Gambio\Core\Application\DependencyInjection\Contracts\Inflector;
use Gambio\Core\Application\DependencyInjection\LeagueContainer;

/**
 * Class Application
 * @package Gambio\Core\Application
 */
class Application implements Contracts\Application
{
    public const VERSION = 'v4.4.0.5';
    
    /**
     * @var Container
     */
    private $container;
    
    /**
     * @var EventListenerProvider|null
     */
    private $eventListener;
    
    /**
     * @var CommandHandlerProvider|null
     */
    private $commandHandler;
    
    
    /**
     * Application constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    
    /**
     * @inheritDoc
     */
    public static function main(Kernel $kernel, Bootstrapper $bootstrapper): void
    {
        $app = new static(LeagueContainer::create());
        $kernel->bootstrap($app, $bootstrapper);
        
        $kernel->run();
    }
    
    
    /**
     * @inheritDoc
     */
    public function registerProvider(string $serviceProvider): void
    {
        $this->container->registerProvider(
            new $serviceProvider($this)
        );
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(string $id, $concrete = null): Definition
    {
        return $this->container->register($id, $concrete);
    }
    
    
    /**
     * @inheritDoc
     */
    public function registerShared(string $id, $concrete = null): Definition
    {
        return $this->container->registerShared($id, $concrete);
    }
    
    
    /**
     * @inheritDoc
     */
    public function inflect(string $type, callable $callback = null): Inflector
    {
        return $this->container->inflect($type, $callback);
    }
    
    
    /**
     * @inheritDoc
     */
    public function get($id)
    {
        return $this->container->get($id);
    }
    
    
    /**
     * @inheritDoc
     */
    public function has($id): bool
    {
        return $this->container->has($id);
    }
    
    
    /**
     * @inheritDoc
     */
    public function attachEventListener(string $eventClass, string $listener): void
    {
        if (null === $this->eventListener && $this->container->has(EventListenerProvider::class)) {
            $this->eventListener = $this->container->get(EventListenerProvider::class);
        }
        if ($this->eventListener) {
            $this->eventListener->attachListener($eventClass, $listener);
        }
    }
    
    
    /**
     * @inheritDoc
     */
    public function attachCommandHandler(string $commandClass, string $handler): void
    {
        if (null === $this->commandHandler && $this->container->has(CommandHandlerProvider::class)) {
            $this->commandHandler = $this->container->get(CommandHandlerProvider::class);
        }
        if ($this->commandHandler) {
            $this->commandHandler->attachHandler($commandClass, $handler);
        }
    }
    
    
    /**
     * Proxy for ::share and ::add methods to call ::registerShared and ::register, respectively.
     *
     * This method is implemented for compatibility purposes and will be removed very soon.
     * So it is important that the ::share and ::add methods wont be used anymore.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return Definition
     * @deprecated
     */
    public function __call(string $name, array $arguments)
    {
        if ($name === 'share') {
            return $this->registerShared(...$arguments);
        }
        if ($name === 'add') {
            return $this->register(...$arguments);
        }
        
        throw new BadMethodCallException("Method (::$name) not found in " . __CLASS__);
    }
}