<?php
/* --------------------------------------------------------------
 SafeCache.php 2020-09-17
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Cache;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Trait SafeCache
 * @package Gambio\Admin\Application\Registry\ModuleRegistry\App\Data
 */
trait SafeCache
{
    /**
     * @var CacheInterface
     */
    private $cache;
    
    
    /**
     * Utility method to safely get a cache value of the given key.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    private function safeGet(string $key)
    {
        try {
            return $this->cache->get($key);
        } catch (InvalidArgumentException $e) {
        }
        
        return null;
    }
    
    
    /**
     * Utility method to safely set the cache value of the given key.
     *
     * @param string $key
     * @param        $value
     */
    private function safeSet(string $key, $value): void
    {
        try {
            $this->cache->set($key, $value);
        } catch (InvalidArgumentException $e) {
        }
    }
    
    
    /**
     * Utility method to safely deletes a cache value of the given key.
     *
     * @param string $key
     */
    private function safeDelete(string $key): void
    {
        try {
            $this->cache->delete($key);
        } catch (InvalidArgumentException $e) {
        }
    }
    
    
    /**
     * Utility method to safely check if the cache has a value for the given key.
     *
     * @param string $key
     *
     * @return bool
     */
    private function safeHas(string $key): bool
    {
        if ($this->cache) {
            try {
                return $this->cache->has($key);
            } catch (InvalidArgumentException $e) {
            }
        }
        
        return false;
    }
}