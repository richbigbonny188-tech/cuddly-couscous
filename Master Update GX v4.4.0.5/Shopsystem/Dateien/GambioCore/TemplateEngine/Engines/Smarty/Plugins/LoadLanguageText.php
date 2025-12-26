<?php
/* --------------------------------------------------------------
 LoadLanguageText.php 2020-09-03
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\TemplateEngine\Engines\Smarty\Plugins;

use Closure;
use Gambio\Core\Application\ValueObjects\UserPreferences;
use Gambio\Core\Language\TextManager;
use Smarty_Internal_Template;

/**
 * Class LoadLanguageText
 * @package Gambio\Core\TemplateEngine\Engines\Smarty\Plugins
 * @codeCoverageIgnore
 */
class LoadLanguageText
{
    /**
     * @var TextManager
     */
    private $textManager;
    
    /**
     * @var UserPreferences
     */
    private $userPreferences;
    
    
    /**
     * LoadLanguageText constructor.
     *
     * @param TextManager     $textManager
     * @param UserPreferences $userPreferences
     */
    public function __construct(TextManager $textManager, UserPreferences $userPreferences)
    {
        $this->textManager     = $textManager;
        $this->userPreferences = $userPreferences;
    }
    
    
    /**
     * Load language text smarty callback function.
     *
     * @return Closure
     */
    public function callback(): callable
    {
        return function (array $params, Smarty_Internal_Template $smarty) {
            $section = $params['section'] ?? '';
            $name    = $params['name'] ?? 'txt';
            
            $sectionArray = $this->textManager->getSectionPhrases($section, $this->userPreferences->languageId());
            $smarty->assign($name, $sectionArray);
        };
    }
}