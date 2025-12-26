<?php
/* --------------------------------------------------------------
 SmartyConfiguration.php 2020-12-22
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\TemplateEngine\Engines\Smarty;

use Gambio\Core\Application\ValueObjects\Path;
use Gambio\Core\TemplateEngine\Engines\Smarty\Plugins\GetUserMod;
use Gambio\Core\TemplateEngine\Engines\Smarty\Plugins\LoadLanguageText;
use Smarty;
use SmartyException;

/**
 * Class SmartyConfiguration
 * @package Gambio\Core\TemplateEngine\Engines\Smarty
 */
class SmartyConfiguration
{
    /**
     * @var GetUserMod
     */
    private $getUserMod;
    
    /**
     * @var LoadLanguageText
     */
    private $loadLanguageText;
    
    /**
     * @var Path
     */
    private $path;
    
    
    /**
     * SmartyConfiguration constructor.
     *
     * @param GetUserMod       $getUserMod
     * @param LoadLanguageText $loadLanguageText
     * @param Path             $path
     */
    public function __construct(GetUserMod $getUserMod, LoadLanguageText $loadLanguageText, Path $path)
    {
        $this->getUserMod       = $getUserMod;
        $this->loadLanguageText = $loadLanguageText;
        $this->path             = $path;
    }
    
    
    public function load(Smarty $smarty): void
    {
        try {
            $templateDir = "{$this->path->base()}/GambioAdmin/Layout/ui/template";
            
            $smarty->setTemplateDir([$templateDir]);
            $smarty->setCompileDir("{$this->path->base()}/cache/smarty/");
            $smarty->registerResource('get_usermod', $this->getUserMod);
            $smarty->registerPlugin('function', 'load_language_text', $this->loadLanguageText->callback());
        } catch (SmartyException $e) {
        }
    }
}