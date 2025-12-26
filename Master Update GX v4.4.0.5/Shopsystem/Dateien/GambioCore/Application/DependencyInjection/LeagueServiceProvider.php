<?php
/* --------------------------------------------------------------
 LeagueServiceProvider.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\DependencyInjection;

use Gambio\Core\Application\DependencyInjection\Contracts\ServiceProvider;
use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Class LeagueServiceProvider
 * @package Gambio\Core\Application\DependencyInjection
 */
class LeagueServiceProvider extends AbstractServiceProvider
{
    /**
     * @var ServiceProvider
     */
    private $internal;
    
    
    /**
     * LeagueServiceProvider constructor.
     *
     * @param ServiceProvider $internal
     */
    public function __construct(ServiceProvider $internal)
    {
        $this->internal = $internal;
        $this->provides = $this->internal->provides();
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
