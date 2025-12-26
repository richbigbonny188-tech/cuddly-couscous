<?php
/* --------------------------------------------------------------
 GmConfigurationTableMigration.php 2020-01-16
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
 * Class GmConfigurationTableMigration
 * @package Gambio\Core\Configuration\Migration\Tables
 */
class GmConfigurationTableMigration implements ConfigurationMigration
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
     * Migrates data of gm_configuration to new gx_configurations table.
     */
    public function migrate()
    {
        try {
            $query = 'SELECT * FROM `gm_configuration` WHERE `gm_key` NOT LIKE "MAILBEEZ_%" AND `gm_key` NOT LIKE "MH_%";';
            
            $currentData = $this->connection->fetchAll($query);
        } catch (Throwable $e) {
            return;
        }
        
        $mapping = ConfigurationMigrationMapping::GM_CONFIGURATION;
        $prefix  = 'gm_configuration/';
        
        foreach ($currentData as $dataSet) {
            $configData = $this->assistant->createGxConfigDataSet($prefix, $mapping, $dataSet);
            $configKey  = $configData['key'];
            $languageId = array_key_exists('language_id', $configData) ? $configData['language_id'] : null;
            
            if (!$this->assistant->gxConfigKeyExists($configKey, $languageId)) {
                $qb   = $this->connection->createQueryBuilder();
                $data = [];
                
                foreach ($configData as $key => $value) {
                    if ($key === 'key') {
                        $value = $this->truncateString($value);
                    }
                    $data[$this->connection->quoteIdentifier($key)] = $qb->createNamedParameter($value);
                }
                
                $qb->insert('gx_configurations')->values($data)->execute();
            }
        }
    }
    
    
    /**
     * @param     $value
     *
     * @param int $length
     *
     * @return mixed
     */
    protected function truncateString($value, $length = 255)
    {
        if ($value && strlen($value) >= $length) {
            // Todo: Log the previous value
            $value = mb_strcut($value, 0, $length);
        }
        
        return $value;
    }
}