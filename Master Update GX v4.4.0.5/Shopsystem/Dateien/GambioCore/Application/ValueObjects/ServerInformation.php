<?php
/* --------------------------------------------------------------
 ServerInformation.php 2020-05-04
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\ValueObjects;

/**
 * Class ServerInformation
 * @package Gambio\Core\Application\ValueObjects
 */
class ServerInformation
{
    /**
     * @var bool|null
     */
    private $modRewriteWorking;
    
    /**
     * @var string|null
     */
    private $htaccessVersion;
    
    
    /**
     * ServerInformation constructor.
     *
     * @param string|null $modRewriteWorking
     * @param string|null $htaccessVersion
     */
    public function __construct(?string $modRewriteWorking, ?string $htaccessVersion)
    {
        if ($modRewriteWorking) {
            $this->modRewriteWorking = (bool)$modRewriteWorking;
        }
        $this->htaccessVersion = $htaccessVersion;
    }
    
    
    /**
     * @return bool
     */
    public function modRewriteAvailable(): bool
    {
        return null !== $this->modRewriteWorking;
    }
    
    
    /**
     * @return bool
     */
    public function modRewriteWorking(): bool
    {
        return $this->modRewriteWorking ?? false;
    }
    
    
    /**
     * @return bool
     */
    public function htaccessVersionAvailable(): bool
    {
        return null !== $this->htaccessVersion;
    }
    
    
    /**
     * @param string $version
     *
     * @return bool
     */
    public function htaccessVersionGreaterEquals(string $version): bool
    {
        if ($this->htaccessVersion) {
            return version_compare($this->htaccessVersion, $version) >= 0;
        }
        
        return false;
    }
}