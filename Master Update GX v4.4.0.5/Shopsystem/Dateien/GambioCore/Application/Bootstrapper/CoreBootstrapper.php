<?php
/* --------------------------------------------------------------
 CoreBootstrapper.php 2021-01-08
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Bootstrapper;

use Gambio\Core\Application\Contracts\Application;

/**
 * Class CoreBootstrapper
 * @package Gambio\Core\Application\Bootstrapper
 * @codeCoverageIgnore
 */
abstract class CoreBootstrapper
{
    /**
     * Loads environment data for the application.
     *
     * @param Application $application
     */
    protected function loadEnvironment(Application $application): void
    {
        (new LoadEnvironment())->boot($application);
    }
    
    
    /**
     * Load server information for the application.
     *
     * @param Application $application
     */
    protected function loadServerInformation(Application $application): void
    {
        (new LoadServerInformation())->boot($application);
    }
    
    
    /**
     * Loads user preferences from session for the application.
     *
     * @param Application $application
     */
    protected function loadUserPreferencesFromSession(Application $application): void
    {
        (new LoadUserPreferencesFromSession())->boot($application);
    }
    
    
    /**
     * Registers the command dispatcher.
     *
     * @param Application $application
     */
    protected function registerCommandDispatcher(Application $application): void
    {
        (new CommandDispatcherRegistration())->boot($application);
    }
    
    
    /**
     * Register the event dispatcher.
     *
     * @param Application $application
     */
    protected function registerEventDispatcher(Application $application): void
    {
        (new EventDispatcherRegistration())->boot($application);
    }
    
    
    /**
     * Registers the core service provider.
     *
     * @param Application $application
     */
    protected function registerCoreServiceProvider(Application $application): void
    {
        (new CoreServiceProviderRegistration())->boot($application);
    }
    
    
    /**
     * Registers external modules.
     *
     * @param Application $application
     */
    protected function registerModules(Application $application): void
    {
        (new ModuleRegistration())->boot($application);
    }
    
    
    /**
     * Registers the slim framework.
     *
     * @param Application $application
     */
    protected function registerSlimFramework(Application $application): void
    {
        (new SlimAppRegistration())->boot($application);
    }
    
    
    /**
     * Starts the session.
     *
     * @param Application $application
     */
    protected function startSession(Application $application): void
    {
        (new StartSession())->boot($application);
    }
    
    
    /**
     * Sets session parameters.
     *
     * @param Application $application
     */
    protected function setSessionParameter(Application $application): void
    {
        (new SetSessionParameters())->boot($application);
    }
    
    
    /**
     * Initializes default server configuration like timezone, error reporting and memory limit.
     *
     * @param Application $application
     */
    protected function initDefaultServerConfiguration(Application $application): void
    {
        (new DefaultServerConfiguration())->boot($application);
    }
}