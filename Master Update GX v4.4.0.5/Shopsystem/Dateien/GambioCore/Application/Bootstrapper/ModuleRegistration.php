<?php
/* --------------------------------------------------------------
 ModuleRegistration.php 2020-08-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Bootstrapper;

use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;
use Gambio\Core\Application\Modules\Module;
use Gambio\Core\Application\Modules\Services\ModuleFinder;
use Slim\App as Slim;
use Throwable;
use UnexpectedValueException;
use function Gambio\Core\Logging\logger;

/**
 * Class ModuleRegistration
 * @package Gambio\Core\Application\Bootstrapper
 */
class ModuleRegistration implements Bootstrapper
{
    private const LOGGER_NAMESPACE = 'module-registration';
    
    
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        $moduleFinder = $this->getModuleFinder($application);
        
        if (!$moduleFinder) {
            return;
        }
        $this->registerAutoloader($moduleFinder);
        $this->registerServiceProvider($application, $moduleFinder);
        
        $slim = $this->getSlim($application);
        foreach ($moduleFinder->getModules() as $module) {
            try {
                $this->registerModule($module, $application, $slim);
            } catch (Throwable $throwable) {
                $this->handleError($throwable, $module);
            }
        }
    }
    
    
    /**
     * Registers autoloader of external modules.
     *
     * @param ModuleFinder $finder
     */
    private function registerAutoloader(ModuleFinder $finder): void
    {
        foreach ($finder->getAutoloaderPaths() as $autoloaderPath) {
            /** @noinspection PhpIncludeInspection */
            require_once $autoloaderPath;
        }
    }
    
    
    /**
     * Registers service providers of external modules.
     *
     * @param Application  $application
     * @param ModuleFinder $finder
     */
    private function registerServiceProvider(Application $application, ModuleFinder $finder): void
    {
        foreach ($finder->getServiceProviderList() as $provider) {
            $application->registerProvider($provider);
        }
    }
    
    
    /**
     * Module registration.
     *
     * This method takes the module definitions and registers the custom functionality for
     * the application.
     *
     * @param Module      $module
     * @param Application $application
     * @param Slim        $slim
     */
    private function registerModule(Module $module, Application $application, Slim $slim): void
    {
        $dependencies = $module->dependsOn();
        if ($dependencies) {
            $missing = [];
            foreach ($dependencies as $dependency) {
                if (!$application->has($dependency)) {
                    $missing[] = $dependency;
                }
            }
            
            if (!empty($missing)) {
                $this->logMissingDependencies($module, $missing);
                
                return;
            }
        }
        
        $this->registerCommandHandler($module, $application);
        $this->registerEventListener($module, $application);
        
        $this->registerRoutes('get', $module->getRoutes(), $slim);
        $this->registerRoutes('post', $module->postRoutes(), $slim);
        $this->registerRoutes('put', $module->putRoutes(), $slim);
        $this->registerRoutes('delete', $module->deleteRoutes(), $slim);
    }
    
    
    /**
     * Registers the $routes to the given HTTP $method.
     *
     * @param string     $method
     * @param array|null $routes
     * @param Slim       $slim
     */
    private function registerRoutes(string $method, ?array $routes, Slim $slim): void
    {
        foreach ($routes ?? [] as $route => $callback) {
            if (is_array($callback)) {
                $callbackMethod = implode(':', $callback);
                $slim->$method($route, $callbackMethod);
            } elseif (is_string($callback)) {
                $slim->$method($route, $callback);
            }
        }
    }
    
    
    /**
     * Registers the module's command handler.
     *
     * @param Module      $module
     * @param Application $application
     */
    private function registerCommandHandler(Module $module, Application $application): void
    {
        foreach ($module->commandHandlers() ?? [] as $command => $handlers) {
            foreach ($handlers as $handler) {
                $application->attachCommandHandler($command, $handler);
            }
        }
    }
    
    
    /**
     * Registers the module's event listener.
     *
     * @param Module      $module
     * @param Application $application
     */
    private function registerEventListener(Module $module, Application $application): void
    {
        foreach ($module->eventListeners() ?? [] as $event => $listeners) {
            foreach ($listeners as $listener) {
                $application->attachEventListener($event, $listener);
            }
        }
    }
    
    
    /**
     * Handles module registration errors.
     *
     * @param Throwable $throwable
     * @param Module    $module
     */
    private function handleError(Throwable $throwable, Module $module): void
    {
        $moduleName = get_class($module);
        $msg        = "Failed to load module ({$moduleName})";
        $context    = [
            'classname' => $moduleName,
            'message'   => $throwable->getMessage()
        ];
        
        $logger = logger(static::LOGGER_NAMESPACE);
        $logger->error($msg, $context);
    }
    
    
    private function logMissingDependencies(Module $module, array $missingDependencies): void
    {
        $moduleClass = get_class($module);
        $namespace   = explode('\\', $moduleClass);
        array_shift($namespace);
        $vendor = array_shift($namespace);
        $module = array_shift($namespace);
        
        $message = "Failed to register module '{$moduleClass}' due to missing dependencies.";
        $message .= " (Vendor: {$vendor}, Module: {$module})";
        
        $logger = logger(static::LOGGER_NAMESPACE);
        $logger->warning($message, ['missing' => $missingDependencies]);
    }
    
    
    /**
     * Returns the slim application instance, if available.
     *
     * @param Application $application
     *
     * @return Slim
     */
    private function getSlim(Application $application): Slim
    {
        if (!$application->has(Slim::class)) {
            throw new UnexpectedValueException('Slim must be registered before the module registration!');
        }
        
        return $application->get(Slim::class);
    }
    
    
    /**
     * Returns the module finder, if available.
     *
     * @param Application $application
     *
     * @return ModuleFinder|null
     */
    private function getModuleFinder(Application $application): ?ModuleFinder
    {
        return $application->has(ModuleFinder::class) ? $application->get(ModuleFinder::class) : null;
    }
}