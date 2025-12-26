<?php
/* --------------------------------------------------------------
 AbstractServiceProvider.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\DependencyInjection;

use Gambio\Core\Application\Contracts\Application;
use League\Container\ServiceProvider\ServiceProviderInterface;

/**
 * Class AbstractServiceProvider
 * @package Gambio\Core\Application\DependencyInjection
 */
abstract class AbstractServiceProvider implements Contracts\ServiceProvider, Contracts\ToLeagueServiceProvider
{
    /**
     * @var Application
     */
    protected $application;
    
    
    /**
     * AbstractBootableServiceProvider constructor.
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }
    
    
    /**
     * @inheritDoc
     */
    public function toLeagueServiceProvider(): ServiceProviderInterface
    {
        return new LeagueServiceProvider($this);
    }
}