<?php
/* --------------------------------------------------------------
 TemplateEngineServiceProvider.php 2020-09-03
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\TemplateEngine;

use Gambio\Core\Application\Modules\GxModules\GxModulesCache;
use Gambio\Core\Application\ValueObjects\Path;
use Gambio\Core\Application\ValueObjects\UserPreferences;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;
use Gambio\Core\Language\TextManager;
use Gambio\Core\TemplateEngine\Collection\LayoutDataCollection;
use Gambio\Core\TemplateEngine\Engines\Smarty\Plugins\GetUserMod;
use Gambio\Core\TemplateEngine\Engines\Smarty\Plugins\LoadLanguageText;
use Gambio\Core\TemplateEngine\Engines\Smarty\SmartyConfiguration;
use Gambio\Core\TemplateEngine\Engines\Smarty\SmartyEngine;
use Gambio\Core\TemplateEngine\Engines\Smarty\TemplateExtender;
use Smarty;

/**
 * Class TemplateEngineServiceProvider
 * @package Gambio\Core\TemplateEngine
 */
class TemplateEngineServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            SmartyEngine::class,
            LayoutData::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->registerShared(LayoutData::class, LayoutDataCollection::class);
        $this->application->registerShared(SmartyEngine::class)->addArguments(
            [
                Smarty::class,
                TemplateExtender::class,
                SmartyConfiguration::class,
            ]
        );
        $this->application->registerShared(Smarty::class);
        $this->application->registerShared(TemplateExtender::class)->addArgument(GxModulesCache::class);
        $this->application->registerShared(SmartyConfiguration::class)->addArguments(
            [GetUserMod::class, LoadLanguageText::class, Path::class]
        );
        $this->application->registerShared(GetUserMod::class)->addArguments([GxModulesCache::class, Path::class]);
        $this->application->registerShared(LoadLanguageText::class)->addArguments(
            [TextManager::class, UserPreferences::class]
        );
    }
}