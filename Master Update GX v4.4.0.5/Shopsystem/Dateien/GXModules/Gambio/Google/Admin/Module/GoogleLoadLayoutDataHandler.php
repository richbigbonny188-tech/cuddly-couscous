<?php
/* --------------------------------------------------------------
 GoogleLoadLayoutDataHandler.php 2020-04-16
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

namespace GXModules\Gambio\Google\Admin\Module;

use Gambio\Core\TemplateEngine\Commands\LoadAdminLayout;

/**
 * Class GoogleLoadLayoutDataHandler
 * @package GXModules\Gambio\Google\Admin
 */
class GoogleLoadLayoutDataHandler
{
    /**
     * @var GoogleFooterBadgeLoader
     */
    private $footerBadgeLoader;
    
    /**
     * @var GoogleTranslationsLoader
     */
    private $translationsLoader;
    
    
    /**
     * GoogleLoadLayoutDataHandler constructor.
     *
     * @param GoogleFooterBadgeLoader  $footerBadgeLoader
     * @param GoogleTranslationsLoader $translationsLoader
     */
    public function __construct(
        GoogleFooterBadgeLoader $footerBadgeLoader,
        GoogleTranslationsLoader $translationsLoader
    ) {
        $this->footerBadgeLoader  = $footerBadgeLoader;
        $this->translationsLoader = $translationsLoader;
    }
    
    
    /**
     * Adds the google footer badge loader.
     *
     * @param LoadAdminLayout $command
     *
     * @return LoadAdminLayout
     */
    public function __invoke(LoadAdminLayout $command): LoadAdminLayout
    {
        $command->addLoader($this->footerBadgeLoader);
        $command->addLoader($this->translationsLoader);
        
        return $command;
    }
}