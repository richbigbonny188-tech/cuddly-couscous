<?php
/* --------------------------------------------------------------
 DoctrineQbServiceProvider.php 2021-05-03
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\ServiceProviders;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;
use TestsConfig;
use function file_exists;
use function file_get_contents;
use function preg_match_all;

/**
 * Class DoctrineQbServiceProvider
 * @package Gambio\Core\ServiceProvider
 */
class DoctrineQbServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            Connection::class
        ];
    }
    
    
    /**
     * @inheritDoc
     * @throws DBALException
     */
    public function register(): void
    {
        $config = $this->getDatabaseConfig();
        $dbHost = $config['DB_SERVER'];
        $dbUser = $config['DB_SERVER_USERNAME'];
        $dbName = $this->lookupDatabaseName($config['DB_DATABASE']);
        $dbPass = $config['DB_SERVER_PASSWORD'] === '' ? '' : ':' . urlencode($config['DB_SERVER_PASSWORD']);
        
        $dsn = "pdo-mysql://$dbUser$dbPass@$dbHost/$dbName?charset=UTF8";
        
        $this->application->registerShared(Connection::class, DriverManager::getConnection(['url' => $dsn]));
    }

    protected function lookupDatabaseName(string $database) : string {
        if (isset($_SERVER['HTTP_X_GXDB'])
            && file_exists(__DIR__.'/../../../.dev-environment')
        ) {
            return $_SERVER['HTTP_X_GXDB'];
        }
    
        return $database;
    }
    
    
    /**
     * @return array
     */
    private function getDatabaseConfig(): array
    {
        if (!defined('DB_SERVER')) {
            if (file_exists(__DIR__ . '/../../../includes/local/configure.php')) {
                include_once __DIR__ . '/../../../includes/local/configure.php';
            } elseif (file_exists(__DIR__ . '/../../../includes/configure.php')) {
                include_once __DIR__ . '/../../../includes/configure.php';
            }
        }

        $config = [
            'DB_SERVER'          => defined('DB_SERVER') ? DB_SERVER : '',
            'DB_SERVER_USERNAME' => defined('DB_SERVER_USERNAME') ? DB_SERVER_USERNAME : '',
            'DB_SERVER_PASSWORD' => defined('DB_SERVER_PASSWORD') ? DB_SERVER_PASSWORD : '',
            'DB_DATABASE'        => defined('DB_DATABASE') ? DB_DATABASE : ''
        ];

        if ($config['DB_DATABASE'] === '') {
            return $this->testsConfigFallback();
        }

        return $config;
    }


    /**
     * @return array
     */
    private function testsConfigFallback(): array
    {
        if (file_exists($testsConfig = __DIR__ . '/../../../../tests/tests.config.inc.php')) {
            require_once $testsConfig;
            
            return [
                'DB_SERVER'          => TestsConfig::DB_HOST,
                'DB_SERVER_USERNAME' => TestsConfig::DB_USER,
                'DB_SERVER_PASSWORD' => TestsConfig::DB_PASSWORD,
                'DB_DATABASE'        => TestsConfig::DB_NAME(),
            ];
        }
        
        return [
            'DB_SERVER'          => '',
            'DB_SERVER_USERNAME' => '',
            'DB_SERVER_PASSWORD' => '',
            'DB_DATABASE'        => '',
        ];
    }
}