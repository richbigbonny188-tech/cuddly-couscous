<?php
/*------------------------------------------------------------------------------
 RoutineLocker.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

class RoutineLocker implements RoutineLockerInterface
{
    /**
     * @var string
     */
    protected $lockerFilePath;
    
    /**
     * @var resource
     */
    protected $lockResource;
    
    
    /**
     * @inheritDoc
     */
    public function acquireLock(): void
    {
        $this->lockResource = @fopen($this->lockerFilePath, 'w+');
        if (!$this->lockResource) {
            throw  new RoutineLockerException("It wasn't possible to create the lock file {$this->lockerFilePath}");
        }
        
        if (!flock($this->lockResource, LOCK_EX | LOCK_NB, $wouldBlock)) {
            if ($wouldBlock) {
                throw new RoutineLockedByAnotherInstanceException("File already locked {$this->lockerFilePath}");
            } else {
                throw new RoutineLockerException("It wasn't possible to acquire lock of the file {$this->lockerFilePath}");
            }
        }
    }
    
    
    /**
     * @return mixed|void
     */
    public function releaseLock(): void
    {
        if ($this->lockResource) {
            fclose($this->lockResource);
            @unlink($this->lockerFilePath);
            $this->lockResource = false;
        }
    }
    
    
    /**
     * RoutineLocker constructor.
     *
     * @param ExistingDirectory $directory
     * @param string            $lockName
     */
    public function __construct(ExistingDirectory $directory, string $lockName)
    {
        $this->lockerFilePath = $directory->getDirPath() . DIRECTORY_SEPARATOR . "cache/$lockName.lock";
    }
    
    
    protected function canLock(): bool
    {
        try {
            $this->acquireLock();
        } catch (RoutineLockedByAnotherInstanceException $e) {
            return false;
        } catch (RoutineLockerException $e) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @inheritDoc
     */
    public function waitUntilLockIsReleasedOrTimeout($timeout): void
    {
        $start = microtime(true);
        while (!$this->canLock() && (microtime(true) - $start) < $timeout) {
            sleep(1);
        }
    }
}