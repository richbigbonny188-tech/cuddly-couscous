<?php
/* --------------------------------------------------------------
 ConfigurationMigrationAssistant.php 2020-01-16
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 16 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Migration;

use Doctrine\DBAL\Connection;

/**
 * Class ConfigurationMigrationAssistant
 * @package Gambio\Core\Configuration\Migration
 */
class ConfigurationMigrationAssistant
{
    /**
     * @var Connection
     */
    private $connection;
    
    
    /**
     * ConfigurationMigrationAssistant constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    
    /**
     * Remapping $dataSet with $mapping and applies $prefix to the config key.
     *
     * @param string $prefix
     * @param array  $mapping
     * @param array  $dataSet
     *
     * @return array
     */
    public function createGxConfigDataSet(string $prefix, array $mapping, array $dataSet): array
    {
        $configData = [];
        foreach ($mapping as $configurationKey => $gxConfigurationsKey) {
            if ($gxConfigurationsKey === 'key') {
                $configData[$gxConfigurationsKey] = "{$prefix}{$dataSet[$configurationKey]}";
            } else {
                if (($gxConfigurationsKey === 'sort_order' && $dataSet[$configurationKey] !== '0')
                    || $gxConfigurationsKey !== 'sort_order') {
                    if (array_key_exists($configurationKey, $dataSet)) {
                        $configData[$gxConfigurationsKey] = $dataSet[$configurationKey];
                    }
                }
            }
        }
        
        return $configData;
    }
    
    
    /**
     * Checks if configuration already exists in gx_configurations table.
     *
     * @param string      $key
     * @param string|null $languageId
     *
     * @return bool
     */
    public function gxConfigKeyExists(string $key, ?string $languageId): bool
    {
        $qb = $this->connection->createQueryBuilder();
        
        $keyWhere  = $qb->expr()->eq($this->connection->quoteIdentifier('key'), $qb->createNamedParameter($key));
        $langWhere = $languageId ? $qb->expr()->eq('language_id', $qb->createNamedParameter($languageId)) : $qb->expr()
                                                                                                               ->isNull(
                                                                                                                   'language_id'
                                                                                                               );
        
        $where = $qb->expr()->andX($keyWhere, $langWhere);
        
        return $qb->select('*')->from('gx_configurations')->where($where)->execute()->rowCount() > 0;
    }
}