<?php
/* --------------------------------------------------------------
 ConfigurationStorageTableMigration.php 2020-01-16
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 16 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Migration\Tables;

use Doctrine\DBAL\Connection;
use Gambio\Core\Configuration\Migration\ConfigurationMigration;
use Gambio\Core\Configuration\Migration\ConfigurationMigrationAssistant;
use Gambio\Core\Configuration\Migration\ConfigurationMigrationMapping;
use Throwable;

/**
 * Class ConfigurationStorageTableMigration
 * @package Gambio\Core\Configuration\Migration\Tables
 */
class ConfigurationStorageTableMigration implements ConfigurationMigration
{
    /**
     * @var Connection
     */
    private $connection;
    
    /**
     * @var ConfigurationMigrationAssistant
     */
    private $assistant;
    
    
    /**
     * GmConfigurationTableMigration constructor.
     *
     * @param Connection                      $connection
     * @param ConfigurationMigrationAssistant $assistant
     */
    public function __construct(Connection $connection, ConfigurationMigrationAssistant $assistant)
    {
        $this->connection = $connection;
        $this->assistant  = $assistant;
    }
    
    
    /**
     * Migrates data from configuration_storage to gx_configurations table.
     */
    public function migrate()
    {
        try {
            $currentData = $this->connection->fetchAll('SELECT * FROM `configuration_storage`;');
        } catch (Throwable $e) {
            return;
        }
        
        $mapping = ConfigurationMigrationMapping::CONFIGURATION_STORAGE;
        
        foreach ($currentData as $dataSet) {
            $configData = $this->assistant->createGxConfigDataSet('', $mapping, $dataSet);
            $configKey  = $configData['key'];
            $languageId = array_key_exists('language_id', $configData) ? $configData['language_id'] : null;
            
            if (!$this->assistant->gxConfigKeyExists($configKey, $languageId)) {
                // apply custom type mapping (that are used to display UI components of configs)
                foreach (ConfigurationMigrationMapping::TYPE_MAP as $type => $keys) {
                    foreach ($keys as $key) {
                        if ($configKey === $key) {
                            $configData['type'] = $type;
                        }
                    }
                }
                
                $qb   = $this->connection->createQueryBuilder();
                $data = [];
                
                foreach ($configData as $key => $value) {
                    $data[$this->connection->quoteIdentifier($key)] = $qb->createNamedParameter($value);
                }
                
                $qb->insert('gx_configurations')->values($data)->execute();
            }
        }
    }
}