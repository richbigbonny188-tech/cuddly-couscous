<?php
/* --------------------------------------------------------------
 ConfigurationServiceProvider.php 2020-04-21
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2019 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration;

use Doctrine\DBAL\Connection;
use Gambio\Core\Application\ValueObjects\UserPreferences;
use Gambio\Core\Configuration\Builder\ConfigurationFinderBuilder;
use Gambio\Core\Configuration\Compatibility\ConfigurationStorageRepositoryBuilder;
use Gambio\Core\Configuration\Compatibility\ModulesConfigurationRepository;
use Gambio\Core\Configuration\Compatibility\Repositories\ModuleCenterConfigurationRepository;
use Gambio\Core\Configuration\Compatibility\Repositories\Storage\NamespaceConverter;
use Gambio\Core\Configuration\EventListeners\GroupPermissionListener;
use Gambio\Core\Configuration\Events\GroupCheckUpdated;
use Gambio\Core\Configuration\Repositories\Components\ConfigurationFactory;
use Gambio\Core\Configuration\Repositories\Components\ConfigurationMapper;
use Gambio\Core\Configuration\Repositories\Components\ConfigurationReader;
use Gambio\Core\Configuration\Repositories\Components\ConfigurationWriter;
use Gambio\Core\Configuration\Repositories\Components\OptionsResolver;
use Gambio\Core\Configuration\Repositories\ConfigurationGroupRepository;
use Gambio\Core\Configuration\Repositories\ConfigurationRepository;
use Gambio\Core\Configuration\Repositories\Implementations\ConfigurationGroupRepository as ConfigurationGroupRepositoryImpl;
use Gambio\Core\Configuration\Repositories\Implementations\ConfigurationRepository as ConfigurationRepositoryImpl;
use Gambio\Core\Configuration\Services\ConfigurationService as ConfigurationServiceImpl;
use Gambio\Core\Application\DependencyInjection\AbstractBootableServiceProvider;
use Gambio\Core\Language\TextManager;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class ConfigurationServiceProvider
 * @package Gambio\Core\Configuration
 */
class ConfigurationServiceProvider extends AbstractBootableServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            ConfigurationService::class,
            ConfigurationFinder::class,
            ConfigurationFinderBuilder::class,
            
            ConfigurationStorageRepositoryBuilder::class,
            ModulesConfigurationRepository::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        $this->application->attachEventListener(GroupCheckUpdated::class, GroupPermissionListener::class);
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->registerConfigurationService();
        $this->registerConfigurationFinders();
        $this->registerConfigurationRepository();
        $this->registerConfigurationComponents();
        $this->registerConfigurationStorage();
        $this->registerModuleConfigurationRepository();
        $this->registerConfigurationGroupRepository();
        $this->registerEventListeners();
    }
    
    
    /**
     * Registers the configuration service.
     */
    private function registerConfigurationService(): void
    {
        $this->application->registerShared(ConfigurationService::class, ConfigurationServiceImpl::class)->addArgument(
            ConfigurationRepository::class
        );
    }
    
    
    private function registerConfigurationFinders(): void
    {
        $this->application->registerShared(ConfigurationFinder::class, Services\ConfigurationFinder::class)
                          ->addArgument(
                              ConfigurationService::class
                          );
        $this->application->registerShared(ConfigurationFinderBuilder::class)->addArgument(ConfigurationFinder::class);
    }
    
    
    /**
     * Registers the configuration group repository.
     */
    private function registerConfigurationGroupRepository(): void
    {
        $this->application->registerShared(ConfigurationGroupRepository::class, ConfigurationGroupRepositoryImpl::class)
                          ->addArgument(ConfigurationReader::class)
                          ->addArgument(ConfigurationMapper::class);
    }
    
    
    /**
     * Registers the module configuration repository.
     */
    private function registerModuleConfigurationRepository(): void
    {
        $this->application->registerShared(
            ModulesConfigurationRepository::class,
            ModuleCenterConfigurationRepository::class
        )->addArgument(Connection::class)->addArgument(ConfigurationMapper::class);
    }
    
    
    /**
     * Registers the configuration storage.
     */
    private function registerConfigurationStorage(): void
    {
        $this->application->registerShared(ConfigurationStorageRepositoryBuilder::class)
                          ->addArgument(Connection::class)
                          ->addArgument(NamespaceConverter::class);
        $this->application->registerShared(NamespaceConverter::class);
    }
    
    
    /**
     * Registers the configuration repository.
     */
    private function registerConfigurationRepository(): void
    {
        $this->application->registerShared(ConfigurationRepository::class, ConfigurationRepositoryImpl::class)
                          ->addArgument(
                              ConfigurationReader::class
                          )
                          ->addArgument(ConfigurationWriter::class)
                          ->addArgument(ConfigurationFactory::class);
    }
    
    
    /**
     * Registers the configuration reader, writer, factory, mapper and options resolver.
     */
    private function registerConfigurationComponents(): void
    {
        $this->application->registerShared(ConfigurationReader::class)->addArgument(Connection::class);
        $this->application->registerShared(ConfigurationWriter::class)->addArgument(Connection::class)->addArgument(
            EventDispatcherInterface::class
        );
        $this->application->registerShared(ConfigurationFactory::class);
        
        $this->application->registerShared(ConfigurationMapper::class)
                          ->addArgument(OptionsResolver::class)
                          ->addArgument(
                              ConfigurationFactory::class
                          );
        
        $this->application->registerShared(OptionsResolver::class)->addArgument(Connection::class)->addArgument(
            TextManager::class
        )->addArgument(UserPreferences::class);
    }
    
    
    public function registerEventListeners(): void
    {
        $this->application->registerShared(GroupPermissionListener::class)
                          ->addArgument(ConfigurationService::class)
                          ->addArgument(Connection::class);
    }
}