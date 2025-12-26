<?php
/*--------------------------------------------------------------------------------------------------
    SingletonPrototype.php 2021-02-17
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core;

use FilesystemAdapter;
use Gambio\StyleEdit\Api\Controllers\ConfigurationController;
use Gambio\StyleEdit\Api\Controllers\DefaultController;
use Gambio\StyleEdit\Api\Controllers\ExportController;
use Gambio\StyleEdit\Api\Controllers\JwtController;
use Gambio\StyleEdit\Api\Controllers\ThemeExtensionController;
use Gambio\StyleEdit\Core\BuildStrategies\ClassBuilder;
use Gambio\StyleEdit\Core\Components\BackgroundGradientGroup\Entities\BackgroundGradientGroupOption;
use Gambio\StyleEdit\Core\Components\BackgroundGroup\Commands\BackgroundSaveCommand;
use Gambio\StyleEdit\Core\Components\BackgroundGroup\Entities\BackgroundGroupOption;
use Gambio\StyleEdit\Core\Components\BackgroundImageGroup\Entities\BackgroundImageGroupOption;
use Gambio\StyleEdit\Core\Components\BorderGroup\Entities\BorderGroupOption;
use Gambio\StyleEdit\Core\Components\ButtonImage\ButtonImageController;
use Gambio\StyleEdit\Core\Components\CategorySearchBox\Entities\CategorySearchBoxOption;
use Gambio\StyleEdit\Core\Components\Checkbox\Entities\CheckboxOption;
use Gambio\StyleEdit\Core\Components\Code\Entities\SourcecodeOption;
use Gambio\StyleEdit\Core\Components\CodeEditor\Entities\CodeEditorOption;
use Gambio\StyleEdit\Core\Components\ColorPicker\Entities\ColorPickerOption;
use Gambio\StyleEdit\Core\Components\ContentZone\Commands\ContentZoneSaveCommand;
use Gambio\StyleEdit\Core\Components\ContentZone\Entities\ContentZoneOption;
use Gambio\StyleEdit\Core\Components\ContentZone\Services\ContentZoneService;
use Gambio\StyleEdit\Core\Components\DropdownSelect\Entities\DropdownSelectOption;
use Gambio\StyleEdit\Core\Components\FontFamily\Entities\FontFamilyOption;
use Gambio\StyleEdit\Core\Components\FontGroup\Entities\FontGroupOption;
use Gambio\StyleEdit\Core\Components\GxLogo\Commands\GxLogoSaveCommand;
use Gambio\StyleEdit\Core\Components\GxLogo\Entities\GxLogoOption;
use Gambio\StyleEdit\Core\Components\ImageUpload\Entities\ImageUploadOption;
use Gambio\StyleEdit\Core\Components\MarginGroup\Entities\MarginGroupOption;
use Gambio\StyleEdit\Core\Components\MeasureValue\Entities\MeasureValueOption;
use Gambio\StyleEdit\Core\Components\NumberBox\Entities\NumberBoxOption;
use Gambio\StyleEdit\Core\Components\Option\Commands\GroupOptionSaveCommand;
use Gambio\StyleEdit\Core\Components\Option\Commands\OptionSaveCommand;
use Gambio\StyleEdit\Core\Components\Option\Entities\Option;
use Gambio\StyleEdit\Core\Components\OrdainedList\Entities\OrdainedListItemOption;
use Gambio\StyleEdit\Core\Components\OrdainedList\Entities\OrdainedListOption;
use Gambio\StyleEdit\Core\Components\PaddingGroup\Entities\PaddingGroupOption;
use Gambio\StyleEdit\Core\Components\Pages\PagesController;
use Gambio\StyleEdit\Core\Components\ProductListGroup\Entities\ProductListGroupOption;
use Gambio\StyleEdit\Core\Components\ProductListing\Entities\ProductListingOption;
use Gambio\StyleEdit\Core\Components\ProductSearchBox\Entities\ProductSearchBoxOption;
use Gambio\StyleEdit\Core\Components\Radio\Entities\RadioOption;
use Gambio\StyleEdit\Core\Components\RadioImage\Entities\RadioImageOption;
use Gambio\StyleEdit\Core\Components\ResponsiveGroup\Entities\ResponsiveGroupOption;
use Gambio\StyleEdit\Core\Components\Slider\Entities\SliderOption;
use Gambio\StyleEdit\Core\Components\Style\CustomScssController;
use Gambio\StyleEdit\Core\Components\Style\CustomScssService;
use Gambio\StyleEdit\Core\Components\TextBox\Entities\TextBox;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\CurrentThemeInterface;
use Gambio\StyleEdit\Core\Components\Theme\Entities\ThemeConfigurationCollection;
use Gambio\StyleEdit\Core\Components\Theme\Repositories\ThemeConfigurationRepository;
use Gambio\StyleEdit\Core\Components\Theme\ThemeController;
use Gambio\StyleEdit\Core\Components\Url\Entities\UrlOption;
use Gambio\StyleEdit\Core\Components\Url\ValueModifier\UrlOptionValueModifier;
use Gambio\StyleEdit\Core\Components\Variant\Commands\VariantSaveCommand;
use Gambio\StyleEdit\Core\Components\Variant\Entities\VariantOption;
use Gambio\StyleEdit\Core\Components\Widget\WidgetController;
use Gambio\StyleEdit\Core\Components\Widget\WidgetRepository;
use Gambio\StyleEdit\Core\Components\Wysiwyg\Entities\WysiwygOption;
use Gambio\StyleEdit\Core\Language\Entities\Language;
use Gambio\StyleEdit\Core\Repositories\ValueObjects\SettingsEnvironment;
use Gambio\StyleEdit\Core\Services\Configuration\ConfigurationService;
use Gambio\StyleEdit\Core\Services\Configuration\Factories\ConfigurationServiceFactory;
use Gambio\StyleEdit\Core\Services\SettingsService;
use Gambio\StyleEdit\Core\Services\StyleEdit3Configuration\Factories\StyleEdit3ConfigurationServiceFactory;
use Gambio\StyleEdit\Core\Services\StyleEdit3Configuration\StyleEdit3ConfigurationService;
use GmConfigurationServiceInterface;
use GXModules\Gambio\StyleEdit\Adapters\Interfaces\ConfigurationAdapterInterface;
use GXModules\Gambio\StyleEdit\Core\Components\CategorySearchBox\CategorySearchBoxController;
use GXModules\Gambio\StyleEdit\Core\Components\GoogleMapsGroup\Entities\GoogleMapsGroupOption;
use GXModules\Gambio\StyleEdit\Core\Components\GapiKey\Entities\GapiKeyOption;
use GXModules\Gambio\StyleEdit\Core\Components\ProductSearchBox\ProductSearchBoxController;
use GXModules\Gambio\StyleEdit\Core\Components\LongTextBox\Entities\LongTextBoxOption;
use Slim\App;
use Slim\Factory\AppFactory;

/**
 * Class ThemePrototypeSingletonFactory
 */
class SingletonPrototype extends ClassBuilder
{
    /**
     * Initialize the prototype all the default classes of the StyleEdit
     */
    protected function initialize(): void
    {
        $this->objectList['ExportController'] = static function() {
            return new ExportController;
        };
        
        $this->objectList['ThemeController'] = static function () {
            return SingletonPrototype::instance()->get(ThemeController::class);
        };

        $this->objectList['ProductsearchController'] = static function () {
            return SingletonPrototype::instance()->get(ProductSearchBoxController::class);
        };

        $this->objectList['CategorysearchController'] = static function () {
            return SingletonPrototype::instance()->get(CategorySearchBoxController::class);
        };

        $this->objectList['WidgetController'] = static function () {
            return new WidgetController;
        };
        
        $this->objectList['DefaultController'] = static function() {
            return new DefaultController;
        };
        
        $this->objectList['CustomScssController'] = static function () {
            return new CustomScssController;
        };
        
        $this->objectList['ConfigurationController'] = static function () {
            return new ConfigurationController;
        };
        
        $this->objectList[ConfigurationService::class] = static function () {
            $themeFilesystem = static::instance()->get(FilesystemAdapter::class);
            $serviceFactory  = new ConfigurationServiceFactory($themeFilesystem);
            
            return $serviceFactory->service();
        };
        
        $this->objectList[StyleEdit3ConfigurationService::class] = static function () {
            $shopRootFilesystem = static::instance()->get('FilesystemAdapterShopRoot');
            $themeFilesystem    = static::instance()->get(FilesystemAdapter::class);
            $serviceFactory     = new StyleEdit3ConfigurationServiceFactory($shopRootFilesystem, $themeFilesystem);
            
            return $serviceFactory->service();
        };
        
        $this->objectList['JwtController'] = static function () {
            return self::instance()->get(JwtController::class);
        };
        
        $this->objectList['WidgetRepository'] = static function () {
            return new WidgetRepository;
        };
        
        $this->objectList[Language::class] = static function () {
            return new Language('en');
        };
        
        $this->objectList[SettingsService::class] = static function () {
            return new SettingsService(static::instance()->get(CurrentThemeInterface::class)->id());
        };
        
        $this->objectList['BackgroundSaveCommand'] = static function () {
            return new BackgroundSaveCommand;
        };
        
        $this->objectList['VariantSaveCommand'] = static function () {
            return self::instance()->get(VariantSaveCommand::class);
        };
        
        $this->objectList[SettingsEnvironment::class] = static function() {
            
            return defined('STYLE_EDIT_SETTINGS_ENVIRONMENT') ? new SettingsEnvironment(STYLE_EDIT_SETTINGS_ENVIRONMENT) : new SettingsEnvironment;
        };
        
        $this->objectList[ThemeConfigurationCollection::class] = static function () {
            $service = self::instance()->get(ThemeConfigurationRepository::class);
            
            return $service->get();
        };
        
        $this->objectList['Option'] = static function () {
            return new Option();
        };
        
        $this->objectList['VariantOption'] = static function () {
            return new VariantOption();
        };
        
        $this->objectList['TextOption']        = static function () {
            return new TextBox();
        };
        $this->objectList['TextboxOption']     = static function () {
            return new TextBox();
        };
        $this->objectList['NumberboxOption']   = static function () {
            return new NumberBoxOption();
        };
        $this->objectList['SliderOption']      = static function () {
            return new SliderOption();
        };
        $this->objectList['ColorpickerOption'] = static function () {
            return new ColorPickerOption();
        };
        $this->objectList['FontfamilyOption']  = static function () {
            return new FontFamilyOption();
        };
        $this->objectList['WysiwygOption']     = static function () {
            return new WysiwygOption;
        };
        
        $this->objectList['SourcecodeOption'] = static function () {
            return new SourcecodeOption;
        };
        
        $this->objectList['RadioimageOption'] = static function () {
            return new RadioImageOption();
        };
        
        $this->objectList['ToprightbottomleftOption'] = static function () {
            return new MarginGroupOption();
        };
        
        $this->objectList['SelectboxOption'] = static function () {
            return new DropdownSelectOption();
        };
        
        $this->objectList['BorderOption'] = static function () {
            return new BorderGroupOption;
        };
        
        $this->objectList['RadioOption']          = static function () {
            return new RadioOption();
        };
        $this->objectList['ImageuploadOption']    = static function () {
            return new ImageUploadOption();
        };
        $this->objectList['DropdownSelectOption'] = static function () {
            return new DropdownSelectOption();
        };
        $this->objectList['DropdownselectOption'] = static function () {
            return new DropdownSelectOption();
        };
        $this->objectList['OptionSaveCommand']    = static function () {
            return new OptionSaveCommand();
        };
        
        $this->objectList['GroupOptionSaveCommand'] = static function () {
            return new GroupOptionSaveCommand();
        };
        
        $this->objectList['PaddingOption'] = static function () {
            return new PaddingGroupOption();
        };
        
        $this->objectList['ResponsiveOption'] = static function () {
            return new ResponsiveGroupOption;
        };
        
        $this->objectList['BackgroundOption'] = static function () {
            return new BackgroundGroupOption();
        };
        
        $this->objectList['CheckboxOption']           = static function () {
            return new CheckboxOption();
        };
        $this->objectList['ColorPickerOption']        = static function () {
            return new ColorPickerOption();
        };
        $this->objectList['BackgroundImageOption']    = static function () {
            return new BackgroundImageGroupOption();
        };
        $this->objectList['BackgroundGradientOption'] = static function () {
            return new BackgroundGradientGroupOption();
        };
        
        $this->objectList['MarginOption'] = static function () {
            return new MarginGroupOption();
        };
        
        $this->objectList['MeasureValueOption'] = static function () {
            return new MeasureValueOption();
        };
        
        $this->objectList['ContentZoneOption'] = static function () {
            return new ContentZoneOption();
        };
        
        $this->objectList['GxLogoOption'] = static function () {
            return new GxLogoOption;
        };
        
        $this->objectList['GapiKeyOption'] = static function () {
            $configurationAdapter = self::instance()->get(ConfigurationAdapterInterface::class);
            $configurationNamespace = 'GOOGLE_API_KEY';
            return new GapiKeyOption($configurationAdapter, $configurationNamespace);
        };
        
        $this->objectList['GxLogoSaveCommand'] = static function () {
            $service = self::instance()->get(GmConfigurationServiceInterface::class);
            
            return new GxLogoSaveCommand($service);
        };
        
        $this->objectList['ContentZoneSaveCommand'] = static function () {
            // @todo: Consider moving it to DC to handle order.
            $service = self::instance()->get(ContentZoneService::class);
            
            return new ContentZoneSaveCommand($service);
        };
        
        $this->objectList['ProductsearchboxOption'] = static function () {
            return SingletonPrototype::instance()->get(ProductSearchBoxOption::class);
        };
        
        $this->objectList['CategorysearchboxOption'] = static function () {
            return SingletonPrototype::instance()->get(CategorySearchBoxOption::class);
        };
        
        $this->objectList['FontOption'] = static function () {
            return new FontGroupOption();
        };
        
        $this->objectList['UrlOption'] = static function () {
            return new UrlOption();
        };
        
        $this->objectList['CodeEditorOption'] = static function () {
            return new CodeEditorOption;
        };
        
        $this->objectList['ProductlistingOption'] = static function () {
            return new ProductListingOption;
        };
        
        $this->objectList['CustomScssService'] = static function () {
            return new CustomScssService;
        };
        
        $this->objectList['ProductlistOption'] = static function () {
            return new ProductListGroupOption();
        };
        
        $this->objectList['OrdainedlistOption'] = static function () {
            return new OrdainedListOption();
        };
        
        $this->objectList['OrdainedlistitemOption'] = static function () {
            return new OrdainedListItemOption();
        };
        
        $this->objectList['ThemeExtensionController'] = static function () {
            return new ThemeExtensionController;
        };
        
        // Slim App
        $this->objectList[App::class] = static function () {
            return AppFactory::create();
        };
        
        $this->objectList['PagesController'] = static function () {
            return SingletonPrototype::instance()->get(PagesController::class);
        };
        
        $this->objectList['ButtonimageController'] = static function () {
            return SingletonPrototype::instance()->get(ButtonImageController::class);
        };
        
        $this->objectList['UrlOptionValueModifier'] = static function() {
            return SingletonPrototype::instance()->get(UrlOptionValueModifier::class);
        };
        
        $this->objectList['GooglemapsOption'] = static function () {
            return new GoogleMapsGroupOption();
        };
        
        $this->objectList['LongtextboxOption'] = static function () {
            return new LongTextBoxOption();
        };
    }
    
}