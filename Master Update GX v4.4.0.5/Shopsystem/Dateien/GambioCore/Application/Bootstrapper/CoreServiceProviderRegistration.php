<?php
/* --------------------------------------------------------------
 CoreServiceProviderRegistration.php 2020-10-05
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Bootstrapper;

use Gambio\Core\AdminAccess\AdminAccessServiceProvider;
use Gambio\Core\Application\Contracts\Application;
use Gambio\Core\Application\Contracts\Bootstrapper;
use Gambio\Core\Application\Modules\ModuleServiceProvider;
use Gambio\Core\Application\Modules\ModulesServiceProvider;
use Gambio\Core\Application\ServiceProviders\DoctrineQbServiceProvider;
use Gambio\Core\Auth\AuthenticationServiceProvider;
use Gambio\Core\Cache\CacheServiceProvider;
use Gambio\Core\Configuration\ConfigurationServiceProvider;
use Gambio\Core\Filesystem\FilesystemServiceProvider;
use Gambio\Core\Images\ImagesServiceProvider;
use Gambio\Core\Language\LanguageServiceProvider;
use Gambio\Core\Language\TextPhrasesServiceProvider;
use Gambio\Core\Logging\LoggingServiceProvider;
use Gambio\Core\TemplateEngine\TemplateEngineServiceProvider;

/**
 * Class CoreServiceProviderRegistration
 * @package Gambio\Core\Application\Bootstrapper
 *
 * Here, we register all service providers off the shop's core components.
 */
class CoreServiceProviderRegistration implements Bootstrapper
{
    private const SERVICE_PROVIDERS = [
        LoggingServiceProvider::class,
        AuthenticationServiceProvider::class,
        CacheServiceProvider::class,
        DoctrineQbServiceProvider::class,
        FilesystemServiceProvider::class,
        ImagesServiceProvider::class,
        LanguageServiceProvider::class,
        TextPhrasesServiceProvider::class,
        ModulesServiceProvider::class,
        ConfigurationServiceProvider::class,
        TemplateEngineServiceProvider::class,
        ModuleServiceProvider::class,
        AdminAccessServiceProvider::class,
    ];
    
    
    /**
     * @inheritDoc
     */
    public function boot(Application $application): void
    {
        foreach (self::SERVICE_PROVIDERS as $provider) {
            $application->registerProvider($provider);
        }
    }
}