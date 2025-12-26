<?php
/* --------------------------------------------------------------
 ConfigurationCompatibilityService.php 2021-04-15
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Compatibility;

use Doctrine\DBAL\Connection;

/**
 * Class ConfigurationCompatibilityService
 * @package Gambio\Core\Configuration\Compatibility
 *          
 * @deprecated This service is deprecated and should not be used to avoid extra database connection.
 *             Use \Gambio\Core\Configuration\Services\ConfigurationService for new domains and gm_get_conf function in 
 *             old code.
 *
 * @codeCoverageIgnore
 */
class ConfigurationCompatibilityService
{
    /**
     * @var array
     */
    private $gmConfigCache = [];
    
    
    /**
     * @var Connection
     */
    private $connection;
    
    
    /**
     * ConfigurationCompatibilityService constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    
    /**
     * @param string|array $key
     * @param string       $type
     * @param bool         $rebuildCache
     *
     * @return string[]|string|false|null
     */
    public function gmGetConf($key, string $type = 'ASSOC', bool $rebuildCache = false)
    {
        if (!$this->isValidKeyAndType($key, $type)) {
            return false;
        }
        if ($rebuildCache || $this->isEmptyGmCache()) {
            $this->buildGmCache();
        }
        if ($this->shouldReturnArray($key)) {
            return $this->buildGmArray($key, $type);
        }
        
        return $this->gmConfigCache[strtoupper($key)] ?? null;
    }
    
    
    /**
     * Builds the internal data array that holds all configuration until the request is finished
     * or a rebuild is requested.
     *
     * @param array  $keys
     * @param string $type
     *
     * @return array
     */
    private function buildGmArray(array $keys, string $type): array
    {
        $values    = [];
        $upperKeys = array_map('strtoupper', $keys);
        
        if ($this->shouldReturnAssoc($type)) {
            foreach ($upperKeys as $key) {
                $values[$key] = $this->gmConfigCache[$key];
            }
            
            return $values;
        }
        foreach ($upperKeys as $key) {
            $values[] = $this->gmConfigCache[$key];
        }
        
        return $values;
    }
    
    
    /**
     * Checks the $key argument. If it is an array, multiple
     * configurations are requested.
     *
     * @param $key
     *
     * @return bool
     */
    private function shouldReturnArray($key): bool
    {
        return is_array($key);
    }
    
    
    /**
     * Checks the $type argument that determines the format of the returned
     * array if multiple configurations are requested.
     *
     * @param string $type
     *
     * @return bool
     */
    private function shouldReturnAssoc(string $type): bool
    {
        return $type === 'ASSOC';
    }
    
    
    /**
     * Validates the $key and $type argument.
     *
     * The argument $key can be either of type string or an array of strings to request multiple configurations.
     * The $type argument is an enum value and expected to be either "NUMERIC" or "ASSOC".
     *
     * @param mixed  $key
     * @param string $type
     *
     * @return bool
     */
    private function isValidKeyAndType($key, string $type): bool
    {
        if (!is_string($key) && !$this->shouldReturnArray($key)) {
            return false;
        }
        
        return $type === 'ASSOC' || $type === 'NUMERIC';
    }
    
    
    /**
     * Rebuilds the cached data of the gm_configuration namespace.
     */
    private function buildGmCache(): void
    {
        $this->gmConfigCache = [];
        $prefix              = 'gm_configuration/';
        $query               = <<<QUERY
SELECT * FROM `gx_configurations` WHERE `key` LIKE "{$prefix}%";
QUERY;
        
        $configurations = $this->connection->fetchAll($query);
        
        foreach ($configurations as $configuration) {
            $key                       = strtoupper(str_replace($prefix, '', $configuration['key']));
            $this->gmConfigCache[$key] = $configuration['value'];
        }
    }
    
    
    /**
     * Checks if the cached data of the gm_configuration namespace is empty.
     *
     * @return bool
     */
    private function isEmptyGmCache(): bool
    {
        return count($this->gmConfigCache) === 0;
    }
}