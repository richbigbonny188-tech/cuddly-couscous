<?php
/* --------------------------------------------------------------
   AdminAccessServiceProvider.php 2020-07-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\AdminAccess;

use Doctrine\DBAL\Connection;
use Gambio\Core\AdminAccess\Admin\AdminFactory;
use Gambio\Core\AdminAccess\Admin\Repository\AdminMapper;
use Gambio\Core\AdminAccess\Admin\Repository\AdminReader;
use Gambio\Core\AdminAccess\Admin\Repository\AdminRepository;
use Gambio\Core\AdminAccess\Admin\Repository\AdminWriter;
use Gambio\Core\AdminAccess\Group\GroupFactory;
use Gambio\Core\AdminAccess\Group\Repository\GroupMapper;
use Gambio\Core\AdminAccess\Group\Repository\GroupReader;
use Gambio\Core\AdminAccess\Group\Repository\GroupRepository;
use Gambio\Core\AdminAccess\Group\Repository\GroupWriter;
use Gambio\Core\AdminAccess\Role\Repository\RoleMapper;
use Gambio\Core\AdminAccess\Role\Repository\RoleReader;
use Gambio\Core\AdminAccess\Role\Repository\RoleRepository;
use Gambio\Core\AdminAccess\Role\Repository\RoleWriter;
use Gambio\Core\AdminAccess\Role\RoleFactory;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;
use Gambio\Core\Language\LanguageService;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class AdminAccessServiceProvider
 *
 * @package Gambio\Core\AdminAccess
 */
class AdminAccessServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            AdminFactory::class,
            AdminService::class,
            GroupFactory::class,
            GroupService::class,
            RoleService::class,
            RoleFactory::class,
            PermissionService::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->registerAdminComponents();
        $this->registerGroupComponents();
        $this->registerRoleComponents();
    }
    
    
    private function registerAdminComponents(): void
    {
        $this->application->registerShared(AdminMapper::class)->addArgument(AdminFactory::class);
        
        $this->application->registerShared(AdminReader::class)->addArgument(Connection::class);
        
        $this->application->registerShared(AdminWriter::class)->addArgument(Connection::class);
        
        $this->application->registerShared(AdminRepository::class)->addArgument(AdminMapper::class)->addArgument(
            AdminReader::class
        )->addArgument(AdminWriter::class)->addArgument(EventDispatcherInterface::class);
        
        $this->application->registerShared(AdminFactory::class, Admin\Model\AdminFactory::class);
        
        $this->application->registerShared(AdminService::class, Services\AdminService::class)->addArgument(
            AdminRepository::class
        )->addArgument(AdminFactory::class);
    }
    
    
    private function registerGroupComponents(): void
    {
        $this->application->registerShared(GroupMapper::class)->addArgument(GroupFactory::class);
        
        $this->application->registerShared(GroupReader::class)->addArgument(Connection::class);
        
        $this->application->registerShared(GroupWriter::class)->addArgument(Connection::class)->addArgument(
            LanguageService::class
        );
        
        $this->application->registerShared(GroupRepository::class)->addArgument(GroupMapper::class)->addArgument(
            GroupReader::class
        )->addArgument(GroupWriter::class)->addArgument(EventDispatcherInterface::class);
        
        $this->application->registerShared(GroupFactory::class, Group\Model\GroupFactory::class);
        
        $this->application->registerShared(GroupService::class, Services\GroupService::class)->addArgument(
            GroupRepository::class
        )->addArgument(GroupFactory::class);
    }
    
    
    private function registerRoleComponents(): void
    {
        $this->application->registerShared(RoleMapper::class)->addArgument(RoleFactory::class);
        
        $this->application->registerShared(RoleReader::class)->addArgument(Connection::class);
        
        $this->application->registerShared(RoleWriter::class)->addArgument(Connection::class)->addArgument(
            LanguageService::class
        );
        
        $this->application->registerShared(RoleRepository::class)->addArgument(RoleMapper::class)->addArgument(
                RoleReader::class
            )->addArgument(RoleWriter::class)->addArgument(EventDispatcherInterface::class);
        
        $this->application->registerShared(RoleFactory::class, Role\Model\RoleFactory::class);
        
        $this->application->registerShared(RoleService::class, Services\RoleService::class)->addArgument(
                RoleRepository::class
            )->addArgument(RoleFactory::class);
        
        $this->application->registerShared(PermissionService::class, Services\PermissionService::class)->addArgument(
            GroupService::class
        )->addArgument(RoleService::class)->addArgument(RoleFactory::class);
    }
}