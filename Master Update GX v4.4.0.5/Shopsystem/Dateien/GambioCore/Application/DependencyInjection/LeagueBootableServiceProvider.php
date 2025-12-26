<?php
/* --------------------------------------------------------------
 LeagueBootableServiceProvider.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\DependencyInjection;

use Gambio\Core\Application\DependencyInjection\Contracts\BootableServiceProvider;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class LeagueBootableServiceProvider
 * @package Gambio\Core\Application\DependencyInjection
 */
class LeagueBootableServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var BootableServiceProvider
     */
    private $internal;
    
    
    /**
     * LeagueBootableServiceProvider constructor.
     *
     * @param BootableServiceProvider $internal
     */
    public function __construct(BootableServiceProvider $internal)
    {
        $this->internal = $internal;
        $this->provides = $this->internal->provides();
    }
    
    
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        $this->internal->boot();
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->internal->register();
    }
    
    
    /**
     * Overrides the identifier accessor.
     * Instead of using the class name as fallback identifier, we will use the name of the adapters class.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier ?? get_class($this->internal);
    }
}