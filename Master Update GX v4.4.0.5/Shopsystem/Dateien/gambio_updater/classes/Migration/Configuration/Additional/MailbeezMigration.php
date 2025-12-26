<?php
/* --------------------------------------------------------------
 MailbeezMigration.php 2020-04-20
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

namespace Gambio\Core\Configuration\Migration\Additional;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Gambio\Core\Configuration\Migration\ConfigurationMigration;
use Gambio\Core\Configuration\Migration\ConfigurationMigrationAssistant;

/**
 * Class MailbeezMigration
 * @package Gambio\Core\Configuration\Migration\PostMigrationFix
 */
class MailbeezMigration implements ConfigurationMigration
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
     * MailbeezMigration constructor.
     *
     * @param Connection                      $connection
     * @param ConfigurationMigrationAssistant $assistant
     */
    public function __construct(Connection $connection, ConfigurationMigrationAssistant $assistant)
    {
        $this->connection = $connection;
        $this->assistant  = $assistant;
    }
    
    
    public function migrate()
    {
        try {
            $query = 'SELECT * FROM `configuration` WHERE `configuration_key` LIKE "MAILBEEZ_%" OR `configuration_key` LIKE "MH_%";';
            $data  = $this->connection->fetchAll($query);
        } catch (DBALException $e) {
            return;
        }
        
        foreach ($data as $dataset) {
            $key = "mailbeez/{$dataset['configuration_key']}";
            if ($this->assistant->gxConfigKeyExists($key, null)) {
                continue;
            }
            
            $valueData = [
                'value'        => $dataset['configuration_value'],
                'set_function' => $dataset['set_function'],
                'use_function' => $dataset['use_function'],
            ];
            $value     = json_encode($valueData);
            $newData   = [
                $this->connection->quoteIdentifier('key')   => $key,
                $this->connection->quoteIdentifier('value') => $value
            ];
            
            try {
                $this->connection->insert('gx_configurations', $newData);
            } catch (DBALException $e) {
            }
        }
        
        try {
            $query = 'DELETE FROM `gx_configurations` WHERE `key` LIKE "configuration/MAILBEEZ_%" OR `key` LIKE "configuration/MH_%";';
            $this->connection->exec($query);
        } catch (DBALException $e) {
        }
    }
}