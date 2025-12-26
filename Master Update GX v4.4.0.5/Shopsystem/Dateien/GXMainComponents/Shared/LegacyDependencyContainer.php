<?php
/*------------------------------------------------------------------------------
 LegacyDependencyContainer.php 2021-01-22
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

use Gambio\Core\AdminAccess\AdminAccessServiceProvider;
use Gambio\Admin\Modules\RedirectRules\RedirectRulesServiceProvider;
use Gambio\Core\Application\ValueObjects\Path;
use Gambio\Core\Application\ValueObjects\Server;
use Gambio\Core\Application\ValueObjects\Url;
use Gambio\Core\Configuration\ConfigurationServiceProvider;
use Gambio\Admin\Modules\ParcelService\ParcelServiceServiceProvider;
use Gambio\Admin\Modules\TrackingCode\TrackingCodeServiceProvider;
use Gambio\Admin\Modules\Withdrawal\WithdrawalServiceProvider;
use Gambio\Core\Application\Application;
use Gambio\Core\Application\Modules\ModulesServiceProvider;
use Gambio\Core\Application\ServiceProviders\CiDbServiceProvider;
use Gambio\Core\Application\ServiceProviders\DoctrineQbServiceProvider;
use Gambio\Core\Auth\AuthenticationServiceProvider;
use Gambio\Core\Cache\CacheServiceProvider;
use Gambio\Core\Command\CommandDispatcherServiceProvider;
use Gambio\Core\Event\EventDispatcherServiceProvider;
use Gambio\Core\Filesystem\FilesystemServiceProvider;
use Gambio\Core\Application\Bootstrapper\LoadUserPreferencesFromSession;
use Gambio\Core\Application\DependencyInjection\LeagueContainer;
use Gambio\Core\Images\ImagesServiceProvider;
use Gambio\Core\Language\LanguageServiceProvider;
use Gambio\Core\Language\TextPhrasesServiceProvider;
use Gambio\Core\Logging\LoggingServiceProvider;
use Gambio\Core\TemplateEngine\TemplateEngineServiceProvider;
use Gambio\Shop\UserNavigationHistory\UserNavigationHistoryServiceProvider;
use Gambio\Shop\Product\Description\ServiceProvider as ProductDescriptionServiceProvider;
use Gambio\Shop\Product\Name\ServiceProvider as ProductNameServiceProvider;
use Gambio\Shop\Product\SellingUnitImage\Database\ServiceProvider;
use Gambio\Shop\Product\Url\ServiceProvider as ProductUrlServiceProvider;
use Gambio\Shop\SellingUnit\Database\Unit\SellingUnitServiceProvider;
use Gambio\Shop\SellingUnit\Database\Price\ProductInformation\ServiceProvider as PriceProductInformationServiceProvider;
use Gambio\Shop\Attributes\SellingUnitPrice\ServiceProvider as PriceAttributeInformationServiceProvider;
use Gambio\Shop\Product\Tabs\ServiceProvider as ProductTabsServiceProvider;
use Gambio\Shop\Product\NumberOfOrders\ServiceProvider as ProductNumberOfOrdersServiceProvider;
use Gambio\Shop\Product\LegalAgeFlag\ServiceProvider as ProductLegalAgeFlagServiceProvider;
use Gambio\Shop\Product\AvailabilityDate\ServiceProvider as ProductAvailabilityDateServiceProvider;
use Gambio\Shop\Product\ReleaseDate\ServiceProvider as ProductReleaseDateServiceProvider;
use Gambio\Shop\Product\Status\ServiceProvider as ProductStatusServiceProvider;
use Gambio\Shop\Product\Ean\ServiceProvider as ProductEanServiceProvider;
use Gambio\Shop\Attributes\SellingUnitEan\ServiceProvider as SellingUnitEanAttributesServiceProvider;
use Gambio\Testing\Framework\DoctrineQbTestServiceProvider;

/**
 * Class EventDispatcher
 */
class LegacyDependencyContainer
{
    /**
     * @var Application
     */
    private static $application;
    
    /**
     * @var LeagueContainer
     */
    private static $container;
    
    
    /**
     * @return Application
     */
    public static function getInstance(): Application
    {
        if (!self::$application instanceof Application) {
            if (!class_exists(Application::class)) {
                require_once __DIR__ . '/../../vendor/autoload.php';
            }
    
            self::$container = LeagueContainer::create();
            self::$application = new Application(self::$container);
    
            if (defined('UNIT_TEST_RUNNING')) {
                $serverPath = '/var/www/html';
                $host       = 'www.mein-test-shop.de';
                $webPath    = '';
                $sslEnabled = true;
                $requestUri = 'www.mein-test-shop.de';
        
                self::$application->registerShared(Path::class)->addArgument($serverPath);
                self::$application->registerShared(Url::class)->addArguments([$host, $webPath]);
                self::$application->registerShared(Server::class)->addArguments([$sslEnabled, $requestUri]);
                self::$application->registerProvider(DoctrineQbTestServiceProvider::class);
            } else {
                $host       = HTTP_SERVER;
                $webPath    = rtrim(DIR_WS_CATALOG, '/');
                $serverPath = rtrim(DIR_FS_CATALOG, '/');

                $isSslEnabled = false;
                if (defined('ENABLE_SSL')) {
                    $isSslEnabled = ENABLE_SSL === true;
                } elseif (defined('ENABLE_SSL_CATALOG')) {
                    $isSslEnabled = strtolower(ENABLE_SSL_CATALOG) === 'true';
                }
                
                $requestUri = $_SERVER['REQUEST_URI'];

                self::$application->registerShared(Path::class)->addArgument($serverPath);
                self::$application->registerShared(Url::class)->addArguments([$host, $webPath]);
                self::$application->registerShared(Server::class)->addArguments([$isSslEnabled, $requestUri]);
                self::$application->registerProvider(DoctrineQbServiceProvider::class);
            }
    
            self::$application->registerProvider(LoggingServiceProvider::class);
            self::$application->registerProvider(TextPhrasesServiceProvider::class);
            self::$application->registerProvider(CacheServiceProvider::class);
            self::$application->registerProvider(EventDispatcherServiceProvider::class);
            self::$application->registerProvider(CommandDispatcherServiceProvider::class);
            self::$application->registerProvider(FilesystemServiceProvider::class);
            self::$application->registerProvider(ImagesServiceProvider::class);
            self::$application->registerProvider(AuthenticationServiceProvider::class);
            self::$application->registerProvider(ConfigurationServiceProvider::class);
            self::$application->registerProvider(ParcelServiceServiceProvider::class);
            self::$application->registerProvider(RedirectRulesServiceProvider::class);
            self::$application->registerProvider(TrackingCodeServiceProvider::class);
            self::$application->registerProvider(LanguageServiceProvider::class);
            self::$application->registerProvider(WithdrawalServiceProvider::class);
            self::$application->registerProvider(ModulesServiceProvider::class);
            self::$application->registerProvider(AdminAccessServiceProvider::class);
            self::$application->registerProvider(TemplateEngineServiceProvider::class);
            
            self::$container->registerLeagueProvider(CiDbServiceProvider::class);
    
            self::$container->registerLeagueProvider(ServiceProvider::class);
            
            /**
             * @internal Do not change the order for OnCreateSellingUnitEvent listeners
             */
            self::$container->registerLeagueProvider(\Gambio\Shop\Price\Product\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Product\Product\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(SellingUnitServiceProvider::class);
            self::$container->registerLeagueProvider(PriceProductInformationServiceProvider::class);
            self::$container->registerLeagueProvider(PriceAttributeInformationServiceProvider::class);

            self::$container->registerLeagueProvider(ProductNameServiceProvider::class);
            self::$container->registerLeagueProvider(ProductUrlServiceProvider::class);
            self::$container->registerLeagueProvider(ProductTabsServiceProvider::class);
            self::$container->registerLeagueProvider(ProductNumberOfOrdersServiceProvider::class);
            self::$container->registerLeagueProvider(ProductDescriptionServiceProvider::class);
            self::$container->registerLeagueProvider(ProductLegalAgeFlagServiceProvider::class);
            self::$container->registerLeagueProvider(ProductAvailabilityDateServiceProvider::class);
            self::$container->registerLeagueProvider(ProductReleaseDateServiceProvider::class);
            self::$container->registerLeagueProvider(ProductStatusServiceProvider::class);
            self::$container->registerLeagueProvider(ProductEanServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Properties\SellingUnitImages\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Attributes\SellingUnitImages\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Attributes\ProductModifiers\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Properties\ProductModifiers\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Attributes\SellingUnitQuantitiy\Database\ServiceProvider::class);
    
            self::$container->registerLeagueProvider(\Gambio\Shop\Properties\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Product\SellingUnitQuantitiy\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Properties\SellingUnit\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Product\SellingUnit\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\SellingUnit\Unit\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Attributes\SellingUnit\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\GxCustomizer\SellingUnit\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Product\Model\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Attributes\SellingUnitModel\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Product\Weight\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Attributes\SellingUnitWeight\Database\ServiceProvider::class);
            // DO NOT CHANGE: EAN-Attributes/Properties providers' order unless you want attributes to override properties
            self::$container->registerLeagueProvider(SellingUnitEanAttributesServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Product\ShippingLink\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Product\TaxInfo\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\SellingUnit\Presentation\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Attributes\Representation\Id\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Properties\Representation\Id\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Product\Representation\ProductLink\ServiceProvider::class);
            self::$container->registerLeagueProvider(Gambio\Shop\Attributes\Representation\SelectionHtml\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Properties\Representation\SelectionHtml\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Product\Representation\ShortDescription\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Product\SellingUnitVpe\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Stock\SellingUnitStock\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\SellingUnit\Database\Image\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\GxCustomizer\Representation\Id\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\GxCustomizer\ProductModifiers\Database\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Product\AdditionalPriceInformation\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\Product\DiscountAllowed\ServiceProvider::class);
            self::$container->registerLeagueProvider(\Gambio\Shop\ProductModifiers\ProductModifiersServiceProvider::class);
            self::$container->registerLeagueProvider(UserNavigationHistoryServiceProvider::class);
            
            if (!defined('UNIT_TEST_RUNNING')) {
                // LoadSessionData have to be booted after the configuration repository was registered
                $loadSessionData = new LoadUserPreferencesFromSession();
                $loadSessionData->boot(self::$application);
            }
        }
        
        return self::$application;
    }
}
