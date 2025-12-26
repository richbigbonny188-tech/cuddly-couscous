<?php
/* --------------------------------------------------------------
 GoogleModule.php 2020-04-16
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

namespace GXModules\Gambio\Google\Admin\Module;

use Gambio\Core\Application\Modules\AbstractModule;
use Gambio\Core\TemplateEngine\Commands\LoadAdminLayout;

/**
 * Class GoogleModule
 * @package GXModules\Gambio\Google
 */
class GoogleModule extends AbstractModule
{
    /**
     * @inheritDoc
     */
    public function commandHandlers(): ?array
    {
        return [
            LoadAdminLayout::class => [
                GoogleLoadLayoutDataHandler::class,
            ]
        ];
    }
}