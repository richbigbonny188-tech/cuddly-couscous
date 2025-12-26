<?php
/* --------------------------------------------------------------
 GxModulesCache.php 2020-06-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules\GxModules;

use Gambio\Core\Cache\CacheFactory;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class GxModulesCache
 * @package Gambio\Core\Application\Modules\GxModules
 */
class GxModulesCache
{
    private const CACHE_NAMESPACE                = 'gx_modules';
    private const CACHE_KEY_INSTALLED_HTML_FILES = 'installed_html_files';
    private const CACHE_KEY_HTML_FILES           = 'html_files';
    
    /**
     * @var CacheInterface
     */
    private $cache;
    
    /**
     * @var InstalledGxModulesPaths
     */
    private $installedModulesPaths;
    
    /**
     * @var GxModulesPaths
     */
    private $modulesPaths;
    
    
    /**
     * GxModulesCache constructor.
     *
     * @param CacheFactory            $cacheFactory
     * @param InstalledGxModulesPaths $installedModulesPath
     * @param GxModulesPaths          $modulesPath
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        CacheFactory $cacheFactory,
        InstalledGxModulesPaths $installedModulesPath,
        GxModulesPaths $modulesPath
    ) {
        $this->cache                 = $cacheFactory->createCacheFor(static::CACHE_NAMESPACE);
        $this->installedModulesPaths = $installedModulesPath;
        $this->modulesPaths          = $modulesPath;
    }
    
    
    /**
     * Returns a list of GXModules html files.
     *
     * @return array
     */
    public function getHtmlFiles(): array
    {
        $cacheKey = static::CACHE_KEY_HTML_FILES;
        if ($this->hasCacheFor($cacheKey)) {
            return $this->getCacheFor($cacheKey);
        }
        
        $files = $this->modulesPaths->htmlFiles();
        $this->setCacheFor($cacheKey, $files);
        
        return $files;
    }
    
    
    /**
     * Returns a list of installed GXModules html files.
     *
     * @return array
     */
    public function getInstalledHtmlFiles(): array
    {
        $cacheKey = static::CACHE_KEY_INSTALLED_HTML_FILES;
        if ($this->hasCacheFor($cacheKey)) {
            return $this->getCacheFor($cacheKey);
        }
        
        $files = $this->installedModulesPaths->installedHtmlFiles();
        $this->setCacheFor($cacheKey, $files);
        
        return $files;
    }
    
    
    /**
     * Utility method to safely call the cache::get method.
     *
     * @param string $key
     *
     * @return array
     */
    private function getCacheFor(string $key): array
    {
        try {
            return $this->cache->get($key);
        } catch (InvalidArgumentException $e) {
            return [];
        }
    }
    
    
    /**
     * Utility method to safely call the cache::set method.
     *
     * @param string $key
     * @param        $value
     */
    private function setCacheFor(string $key, $value): void
    {
        try {
            $this->cache->set($key, $value);
        } catch (InvalidArgumentException $e) {
        }
    }
    
    
    /**
     * Utility method to safely call the cache::has method.
     *
     * @param string $key
     *
     * @return bool
     */
    private function hasCacheFor(string $key): bool
    {
        try {
            return $this->cache->has($key);
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}