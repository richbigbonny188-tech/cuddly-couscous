<?php
/* --------------------------------------------------------------
 ConfigurationStorageWriter.php 2020-01-15
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 15 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Compatibility\Repositories\Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\InvalidArgumentException;

/**
 * Class ConfigurationStorageWriter
 * @package Gambio\Core\Configuration\Repositories\Legacy\Storage
 */
class ConfigurationStorageWriter
{
    private const TABLE_NAME = 'gx_configurations';
    
    /**
     * @var Connection
     */
    private $connection;
    
    /**
     * @var string
     */
    private $namespace;
    
    
    /**
     * StorageWriter constructor.
     *
     * @param Connection $connection
     * @param string     $namespace
     */
    public function __construct(Connection $connection, string $namespace)
    {
        $this->connection = $connection;
        $this->namespace  = $namespace;
    }
    
    
    /**
     * Sets a configuration value.
     * A new one will be added if the key didnt exist before, otherwise the existing key will be updated.
     *
     * @param string $key
     * @param string $value
     *
     * @throws DBALException
     */
    public function set(string $key, string $value): void
    {
        if ($this->keyExists($key)) {
            $this->connection->update(self::TABLE_NAME,
                                      [$this->connection->quoteIdentifier('value') => $value],
                                      [$this->connection->quoteIdentifier('key') => "{$this->namespace}/$key"]);
            
            return;
        }
        
        $this->connection->insert(self::TABLE_NAME,
                                  [
                                      $this->connection->quoteIdentifier('value') => $value,
                                      $this->connection->quoteIdentifier('key')   => "{$this->namespace}/$key"
                                  ]);
    }
    
    
    /**
     * Deletes a configuration from namespace by using the given key.
     *
     * @param string $key
     *
     * @throws DBALException
     * @throws InvalidArgumentException
     */
    public function delete(string $key): void
    {
        $identifier = [
            $this->connection->quoteIdentifier('key') => "{$this->namespace}/$key"
        ];
        $this->connection->delete(self::TABLE_NAME, $identifier);
    }
    
    
    /**
     * Deletes all configurations from namespace.
     *
     * It is possible to restrict the delete command by providing a prefix.
     *
     * @param string|null $prefix
     */
    public function deleteAll(string $prefix = null): void
    {
        $qb    = $this->connection->createQueryBuilder();
        $param = $prefix ? "{$this->namespace}/{$prefix}" : $this->namespace;
        $param .= '%';
        
        $where = "{$this->connection->quoteIdentifier('key')} LIKE {$qb->createNamedParameter($param)}";
        $qb->delete(self::TABLE_NAME)->where($where)->execute();
    }
    
    
    private function keyExists(string $key): bool
    {
        $qb = $this->connection->createQueryBuilder();
        
        $where  = "{$this->connection->quoteIdentifier('key')} = {$qb->createNamedParameter("{$this->namespace}/$key")}";
        $result = $qb->select('*')->from(self::TABLE_NAME)->where($where)->execute();
        
        return $result->rowCount() > 0;
    }
}