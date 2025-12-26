<?php
/* --------------------------------------------------------------
 GoogleServiceProvider.php 2020-04-16
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

namespace GXModules\Gambio\Google\Admin\Module;

use Doctrine\DBAL\Connection;
use Gambio\Core\Application\Modules\Abstraction\AbstractModuleServiceProvider;
use Gambio\Core\Language\TextManager;

/**
 * Class GoogleServiceProvider
 * @package GXModules\Gambio\Google\Admin
 */
class GoogleServiceProvider extends AbstractModuleServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [GoogleLoadLayoutDataHandler::class];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->share(GoogleLoadLayoutDataHandler::class)->addArguments(
            [GoogleFooterBadgeLoader::class, GoogleTranslationsLoader::class]
        );
        $this->application->share(GoogleFooterBadgeLoader::class)->addArgument(Connection::class);
        $this->application->share(GoogleTranslationsLoader::class)->addArgument(TextManager::class);
    }
}