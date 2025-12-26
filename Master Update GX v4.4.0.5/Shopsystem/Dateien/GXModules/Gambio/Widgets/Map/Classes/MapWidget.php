<?php
/*--------------------------------------------------------------------------------------------------
    MapWidget.php 2020-11-06
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\StyleEdit\Core\Components\NumberBox\Entities\NumberBoxOption;
use Gambio\StyleEdit\Core\Components\RadioImage\Entities\RadioImageOption;
use Gambio\StyleEdit\Core\Components\TextBox\Entities\TextBox;
use Gambio\StyleEdit\Core\Language\Entities\Language;
use Gambio\StyleEdit\Core\SingletonPrototype;
use Gambio\StyleEdit\Core\Widgets\Abstractions\AbstractWidget;
use GXModules\Gambio\StyleEdit\Adapters\Interfaces\ConfigurationAdapterInterface;
use GXModules\Gambio\StyleEdit\Adapters\Interfaces\TextManagerAdapterInterface;
use GXModules\Gambio\StyleEdit\Core\Components\GoogleMapsGroup\Entities\GoogleMapsGroupOption;

class MapWidget extends AbstractWidget
{
    
    /**
     * @var string
     */
    protected $type = 'map';
    
    
    /**
     * @var NumberBoxOption
     */
    
    protected $width;
    
    
    /**
     * @var NumberBoxOption
     */
    protected $height;
    
    
    /**
     * @var RadioImageOption
     */
    protected $style;
    
    /**
     * @var TextBox
     */
    protected $customStyle;
    
    /**
     * @var GoogleMapsGroupOption
     */
    protected $googleMaps;
    
    /**
     * @var ConfigurationAdapterInterface
     */
    protected $configurationAdapter;
    
    /**
     * @var TextManagerAdapterInterface
     */
    protected $textManagerAdapter;
    
    /**
     * @var CookieConsentPurposeReaderServiceInterface
     */
    protected $consentPurposeReaderService;
    
    /**
     * @var int
     */
    protected $cookieConsentPurposeId;
    
    
    public function __construct(
        string $static_id,
        array $fieldsets,
        stdClass $jsonObject,
        ConfigurationAdapterInterface $configurationAdapter,
        TextManagerAdapterInterface $textManagerAdapter,
        CookieConsentPurposeReaderServiceInterface $consentPurposeReaderService
    ) {
        parent::__construct($static_id, $fieldsets, $jsonObject);
        $this->configurationAdapter = $configurationAdapter;
        $this->textManagerAdapter = $textManagerAdapter;
        $this->consentPurposeReaderService = $consentPurposeReaderService;
    }
    
    /**
     * {@inheritdoc}
     */
    protected static function createWidgetObject(stdClass $jsonObject, $fieldSets = [])
    {
        return new self(
            $jsonObject->id,
            $fieldSets,
            $jsonObject,
            SingletonPrototype::instance()->get(ConfigurationAdapterInterface::class),
            SingletonPrototype::instance()->get(TextManagerAdapterInterface::class),
            SingletonPrototype::instance()->get(CookieConsentPurposeReaderServiceInterface::class)
        );
    }
    
    
    /**
     * @param Language|null $currentLanguage
     *
     * @return string
     */
    public function previewContent(?Language $currentLanguage): string
    {
        $gapiKeyOption = $this->googleMaps->apiKey()->value();
        
        // Saving the GAPI Key only if the user does not have any value in the database yet
        // or the values are different (in case for saving a new GAPI Key)
        if (!empty($gapiKeyOption)) {
            $gapiConfiguration = $this->configurationAdapter->get('GOOGLE_API_KEY')->value();
    
            if (empty($gapiConfiguration) || $gapiConfiguration !== $gapiKeyOption) {
                $this->configurationAdapter->set('GOOGLE_API_KEY', $gapiKeyOption);
            }
        }
    
    
        $configurationFactory = MainFactory::create(MapWidgetConfigurationFactory::class);
        $commandConfiguration = $configurationFactory->createCommandConfigurationFromArray([
            'id'               => $this->id->value(),
            'mapConfiguration' => $this->getMapConfig(),
            'languageId'       => $currentLanguage->id(),
            'width'            => $this->width->value(),
            'height'           => $this->height->value(),
            'isPreview'        => true
       ]);
    
        $readerService = SingletonPrototype::instance()->get(CookieConsentPurposeReaderServiceInterface::class);
        $textManager = SingletonPrototype::instance()->get(TextManagerAdapterInterface::class);
        $configurationAdapter = SingletonPrototype::instance()->get(ConfigurationAdapterInterface::class);
        
        return MainFactory::create(
            MapWidgetOutputCommand::class,
            $commandConfiguration,
            $readerService,
            $textManager,
            $configurationAdapter
            )->execute() ?? '';
    }
    
    
    /**
     * Saves the Google Api Key option in the database on the SAVE request
     *
     */
    public function update(): void
    {
        $gapiKeyOption = $this->googleMaps->apiKey()->value();
        
        if (!empty($gapiKeyOption)) {
            $this->configurationAdapter->set('GOOGLE_API_KEY', $gapiKeyOption);
        }
    }
    
    
    /**
     * @param Language|null $currentLanguage
     *
     * @return string
     */
    public function htmlContent(?Language $currentLanguage): string
    {
        $mapConfig = json_encode($this->getMapConfig());
        
        $html = "\n{google_maps_widget id='{$this->id->value()}'" .
                " languageId='{$currentLanguage->id()}'" .
                " width='{$this->width->value()}'" .
                " height='{$this->height->value()}'" .
                " mapConfiguration='{$mapConfig}'}";
        return $html;
    }
    
    
    /**
     * @inheritcDoc
     */
    public function jsonSerialize()
    {
        $result            = $this->jsonObject;
        $result->type      = $this->type;
        $result->id        = $this->static_id;
        $result->fieldsets = $this->fieldsets;
    
        return $result;
    }
    
    
    /**
     * @return array|false|string
     */
    protected function getMapStyle()
    {
        if ($this->style->value() === 'custom') {
            return json_decode($this->customStyle->value());
        }
        
        $styleJsonFilePath = dirname(__DIR__) . "/Assets/MapStyles/{$this->style->value()}.json";
    
        if (file_exists($styleJsonFilePath)) {
            $styleJson = file_get_contents($styleJsonFilePath);
            $styleJson = json_decode($styleJson);
    
            if (!json_last_error()) {
                return $styleJson;
            }
            
        }
    
        return [];
    }
    
    
    /**
     * @return array
     */
    protected function getMapConfig(): array
    {
        // Gambio's location (test purposes)
        $lat = $this->googleMaps->latitude()->value() ?: 53.0970934;
        $lon = $this->googleMaps->longitude()->value() ?: 8.7913892;
        $zoom = $this->googleMaps->zoom()->value() ?? 8;
        
        return [
            'center' => ['lat' => $lat, 'lng' => $lon],
            'zoom' => (int)$zoom,
            'styles' => $this->getMapStyle()
        ];
    }
    
}