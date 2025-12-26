<?php
/* --------------------------------------------------------------
 TemplateExtender.php 2020-07-10
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\TemplateEngine\Engines\Smarty;

use Gambio\Core\Application\Modules\GxModules\GxModulesCache;

/**
 * Class TemplateExtender
 * @package            Gambio\Core\TemplateEngine\Engines\Smarty
 * @codeCoverageIgnore Will be refactored with Gambio\Core\Modules\GxModules namespace together.
 */
class TemplateExtender
{
    /**
     * @var GxModulesCache
     */
    private $gxModulesCache;
    
    
    /**
     * TemplateExtender constructor.
     *
     * @param GxModulesCache $gxModulesCache
     */
    public function __construct(GxModulesCache $gxModulesCache)
    {
        $this->gxModulesCache = $gxModulesCache;
    }
    
    
    /**
     * Extends the template with valid GxModules.
     *
     * @param string $template
     *
     * @return string
     */
    public function extend(string $template): string
    {
        $template             = realpath($template);
        $templates            = ["extends:{$template}"];
        $relativeTemplatePath = preg_replace('/.*\/GambioAdmin\/Modules\/(.*\/ui\/.*)/i', '$1', $template);
        
        foreach ($this->gxModulesCache->getInstalledHtmlFiles() as $htmlFile) {
            if ($this->isTemplate($template, $htmlFile)) {
                continue;
            }
            if ($this->isExtendingLayout($htmlFile) || $this->isExtendingPage($relativeTemplatePath, $htmlFile)) {
                $templates[] = $htmlFile;
            }
        }
        
        return implode('|', $templates);
    }
    
    
    /**
     * Simple check for equality of both arguments.
     *
     * @param string $template
     * @param string $htmlFile
     *
     * @return bool
     */
    private function isTemplate(string $template, string $htmlFile): bool
    {
        return $template === $htmlFile;
    }
    
    
    /**
     * Checks if $htmlFile should extend the whole layout.
     *
     * Any GxModules html file located in GxModules/Vendor/Module/Admin/Html/layout/ will be used
     * to extend the layout.
     *
     * @param string $htmlFile
     *
     * @return bool
     */
    private function isExtendingLayout(string $htmlFile): bool
    {
        return stripos($htmlFile, '/admin/html/layout/') !== false;
    }
    
    
    /**
     * Checks if $htmlFile should extend the page.
     *
     * So when the $htmlFile contains $relativeFilePath (case insensitive), the current page should be extended.
     *
     * Example:
     * $htmlFile = /path/shop/GxModules/Vendor/Module/Admin/Html/configuration/ui/configuration_page.html
     * $relativeFilePath = Configuration/UI/configuration_page.html
     *
     * The GxModules $htmlFile should extend the current page in this case.
     *
     * @param string $relativeTemplatePath
     * @param string $htmlFile
     *
     * @return bool
     */
    private function isExtendingPage(string $relativeTemplatePath, string $htmlFile): bool
    {
        return stripos($htmlFile, $relativeTemplatePath) !== false;
    }
}