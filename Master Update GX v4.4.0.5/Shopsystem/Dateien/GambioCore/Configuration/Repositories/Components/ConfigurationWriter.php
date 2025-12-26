<?php
/* --------------------------------------------------------------
 ConfigurationQbWriter.php  2019-10-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2019 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

namespace Gambio\Core\Configuration\Repositories\Components;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Exception;
use Gambio\Core\Configuration\Events\GroupCheckUpdated;
use Gambio\Core\Configuration\Models\Write\Interfaces\Configuration;
use Gambio\Core\Configuration\Models\Write\Interfaces\LanguageSpecificConfiguration;
use Psr\EventDispatcher\EventDispatcherInterface;
use function array_merge;
use function Gambio\Core\Logging\logger;

/**
 * Class ConfigurationWriter
 * @package Gambio\Core\ConfigurationBak\Repository
 */
class ConfigurationWriter
{
    public const  KEY_GROUP_CHECK = 'configuration/GROUP_CHECK';
    private const TABLE_NAME      = 'gx_configurations';
    
    /**
     * @var Connection
     */
    private $connection;
    
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    
    /**
     * @var array
     */
    private $languageIdCache = [];
    
    
    /**
     * ConfigurationWriter constructor.
     *
     * @param Connection               $connection
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        Connection $connection,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->connection      = $connection;
        $this->eventDispatcher = $eventDispatcher;
    }
    
    
    /**
     * Updates a configuration.
     *
     * @param Configuration $item
     */
    public function update(Configuration $item): void
    {
        if ($item->key() === self::KEY_GROUP_CHECK) {
            $this->eventDispatcher->dispatch(new GroupCheckUpdated($item->value() ?? 'false'));
        }
        
        if ($item instanceof LanguageSpecificConfiguration) {
            $this->updateLanguageSpecific($item);
            
            return;
        }
        if ($item instanceof Configuration) {
            $this->updateConfiguration($item);
        }
    }
    
    
    /**
     * Updates a configuration.
     *
     * @param Configuration $configuration
     */
    private function updateConfiguration(Configuration $configuration): void
    {
        $set = [$this->connection->quoteIdentifier('value') => $configuration->value()];
        
        try {
            $this->connection->update(self::TABLE_NAME, $set, $this->identifier($configuration->key()));
        } catch (DBALException $e) {
            logger()->notice('Failed to execute db query', $this->exceptionToArray($e));
        }
    }
    
    
    /**
     * Updates a language specific configuration.
     *
     * @param LanguageSpecificConfiguration $configuration
     */
    private function updateLanguageSpecific(LanguageSpecificConfiguration $configuration): void
    {
        $set = [$this->connection->quoteIdentifier('value') => $configuration->value()];
        
        try {
            $langWhere = array_merge(
                $this->identifier($configuration->key()),
                ['language_id' => $this->languageId($configuration->languageCode())]
            );
            $this->connection->update(self::TABLE_NAME, $set, $langWhere);
        } catch (DBALException $e) {
            logger()->notice('Failed to execute db query', $this->exceptionToArray($e));
        }
    }
    
    
    /**
     * Adds a new configuration.
     *
     * @param Configuration $item
     */
    public function add(Configuration $item): void
    {
        if ($item instanceof LanguageSpecificConfiguration) {
            $this->addLanguageSpecific($item);
            
            return;
        }
        if ($item instanceof Configuration) {
            $this->addConfiguration($item);
        }
    }
    
    
    /**
     * Adds a new configuration.
     *
     * @param Configuration $configuration
     */
    private function addConfiguration(Configuration $configuration): void
    {
        $data = [
            $this->connection->quoteIdentifier('key')   => $configuration->key(),
            $this->connection->quoteIdentifier('value') => $configuration->value(),
        ];
        
        try {
            $this->connection->insert(self::TABLE_NAME, $data);
        } catch (DBALException $e) {
            logger()->notice('Failed to execute db query', $this->exceptionToArray($e));
        }
    }
    
    
    /**
     * Adds a new language specific configuration.
     *
     * @param LanguageSpecificConfiguration $configuration
     */
    private function addLanguageSpecific(LanguageSpecificConfiguration $configuration): void
    {
        try {
            $data = [
                $this->connection->quoteIdentifier('key')         => $configuration->key(),
                $this->connection->quoteIdentifier('value')       => $configuration->value(),
                $this->connection->quoteIdentifier('language_id') => $this->languageId($configuration->languageCode()),
            ];
            
            $this->connection->insert(self::TABLE_NAME, $data);
        } catch (DBALException $e) {
            logger()->notice('Failed to execute db query', $this->exceptionToArray($e));
        }
    }
    
    
    /**
     * Deletes configurations by key name.
     *
     * @param string ...$keys
     */
    public function delete(string ...$keys): void
    {
        // just loop through, so i can use prepared statement instead of insecure WHERE IN
        // (in this case with arbitrary input keys)
        foreach ($keys as $key) {
            try {
                $this->connection->delete(self::TABLE_NAME, $this->identifier($key));
            } catch (DBALException $e) {
                logger()->notice('Failed to execute db query', $this->exceptionToArray($e));
            }
        }
    }
    
    
    /**
     * Returns an identifier array that can be used by the connection to create a where condition.
     * (Format: ['`key`' => $key])
     *
     * @param string $key
     *
     * @return array
     */
    private function identifier(string $key): array
    {
        return [$this->connection->quoteIdentifier('key') => $key];
    }
    
    
    /**
     * Returns the language id of the given language code.
     * Consecutive calls will use an internal cache to provide the id.
     *
     * @param string $languageCode
     *
     * @return string
     * @throws DBALException
     */
    private function languageId(string $languageCode): string
    {
        if (!array_key_exists($languageCode, $this->languageIdCache)) {
            $key = 'languages_id';
            
            $result = $this->connection->fetchAssoc(
                "SELECT {$key} FROM languages WHERE code = ?",
                [$languageCode]
            );
            
            $this->languageIdCache[$languageCode] = $result[$key] ?? '';
        }
        
        return $this->languageIdCache[$languageCode];
    }
    
    
    /**
     * Converts an exception in an array.
     *
     * @param Exception $e
     *
     * @return array
     */
    private function exceptionToArray(Exception $e): array
    {
        return [
            'message' => $e->getMessage(),
            'trace'   => $e->getTrace(),
            'code'    => $e->getCode(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ];
    }
}