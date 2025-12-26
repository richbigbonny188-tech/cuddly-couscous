<?php
/*--------------------------------------------------------------
   UserNavigationHistoryServiceProvider.php 2020-09-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\UserNavigationHistory;

use Doctrine\DBAL\Connection;
use Gambio\Shop\UserNavigationHistory\Database\Reader\CategoryDescriptionReader;
use Gambio\Shop\UserNavigationHistory\Database\Reader\MysqlCategoryDescriptionReader;
use Gambio\Shop\UserNavigationHistory\Database\Repository\HistoryRepository;
use Gambio\Shop\UserNavigationHistory\Factories\HistoryFactory;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Class UserNavigationHistoryServiceProvider
 * @package Gambio\Shop\UserNavigationHistory
 * @property  Container container
 * @codeCoverageIgnore
 */
class UserNavigationHistoryServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var array
     */
    protected $provides = [UserNavigationHistoryService::class];
    
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
    
    }
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->container->share(UserNavigationHistoryService::class)->addArgument(HistoryFactory::class)->addArgument(HistoryRepository::class);
        $this->container->share(HistoryFactory::class);
        $this->container->share(HistoryRepository::class)->addArgument(CategoryDescriptionReader::class);
        $this->container->share(CategoryDescriptionReader::class, MysqlCategoryDescriptionReader::class)->addArgument(Connection::class);
    }
}