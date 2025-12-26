<?php
/* --------------------------------------------------------------
   FileCache.php 2020-04-30
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Cache;

use Gambio\Core\Cache\Events\AddedDataToCache;
use Gambio\Core\Cache\Events\CachedDataExpired;
use Gambio\Core\Cache\Events\CachedDataNotFound;
use Gambio\Core\Cache\Events\ClearedCache;
use Gambio\Core\Cache\Events\RemovedDataFromCache;
use Gambio\Core\Cache\Exceptions\InvalidArgumentException;
use Gambio\Core\Filesystem\Exceptions\FilesystemException;
use Gambio\Core\Filesystem\Interfaces\Filesystem;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Class FileCache
 *
 * @package Gambio\Core\Cache
 */
class FileCache implements CacheInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    
    /**
     * @var Filesystem
     */
    private $filesystem;
    
    /**
     * @var string
     */
    private $namespace;
    
    /**
     * @var CachedData[]
     */
    private $cachedData;
    
    
    /**
     * FileBasedDataCache constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param Filesystem               $filesystem
     * @param string                   $namespace
     */
    private function __construct(EventDispatcherInterface $eventDispatcher, Filesystem $filesystem, string $namespace)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->filesystem      = $filesystem;
        $this->namespace       = $namespace;
        $this->cachedData      = [];
    }
    
    
    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param Filesystem               $filesystem
     * @param string                   $namespace
     *
     * @return FileCache
     *
     * @throws InvalidArgumentException
     */
    public static function create(
        EventDispatcherInterface $eventDispatcher,
        Filesystem $filesystem,
        string $namespace
    ): FileCache {
        if (strlen($namespace) > 64 || preg_match('/^[A-Za-z0-9_\.]+$/', $namespace) !== 1) {
            throw InvalidArgumentException::forNamespace();
        }
        
        return new self($eventDispatcher, $filesystem, $namespace);
    }
    
    
    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        if ($this->validateKey($key) === false) {
            throw InvalidArgumentException::forKey();
        }
        
        if (array_key_exists($key, $this->cachedData) === false) {
            $cache = $this->filesystem->read('cache/' . $this->getCacheFilename($key));
            if ($cache === null) {
                $event = CachedDataNotFound::create($this->namespace, $key, $default);
                $this->eventDispatcher->dispatch($event);
                
                return $event->defaultValue();
            }
            $this->cachedData[$key] = CachedData::createFromJson($cache);
        }
        
        if ($this->cachedData[$key]->isExpired()) {
            $this->delete($key);
            unset($this->cachedData[$key]);
            
            $event = CachedDataExpired::create($this->namespace, $key, $default);
            $this->eventDispatcher->dispatch($event);
            
            return $event->defaultValue();
        }
        
        return $this->cachedData[$key]->cachedValue();
    }
    
    
    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null): bool
    {
        $this->cachedData[$key] = CachedData::create($key, $value, ($ttl === null) ? null : time() + $ttl);
        
        try {
            $this->filesystem->update('cache/' . $this->getCacheFilename($key), (string)$this->cachedData[$key]);
        } catch (FilesystemException $exception) {
            unset($this->cachedData[$key]);
            
            return false;
        }
        
        $event = AddedDataToCache::create($this->namespace, $this->cachedData[$key]);
        $this->eventDispatcher->dispatch($event);
        
        return true;
    }
    
    
    /**
     * @inheritDoc
     */
    public function delete($key): bool
    {
        if ($this->validateKey($key) === false) {
            throw InvalidArgumentException::forKey();
        }
        
        try {
            $this->filesystem->delete('cache/' . $this->getCacheFilename($key));
        } catch (FilesystemException $exception) {
            return false;
        }
        
        $event = RemovedDataFromCache::create($this->namespace, $key);
        unset($this->cachedData[$key]);
        $this->eventDispatcher->dispatch($event);
        
        return true;
    }
    
    
    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $cacheFiles = $this->filesystem->list('cache/');
        foreach ($cacheFiles as $cacheFile) {
            if ($cacheFile->isDirectory() || $cacheFile->extension() !== 'cache'
                || strpos($cacheFile->filename(), $this->namespace . '-') !== 0) {
                continue;
            }
            
            try {
                $this->filesystem->delete($cacheFile->path());
            } catch (FilesystemException $exception) {
                return false;
            }
        }
        
        $event = ClearedCache::create($this->namespace);
        $this->eventDispatcher->dispatch($event);
        
        return true;
    }
    
    
    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        
        return $result;
    }
    
    
    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null): bool
    {
        $success = 1;
        foreach ($values as $key => $value) {
            $result   = $this->set($key, $value, $ttl);
            $success &= $result;
        }
        
        return (bool)$success; // casting to bool because an int could be returned otherwise
    }
    
    
    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys): bool
    {
        $success = 1;
        foreach ($keys as $key) {
            $success &= $this->delete($key);
        }
        
        return (bool)$success; // casting to bool because an int could be returned otherwise
    }
    
    
    /**
     * @inheritDoc
     */
    public function has($key): bool
    {
        if ($this->validateKey($key) === false) {
            throw InvalidArgumentException::forKey();
        }
        
        if (!isset($this->cachedData[$key])) {
            $cache = $this->filesystem->read('cache/' . $this->getCacheFilename($key));
            if ($cache === null) {
                $event = CachedDataNotFound::create($this->namespace, $key, null);
                $this->eventDispatcher->dispatch($event);
                
                return false;
            }
            $this->cachedData[$key] = CachedData::createFromJson($cache);
        }
        
        return $this->cachedData[$key]->isExpired() === false;
    }
    
    
    /**
     * @param string $key
     *
     * @return string
     */
    private function getCacheFilename(string $key): string
    {
        return $this->namespace . '-' . md5($key) . '.cache';
    }
    
    
    /**
     * @param string $key
     *
     * @return bool
     */
    public function validateKey(string $key): bool
    {
        return preg_match('/^[A-Za-z0-9_\.]+$/', $key) === 1 && strlen($key) <= 64;
    }
}