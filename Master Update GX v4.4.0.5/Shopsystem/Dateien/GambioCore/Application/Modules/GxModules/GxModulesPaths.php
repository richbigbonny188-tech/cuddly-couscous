<?php
/* --------------------------------------------------------------
 GxModulesPaths.php 2020-06-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules\GxModules;

use Gambio\Core\Application\ValueObjects\Path;

/**
 * Class GxModulesPaths
 * @package Gambio\Core\Application\Modules\GxModules
 */
class GxModulesPaths
{
    use Iterators;
    
    /**
     * @var Path
     */
    private $path;
    
    
    /**
     * GxModulesPaths constructor.
     *
     * @param Path $path
     */
    public function __construct(Path $path)
    {
        $this->path = $path;
    }
    
    
    /**
     * Returns all html files of installed GXModules.
     *
     * @return array
     */
    public function htmlFiles(): array
    {
        $files = [];
        
        foreach ($this->modules() as $module) {
            foreach ($this->recursiveDirectoryIterator($module) as $fileInfo) {
                if ($fileInfo->getExtension() === 'html') {
                    $files[] = $fileInfo->getPathname();
                }
            }
        }
        
        return $files;
    }
    
    
    /**
     * Returns all GXModules module paths.
     *
     * @return array
     */
    public function modules(): array
    {
        $modules = [];
        
        foreach ($this->vendors() as $vendor) {
            foreach ($this->directoryIterator($vendor) as $module) {
                if ($module->isDir() && !$module->isDot()) {
                    $modules[] = $module->getPathname();
                }
            }
        }
        
        return $modules;
    }
    
    
    /**
     * Returns all GXModules vendor paths.
     *
     * @return array
     */
    public function vendors(): array
    {
        $gxModulesIterator = $this->directoryIterator("{$this->path->base()}/GXModules");
        $vendors           = [];
        
        foreach ($gxModulesIterator as $vendor) {
            if ($vendor->isDir() && !$vendor->isDot()) {
                $vendors[] = $vendor->getPathname();
            }
        }
        
        return $vendors;
    }
}