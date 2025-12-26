<?php
/* --------------------------------------------------------------
 LeagueContainer.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\DependencyInjection;

use Gambio\Core\Application\DependencyInjection\Contracts\Container;
use Gambio\Core\Application\DependencyInjection\Contracts\Definition;
use Gambio\Core\Application\DependencyInjection\Contracts\Inflector;
use Gambio\Core\Application\DependencyInjection\Contracts\ToLeagueServiceProvider;

/**
 * Class LeagueContainer
 * @package Gambio\Core\Application
 */
class LeagueContainer implements Container
{
    /**
     * @var \League\Container\Container
     */
    private $internal;
    
    
    /**
     * LeagueContainer constructor.
     *
     * @param \League\Container\Container $internal
     */
    public function __construct(\League\Container\Container $internal)
    {
        $this->internal = $internal;
    }
    
    
    /**
     * @return static
     */
    public static function create(): self
    {
        return new static(new \League\Container\Container());
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(string $id, $concrete = null): Definition
    {
        return new LeagueDefinition(
            $this->internal->add($id, $concrete)
        );
    }
    
    
    /**
     * @inheritDoc
     */
    public function registerShared(string $id, $concrete = null): Definition
    {
        return new LeagueDefinition(
            $this->internal->share($id, $concrete)
        );
    }
    
    
    /**
     * @inheritDoc
     */
    public function registerProvider(ToLeagueServiceProvider $provider): Container
    {
        $this->internal->addServiceProvider($provider->toLeagueServiceProvider());
        
        return $this;
    }
    
    
    /**
     * @param string $leagueServiceProviderFqn
     *
     * @deprecated Should only be used from the LegacyDependencyContainer
     * @codeCoverageIgnore
     */
    public function registerLeagueProvider(string $leagueServiceProviderFqn): void
    {
        $this->internal->addServiceProvider($leagueServiceProviderFqn);
    }
    
    
    /**
     * @inheritDoc
     */
    public function inflect(string $type, callable $callback = null): Inflector
    {
        return new LeagueInflector(
            $this->internal->inflector($type, $callback)
        );
    }
    
    
    /**
     * @inheritDoc
     */
    public function get($id)
    {
        return $this->internal->get($id);
    }
    
    
    /**
     * @inheritDoc
     */
    public function has($id): bool
    {
        return $this->internal->has($id);
    }
}