<?php
/* --------------------------------------------------------------
 GetUserMod.php 2020-09-03
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\TemplateEngine\Engines\Smarty\Plugins;

use Gambio\Core\Application\Modules\GxModules\GxModulesCache;
use Gambio\Core\Application\ValueObjects\Path;
use Gambio\Core\TemplateEngine\Exceptions\TemplateNotFoundException;
use Smarty_Resource_Custom;

/**
 * Class GetUserMod
 * @package Gambio\Admin\Layout\LayoutLoader\Plugins
 * @codeCoverageIgnore
 */
class GetUserMod extends Smarty_Resource_Custom
{
    /**
     * @var GxModulesCache
     */
    private $gxModulesCache;
    
    /**
     * @var Path
     */
    private $path;
    
    
    /**
     * GetUserMod constructor.
     *
     * @param GxModulesCache $gxModulesCache
     * @param Path           $path
     */
    public function __construct(GxModulesCache $gxModulesCache, Path $path)
    {
        $this->gxModulesCache = $gxModulesCache;
        $this->path           = $path;
    }
    
    
    /**
     * Fetches the template source.
     *
     * @param string $name
     * @param string $source
     * @param int    $mtime
     *
     * @throws TemplateNotFoundException
     */
    protected function fetch($name, &$source, &$mtime): void
    {
        if ($this->isExtending($name)) {
            $this->handleExtend($name, $source, $mtime);
            
            return;
        }
        
        $filename  = $this->evaluateFilename($name);
        $source    = file_get_contents($filename);
        $fileMTime = filemtime($filename);
        $mtime     = $fileMTime === false ? 0 : $fileMTime;
    }
    
    
    /**
     * Evaluates the template filename.
     *
     * @param string $name
     *
     * @return string
     * @throws TemplateNotFoundException
     */
    private function evaluateFilename(string $name): string
    {
        if ($this->isAbsolutePath($name) && file_exists($name)) {
            return $name;
        }
        
        $adminPath = "{$this->path->admin()}/html/content/{$name}";
        if (file_exists($adminPath)) {
            return $adminPath;
        }
        
        $gxModulesPath = "{$this->path->base()}/GXModules/{$name}";
        if ($this->isGxModulesHtml($gxModulesPath) && file_exists($gxModulesPath)) {
            return $gxModulesPath;
        }
        
        throw new TemplateNotFoundException("Template not found:\n{$name}");
    }
    
    
    /**
     * Checks if $path is a valid GXModules HTML file.
     *
     * @param string $path
     *
     * @return bool
     */
    private function isGxModulesHtml(string $path): bool
    {
        $htmlFiles = $this->gxModulesCache->getInstalledHtmlFiles();
        
        return in_array($path, $htmlFiles, true);
    }
    
    
    /**
     * Checks if $path is an absolute shop path.
     *
     * @param string $path
     *
     * @return bool
     */
    private function isAbsolutePath(string $path): bool
    {
        return strpos($path, $this->path->base()) === 0;
    }
    
    
    /**
     * Handles the extend block.
     *
     * @param $name
     * @param $source
     * @param $mtime
     */
    private function handleExtend($name, &$source, &$mtime): void
    {
        $filename  = str_replace('extends:', '', $name);
        $filenames = explode('|', $filename);
        $source    = '{extends file="' . $filenames[0] . '"}';
        $mtime     = 0;
        unset($filenames[0]);
        foreach ($filenames as $filename) {
            $source           .= file_get_contents($filename);
            $modificationTime = filemtime($filename);
            
            if ($mtime < $modificationTime) {
                $mtime = $modificationTime;
            }
        }
    }
    
    
    /**
     * Checks if $name is an extends block.
     *
     * @param string $name
     *
     * @return bool
     */
    private function isExtending(string $name): bool
    {
        return stripos($name, 'extends:') === 0;
    }
}