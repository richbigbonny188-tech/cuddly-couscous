<?php
/* --------------------------------------------------------------
 DoctrineConnectionServiceProvider.php 2020-04-16
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use League\Container\ServiceProvider\AbstractServiceProvider;

class DoctrineConnectionServiceProvider extends AbstractServiceProvider
{
    protected $provides = [
        Connection::class
    ];
    
    
    public function register(): void
    {
        $host     = DB_SERVER;
        $user     = DB_SERVER_USERNAME;
        $name     = DB_DATABASE;
        $password = DB_SERVER_PASSWORD;
        $password = $password === '' ? '' : ':' . urlencode($password);
        
        $dsn = "pdo-mysql://{$user}{$password}@{$host}/{$name}?charset=UTF8";
        
        $this->leagueContainer->share(
            Connection::class,
            static function () use ($dsn) {
                return DriverManager::getConnection(['url' => $dsn]);
            }
        );
    }
}