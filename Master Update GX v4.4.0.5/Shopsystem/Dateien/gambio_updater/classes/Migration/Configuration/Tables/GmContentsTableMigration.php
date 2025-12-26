<?php
/* --------------------------------------------------------------
 GmContentsTableMigration.php 2020-01-16
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
use function array_key_exists;

/**
 * Class GmContentsTableMigration
 * @package Gambio\Core\Configuration\Migration\Tables
 */
class GmContentsTableMigration implements ConfigurationMigration
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
     * @var array
     */
    private $gmContentsCache = [];
    
    
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
     * Migrates data from gm_contents to gx_configurations table.
     */
    public function migrate()
    {
        try {
            $currentData = $this->connection->fetchAll('SELECT * FROM `gm_contents`;');
        } catch (Throwable $e) {
            return;
        }
        
        $mapping     = ConfigurationMigrationMapping::GM_CONTENTS;
        $prefix      = 'gm_configuration/';
        
        foreach ($currentData as $dataSet) {
            $configData = $this->assistant->createGxConfigDataSet($prefix, $mapping, $dataSet);
            $configKey  = $configData['key'];
            $languageId = array_key_exists('language_id', $configData) ? $configData['language_id'] : null;
            
            // remaps meta settings from group id 1 (old) to 9999 (new)
            if ((int)$configData['legacy_group_id'] === 1) {
                $configData['legacy_group_id'] = '9999';
            }
            
            $qb = $this->connection->createQueryBuilder();
            
            $where  = $qb->expr()->andX(
                $qb->expr()->eq($this->connection->quoteIdentifier('key'), $qb->createNamedParameter($configKey)),
                $qb->expr()->isNull('language_id')
            );
            $result = $qb->select('*')->from('gx_configurations')->where($where)->execute();
            
            if ($result->rowCount() > 0) {
                $entry                             = $result->fetch();
                $this->gmContentsCache[$configKey] = [
                    'sort_order'      => $entry['sort_order'],
                    'legacy_group_id' => $entry['legacy_group_id'],
                ];
                
                $identifier = [$this->connection->quoteIdentifier('key') => $configKey, 
                               $this->connection->quoteIdentifier('language_id') => null];
                $this->connection->delete('gx_configurations', $identifier);
            }

            if (array_key_exists($configKey, $this->gmContentsCache)
                && !$this->assistant->gxConfigKeyExists($configKey,
                                                        $languageId)) {
                $qb = $this->connection->createQueryBuilder();
                $configData['sort_order']      = $this->gmContentsCache[$configKey]['sort_order'];
                $configData['legacy_group_id'] = $this->gmContentsCache[$configKey]['legacy_group_id'];
                
                $data = [];
                foreach ($configData as $key => $value) {
                    if ($key === 'key') {
                        $value = $this->truncateString($value);
                    }
                    $data[$this->connection->quoteIdentifier($key)] = $qb->createNamedParameter($value);
                }
                
                $qb->insert('gx_configurations')->values($data)->execute();
            } elseif (!$this->assistant->gxConfigKeyExists($configKey, $languageId)) {
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