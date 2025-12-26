<?php
/*--------------------------------------------------------------------------------------------------
    ThemeConfigurationFactory.php 2020-07-17
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\StyleEdit\Core\Components\Theme\Factories;

use Exception;
use FileNotFoundException;
use Gambio\StyleEdit\Configurations\ShopBaseUrl;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\ActiveThemeInterface;
use Gambio\StyleEdit\Core\Components\Theme\Entities\ThemeConfiguration;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Translators\ImagePathTranslator;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Translators\LanguageTranslator;
use Gambio\StyleEdit\Core\Components\Theme\Validator;
use Gambio\StyleEdit\Core\Language\Services\LanguageService;
use Gambio\StyleEdit\Core\Options\Entities\ConfigurationCategory;
use Gambio\StyleEdit\Core\Options\Factories\ConfigurationCategoryFactory;
use Gambio\StyleEdit\Core\Repositories\SettingsRepository;
use Gambio\StyleEdit\Core\Repositories\Entities\ConfigurationCollection;
use Gambio\StyleEdit\Core\Services\SettingsService;
use Gambio\StyleEdit\StyleEditConfiguration;
use stdClass;

/**
 * Class ThemeConfigurationFactory
 * @package Gambio\StyleEdit\Core\Components\Theme\Factories
 */
class ThemeConfigurationFactory
{
    /**
     * @var bool
     */
    protected $activeTheme;
    /**
     * @var ShopBaseUrl
     */
    protected $baseUrl;
    /**
     * @var ImagePathTranslator
     */
    protected $imagePathTranslator;
    /**
     * @var LanguageService
     */
    protected $languageService;
    /**
     * @var LanguageTranslator
     */
    protected $languageTranslator;
    /**
     * @var StyleEditConfiguration|null
     */
    protected $styleEditConfiguration;
    /**
     * @var ConfigurationCategoryFactory
     */
    private $categoryFactory;
    /**
     * @var SettingsService
     */
    private $theme;


    /**
     * ThemeConfigurationFactory constructor.
     *
     * @param ShopBaseUrl $baseUrl
     * @param LanguageTranslator $themeTranslator
     *
     * @param LanguageService $languageService
     * @param ImagePathTranslator $imagePathTranslator
     * @param StyleEditConfiguration|null $styleEditConfiguration
     * @param ActiveThemeInterface $activeTheme
     * @param ConfigurationCategoryFactory $categoryFactory
     */
    public function __construct(
        ShopBaseUrl $baseUrl,
        LanguageTranslator $themeTranslator,
        LanguageService $languageService,
        ImagePathTranslator $imagePathTranslator,
        StyleEditConfiguration $styleEditConfiguration,
        ActiveThemeInterface $activeTheme,
        ConfigurationCategoryFactory $categoryFactory

    )
    {
        $this->baseUrl = $baseUrl;
        $this->languageTranslator = $themeTranslator;
        $this->imagePathTranslator = $imagePathTranslator;
        $this->styleEditConfiguration = $styleEditConfiguration;
        $this->languageService = $languageService;
        $this->activeTheme = $activeTheme;
        $this->categoryFactory = $categoryFactory;
    }


    /** Creates a themeConfiguration instance with all the related data
     *
     * @param stdClass $themeConfig
     *
     * @return ThemeConfiguration
     *
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function createExtendedFromJson(
        stdClass $themeConfig
    ): ThemeConfiguration
    {
        $this->languageTranslator->translateContent($themeConfig);
        $this->imagePathTranslator->translateContent($themeConfig);
        $path = $this->styleEditConfiguration->themesFolderPath() . $themeConfig->id;

        //areas must be initialized first in order to create the settings.json
        $areas = $this->getAreas($themeConfig);
        $basics = $this->getBasics($themeConfig);
        $styles = $this->getStyles($themeConfig);

        $repository = SettingsRepository::createForTheme($themeConfig->id);

        return new ThemeConfiguration($themeConfig->id ?? null,
            $this->getTitle($themeConfig),
            $this->getThumbnail($themeConfig),
            $this->getAuthor($themeConfig),
            $this->getVersion($themeConfig),
            $themeConfig->extends ?? null,
            $themeConfig->inherits ?? null,
            //07
            $this->getIsPreview($themeConfig),
            $this->getIsEditable($themeConfig),
            $this->getIsRemovable($themeConfig),
            $this->getIsActive($themeConfig),
            $this->getColorPalette($themeConfig->colorPalette ?? null, $repository->getAll()),
            $areas,
            $basics,
            $styles,
            $this->getChildren(),
            $this->languageService->getActiveLanguages()->getArray(),
            $path,
            $this->getIsUpdatable($themeConfig));
    }


    /**
     * @param stdClass $themeConfig
     *
     * @return string
     */
    private function getTitle(stdClass $themeConfig): string
    {
        if (isset($themeConfig->id)) {
            return isset($themeConfig->title) ? (string)$themeConfig->title : (string)$themeConfig->id;
        }
    }


    /**
     * @param stdClass $themeConfig
     *
     * @return string|null
     */
    private function getThumbnail(stdClass $themeConfig): ?string
    {
        if (isset($themeConfig->thumbnail)) {
            return (string)$themeConfig->thumbnail;
        }

        return null;
    }


    /**
     * @param stdClass $themeConfig
     *
     * @return mixed
     */
    private function getAuthor(stdClass $themeConfig): ?string
    {
        if (isset($themeConfig->author)) {
            return (string)$themeConfig->author;
        }

        return null;
    }


    /**
     * @param stdClass $themeConfig
     *
     * @return mixed
     */
    private function getVersion(stdClass $themeConfig): ?string
    {
        if (isset($themeConfig->version)) {
            return (string)$themeConfig->version;
        }

        return null;
    }


    /**
     * @param stdClass $themeConfig
     *
     * @return bool
     */
    private function getIsPreview(stdClass $themeConfig): bool
    {
        return isset($themeConfig->preview, $themeConfig->id)
            && $themeConfig->preview
            && strpos($themeConfig->id, '_preview');
    }


    /**
     * @param stdClass $themeConfig
     *
     * @return bool
     * @throws FileNotFoundException
     */
    private function getIsEditable(stdClass $themeConfig): bool
    {

        return !$this->getIsPreview($themeConfig)
            && isset($themeConfig->id)
            && Validator::for($themeConfig->id)->canBeOpenedInStyleEdit4();
    }


    /**
     * @param stdClass $themeConfig
     *
     * @return bool
     * @throws Exception
     */
    private function getIsRemovable(stdClass $themeConfig): bool
    {

        return !(in_array($themeConfig->id ?? '', $this->getGambioThemeIds(), true)
                || $this->getIsActive($themeConfig))
            || $this->getIsPreview($themeConfig);
    }


    /**
     * @param stdClass $themeConfig
     *
     * @return bool
     * @throws Exception
     */
    private function getIsActive(stdClass $themeConfig): bool
    {
        return isset($themeConfig->id) && $themeConfig->id === $this->activeTheme->value();
    }


    /**
     * @param array $colorPalette
     *
     * @param ConfigurationCollection $configurationCollection
     *
     * @return array
     * @throws Exception
     */
    protected function getColorPalette(array $colorPalette = null, ConfigurationCollection $configurationCollection): array
    {
        if ($colorPalette === null) {
            $colorPalette = [];
        }
        $result = [];

        if (is_array($colorPalette)) {
            foreach ($colorPalette as &$variableName) {
                $result[$variableName] = $configurationCollection->getValue($variableName)->value();
            }
        }
        return $result;
    }


    /**
     * @param stdClass $themeConfig
     *
     * @return ConfigurationCategory
     * @throws Exception
     */
    protected function getAreas(stdClass $themeConfig): ?ConfigurationCategory
    {
        if (isset($themeConfig->config, $themeConfig->config->areas) && $themeConfig->config->areas) {
            return $this->categoryFactory->createFromThemeIdAndJsonObject($themeConfig->id, $themeConfig->config->areas);
        }

        return null;
    }


    /**
     * @param stdClass $themeConfig
     *
     * @return ConfigurationCategory
     * @throws Exception
     */
    protected function getBasics(stdClass $themeConfig): ?ConfigurationCategory
    {
        if (isset($themeConfig->config, $themeConfig->config->basics) && $themeConfig->config->basics) {

            return $this->categoryFactory->createFromThemeIdAndJsonObject($themeConfig->id, $themeConfig->config->basics);
        }

        return null;
    }


    /**
     * @param stdClass $themeConfig
     *
     * @return ConfigurationCategory
     * @throws Exception
     */
    protected function getStyles(stdClass $themeConfig): ?ConfigurationCategory
    {
        if (isset($themeConfig->config, $themeConfig->config->styles) && $themeConfig->config->styles) {
            return $this->categoryFactory->createFromThemeIdAndJsonObject($themeConfig->id, $themeConfig->config->styles);
        }

        return null;
    }


    /**
     * @return array
     */
    protected function getChildren(): array
    {
        return [];
    }


    /**
     * @return array
     */
    protected function getGambioThemeIds(): array
    {
        return [
            'Honeygrid',
            'Malibu',
            'Childgrid',
            'Grandgrid'
        ];
    }


    /** Simplified ThemeConfiguration for listing purposes
     *
     * @param stdClass $themeConfig
     *
     * @return ThemeConfiguration
     * @throws FileNotFoundException
     */
    public function createFromJson(
        stdClass $themeConfig
    ): ThemeConfiguration
    {

        $this->languageTranslator->translateContent($themeConfig);
        $this->imagePathTranslator->translateContent($themeConfig);
        $path = $this->styleEditConfiguration->themesFolderPath() . $themeConfig->id;

        return new ThemeConfiguration($themeConfig->id ?? null,//01
            $this->getTitle($themeConfig),//02
            $this->getThumbnail($themeConfig),//03
            $this->getAuthor($themeConfig),//04
            $this->getVersion($themeConfig),//05
            $themeConfig->extends ?? null,//06
            $themeConfig->inherits ?? null,//07
            $this->getIsPreview($themeConfig),//08
            $this->getIsEditable($themeConfig),//09
            $this->getIsRemovable($themeConfig),//10
            $this->getIsActive($themeConfig),//11
            [],//12
            null,//13
            null,//14
            null,//15
            $this->getChildren(),//16
            $this->languageService->getActiveLanguages()->getArray(),//17
            $path,
            $this->getIsUpdatable($themeConfig));//18
    }

    protected function getIsUpdatable(stdClass $themeConfig)
    {
        if(in_array($themeConfig->id ?? '', $this->getGambioThemeIds(), true))
            return false;

        if(isset($themeConfig->updatable))
            return $themeConfig->updatable == true;

        //check the old name pattern
        $re = '/([a-zA-Z0-9_-]+)([0-9]{4})-([0-9]{2})-([0-9]{2})_([0-9]{2})-([0-9]{2})-([0-9]{2})[\.]*/m';
        if(preg_match_all($re, $themeConfig->id, $matches, PREG_SET_ORDER, 0)){
            return true;
        }
        return false;
    }
}