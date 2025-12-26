<?php
/* --------------------------------------------------------------
 InstalledGxModulesPaths.php 2020-10-08
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules\GxModules;

use Gambio\Core\Configuration\ConfigurationFinder;
use SplFileInfo;

/**
 * Class InstalledGxModulesPaths
 * @package Gambio\Core\Application\Modules\GxModules
 */
class InstalledGxModulesPaths
{
    use Iterators;
    
    private const ALLOWED_DIRECTORIES = [
        '/Classes/',
        '/Shop/',
        '/Admin/',
        '/StyleEdit/',
        '/Build/',
        '/TextPhrases/'
    ];
    
    /**
     * @var ConfigurationFinder
     */
    private $configurationFinder;
    
    /**
     * @var GxModulesPaths
     */
    private $gxModulesPaths;
    
    
    /**
     * GxModulesPaths constructor.
     *
     * @param ConfigurationFinder $configurationFinder
     * @param GxModulesPaths      $gxModulesPaths
     */
    public function __construct(ConfigurationFinder $configurationFinder, GxModulesPaths $gxModulesPaths)
    {
        $this->configurationFinder = $configurationFinder;
        $this->gxModulesPaths      = $gxModulesPaths;
    }
    
    
    /**
     * Returns all html files of installed GXModules.
     *
     * @return array
     */
    public function installedHtmlFiles(): array
    {
        $files = [];
        
        foreach ($this->modules() as $module) {
            foreach ($this->recursiveDirectoryIterator($module) as $fileInfo) {
                $filepath       = str_replace('\\', '/', $fileInfo->getPathname());
                $isGxModuleHtml = $fileInfo->getExtension() === 'html'
                                  && $this->strContains('/Admin/Html', $filepath);
                
                if ($isGxModuleHtml && $this->isAllowedFile($fileInfo)) {
                    $files[] = $filepath;
                }
            }
        }
        
        return $files;
    }
    
    
    /**
     * Returns a list of installed GXModules.
     *
     * GXModules are marked as installed by default.
     * In case of an existent GXModule.json configuration, the module will be verified by checking
     * the "forceIncludingFiles" configuration option or active flag in the configuration table.
     *
     * @return array
     */
    public function modules(): array
    {
        $modules = [];
        foreach ($this->gxModulesPaths->modules() as $module) {
            $isActive   = true;
            $configFile = "{$module}/GXModule.json";
            
            if (is_file($configFile)) {
                $config = json_decode(file_get_contents($configFile), true);
                if ($config['forceIncludingFiles'] ?? false) {
                    $modules[] = $module;
                    continue;
                }
                
                $modulePathArray = explode('/', $module);
                $moduleName      = array_pop($modulePathArray);
                $vendor          = array_pop($modulePathArray);
                
                $namespace   = "modules/{$vendor}${moduleName}";
                $configValue = $this->configurationFinder->get("{$namespace}/active", '0');
                
                $isActive = $configValue === '1' || strtolower($configValue) === 'true';
            }
            
            if ($isActive) {
                $modules[] = $module;
            }
        }
        
        return $modules;
    }
    
    
    /**
     * Checks if the file is a valid GXModules file.
     *
     * @param SplFileInfo $fileInfo
     *
     * @return bool
     */
    private function isAllowedFile(SplFileInfo $fileInfo): bool
    {
        if ($fileInfo->isDir()) {
            return false;
        }
        
        if (stripos($fileInfo->getFilename(), 'GXModules.json') !== false) {
            return true;
        }
        
        if ($this->isInAllowedDirectory($fileInfo)) {
            return true;
        }
        
        return false;
    }
    
    
    /**
     * Checks if the file is located in an allowed directory.
     *
     * @param SplFileInfo $fileInfo
     *
     * @return bool
     */
    private function isInAllowedDirectory(SplFileInfo $fileInfo): bool
    {
        foreach (static::ALLOWED_DIRECTORIES as $allowedDirectory) {
            if (stripos(str_replace('\\', '/', $fileInfo->getPathname()), $allowedDirectory) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    
    /**
     * Checks if $subject contains $value.
     *
     * @param string $value
     * @param string $subject
     *
     * @return bool
     */
    private function strContains(string $value, string $subject): bool
    {
        return stripos($subject, $value) !== false;
    }
}