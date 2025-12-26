<?php
/*--------------------------------------------------------------------------------------------------
    ConfigurationCategoryFactory.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Options\Factories;

use Exception;
use Gambio\StyleEdit\Configurations\ShopBasePath;
use Gambio\StyleEdit\Configurations\ShopBaseUrl;
use Gambio\StyleEdit\Core\Components\Variant\Json\VariantInheritanceHandler;
use Gambio\StyleEdit\Core\Components\Variant\Services\VariantService;
use Gambio\StyleEdit\Core\Language\Services\LanguageService;
use Gambio\StyleEdit\Core\Options\Entities\ConfigurationCategory;
use Gambio\StyleEdit\Core\Options\Entities\ConfigurationCategoryCollection;
use Gambio\StyleEdit\Core\Options\Entities\FieldSetCollection;
use Gambio\StyleEdit\Core\Repositories\SettingsRepository;
use Gambio\StyleEdit\Core\SingletonPrototype;
use Gambio\StyleEdit\StyleEditConfiguration;
use RuntimeException;
use stdClass;

/**
 * Class ConfigurationCategoryFactory
 * @package Gambio\StyleEdit\Core\Options\Factories
 */
class ConfigurationCategoryFactory
{
    public const DEFAULT_THUMBNAIL_FILENAME = 'thumbnail.png';
    /**
     * @var FieldSetFactory
     */
    protected $fieldSetFactory;
    /**
     * @var VariantInheritanceHandler
     */
    protected $inheritanceHandler;
    /**
     * @var LanguageService
     */
    protected $languageService;
    /**
     * @var VariantService
     */
    protected $variantService;
    /**
     * @var ShopBaseUrl
     */
    private $baseUrl;
    /**
     * @var ShopBasePath
     */
    private $basePath;


    /**
     * ConfigurationCategoryFactory constructor.
     *
     * @param VariantService $variantService
     * @param LanguageService $languageService
     * @param VariantInheritanceHandler $inheritanceHandler
     * @param FieldSetFactory $fieldSetFactory
     */
    public function __construct(
        VariantService $variantService,
        LanguageService $languageService,
        VariantInheritanceHandler $inheritanceHandler,
        FieldSetFactory $fieldSetFactory,
        ShopBaseUrl $baseUrl,
        ShopBasePath $basePath
    )
    {
        $this->variantService = $variantService;
        $this->languageService = $languageService;
        $this->inheritanceHandler = $inheritanceHandler;
        $this->fieldSetFactory = $fieldSetFactory;
        $this->baseUrl = $baseUrl;
        $this->basePath = $basePath;
    }


    /**
     * @param string $themeId
     * @param string|null $variantId
     * @param string|null $variantOptionId
     *
     * @return ConfigurationCategory
     * @throws Exception
     */
    public function createFromVariant(
        string $themeId,
        ?string $variantId,
        ?string $variantOptionId
    ): ?ConfigurationCategory
    {
        $variantSettingsFile = $this->variantSettingsFilename($themeId, $variantId, $variantOptionId);
        $variantFile = dirname($variantSettingsFile) . DIRECTORY_SEPARATOR . 'variant.json';
        $this->inheritanceHandler->setFilename($variantFile);
        $variantJson = $this->inheritanceHandler->execute();
        // Copies the default values to settings.json
        $repository = SettingsRepository::createForFile($variantSettingsFile);

        return $this->createFromJsonAndRepository($themeId, $variantJson, $repository);
    }


    /**
     * @param string $themeId
     * @param string $variantId
     * @param string $variantOptionId
     *
     * @return string
     * @throws Exception
     */
    protected function variantSettingsFilename(string $themeId, string $variantId, string $variantOptionId): string
    {
        $configurations = SingletonPrototype::instance()->get(StyleEditConfiguration::class);

        $variantsDirectoryPath = $configurations->themesFolderPath() . $themeId . DIRECTORY_SEPARATOR . 'variants';

        return implode(DIRECTORY_SEPARATOR, [$variantsDirectoryPath, $variantId, $variantOptionId, 'settings.json']);
    }


    /**
     * @param string $themeId
     * @param stdClass $variantJsonObject
     * @param SettingsRepository $configurationRepository
     *
     * @return ConfigurationCategory
     * @throws Exception
     */
    protected function createFromJsonAndRepository(
        string $themeId,
        stdClass $variantJsonObject,
        SettingsRepository $configurationRepository
    ): ConfigurationCategory
    {
        if ($themeId === null) {
            throw new Exception('Invalid Theme ID!');
        }

        if (!isset($variantJsonObject->categories) && !isset($variantJsonObject->fieldsets)) {
            throw new Exception('Category must have FieldSets or Categories!');
        }

        if (isset($variantJsonObject->categories, $variantJsonObject->fieldsets)) {
            throw new Exception('Category must have FieldSets or Categories but not both!');
        }

        if (!isset($variantJsonObject->id)) {
            throw new Exception('Category must have an id!');
        }

        return new ConfigurationCategory(
            $variantJsonObject->id ?? null,
            $variantJsonObject->title ?? null,
            $variantJsonObject->type ?? null,
            $variantJsonObject->basic ?? null,
            $variantJsonObject->hidden ?? null,
            $variantJsonObject->for ?? null,
            $variantJsonObject->selector ?? null,
            $variantJsonObject->pageNamespace ?? null,
            $this->createListFromJsonAndRepository($themeId,
                $variantJsonObject->categories ??
                null,
                $configurationRepository),
            $this->createFieldsetCollection($variantJsonObject->fieldsets ?? null,
                $configurationRepository)

        );

    }


    /**
     * @param string $themeId
     * @param array|null $jsonCategoryList
     *
     * @param SettingsRepository $configurationRepository
     *
     * @return ConfigurationCategoryCollection|null
     * @throws Exception
     */
    protected function createListFromJsonAndRepository(
        string $themeId,
        array $jsonCategoryList = null,
        SettingsRepository $configurationRepository
    ): ?ConfigurationCategoryCollection
    {
        $result = null;
        if ($jsonCategoryList) {
            if (!is_array($jsonCategoryList)) {
                throw new RuntimeException('Categories field must  be an array!');
            }
            $result = new ConfigurationCategoryCollection();
            foreach ($jsonCategoryList as &$jsonCategoryConfig) {

                if (isset($jsonCategoryConfig->categories)
                    && $this->categoryCollectionContainsVariant($jsonCategoryConfig->categories)) {

                    $jsonCategoryConfig->categories = $this->addVariantCategoriesToCategory($themeId,
                        $jsonCategoryConfig->categories);
                }

                $result->addItem($this->createFromJsonAndRepository($themeId,
                    $jsonCategoryConfig,
                    $configurationRepository));
            }
        }

        return $result;
    }


    /**
     * @param array $jsonFieldSetList
     * @param SettingsRepository $configurationRepository
     *
     * @return FieldSetCollection|null
     * @throws Exception
     */
    protected function createFieldsetCollection(
        array $jsonFieldSetList = null,
        SettingsRepository $configurationRepository
    ): ?FieldSetCollection
    {
        $result = null;
        if (is_array($jsonFieldSetList)) {
            $result = new FieldSetCollection();
            foreach ($jsonFieldSetList as $fieldsetItem) {
                $result->addItem($this->fieldSetFactory->createFromJsonAndRepository($fieldsetItem, $configurationRepository));
            }
        }

        return $result;
    }


    /**
     * @param array $categories
     *
     * @return bool
     */
    protected function categoryCollectionContainsVariant(?array $categories): bool
    {
        if (!is_array($categories) || empty($categories)) {

            return false;
        }

        foreach ($categories as $category) {

            if (isset($category->fieldsets)) {

                foreach ($category->fieldsets as $fieldset) {

                    if (isset($fieldset->options)) {

                        foreach ($fieldset->options as $option) {

                            if ($option->type === 'variant') {

                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }


    /**
     * @param string $themeId
     * @param array $category
     *
     * @return array $category with variant categories
     *
     * @throws Exception
     */
    public function addVariantCategoriesToCategory(string $themeId, array $category): array
    {
        $variantCategories = [];

        foreach ($category as &$categories) {

            foreach ($categories->fieldsets as &$fieldset) {

                foreach ($fieldset->options as &$element) {

                    if ($element->type === 'variant') {

                        foreach ($element->options as &$option) {

                            $option->dir = 'variants/' . $element->id . '/' . $option->id;

                            $variantExists = $this->variantService->exists($element->id, $option->id);

                            if (!$variantExists) {
                                $this->variantService->createInheritedVariantOption($element->id, $option->id);
                            }

                            if (!$this->variantService->hasSettings($option->dir)) {
                                //create the categories for the variant just to initialize the settings.json of each category
                                $this->createFromVariant($themeId, $element->id, $option->id);
                            }
                            $variantJson = $this->variantService->loadVariantJson($element->id, $option->id);
                            $variantIdentification = $element->id . '/' . $option->id;

                            $this->setForProperties($variantJson, $variantIdentification);
                            $this->translateVariantProperties($variantJson);

                            $variantCategories[] = $variantJson;

                            // add variant's thumbnail
                            $thumbnail = $variantJson->thumbnail ?? null;
                            if (!$thumbnail) {
                                $thumbnailFilename = $option->thumbnail ?? self::DEFAULT_THUMBNAIL_FILENAME;
                                $thumbnail = "{$themeId}/{$option->dir}/$thumbnailFilename";
                            }

                            $option->thumbnail = $this->getThumbnailPath($thumbnail);
                        }
                    }
                }
            }

            unset($categories, $fieldset, $element, $option);

            array_splice($category, 1, 0, $variantCategories);

            return $category;
        }
    }


    /**
     * @param string $thumbnailFullPath
     *
     * @return string
     */
    protected function getThumbnailPath(string $thumbnailFullPath): string
    {
        $thumbnailPath = str_replace($this->basePath->value(), '', $thumbnailFullPath);
        return $this->baseUrl->value() . "themes/{$thumbnailPath}";
    }


    /**
     * @param stdClass $variantJson
     * @param string $variantIdentification
     */
    protected function setForProperties(stdClass $variantJson, string $variantIdentification): void
    {
        $variantJson->for = $variantIdentification;

        if (isset($variantJson->categories)) {

            foreach ($variantJson->categories as $category) {

                $this->setForProperties($category, $variantIdentification);
            }
        }

        if (isset($variantJson->fieldsets)) {

            foreach ($variantJson->fieldsets as $fieldset) {

                $fieldset->for = $variantIdentification;

                if (isset($fieldset->options)) {

                    foreach ($fieldset->options as $option) {

                        $option->for = $variantIdentification;
                    }
                }
            }
        }
    }


    /**
     * @param stdClass $variantJson
     *
     * @throws Exception
     */
    protected function translateVariantProperties(stdClass $variantJson): void
    {
        $variantJson->title = $this->languageService->translate($variantJson->title);

        if (isset($variantJson->categories)) {

            foreach ($variantJson->categories as $category) {

                $this->translateVariantProperties($category);
            }
        }

        if (isset($variantJson->fieldsets)) {

            foreach ($variantJson->fieldsets as $fieldset) {

                $fieldset->title = $this->languageService->translate($fieldset->title);

                foreach ($fieldset->options as $option) {

                    $option->label = $this->languageService->translate($option->label);
                }
            }
        }
    }


    /**
     * @param string $themeId
     * @param stdClass $variantJsonObject
     *
     * @return mixed
     * @throws Exception
     */
    public function createFromThemeIdAndJsonObject(string $themeId, stdClass $variantJsonObject)
    {
        $repository = SettingsRepository::createForTheme($themeId);

        return $this->createFromJsonAndRepository($themeId, $variantJsonObject, $repository);
    }


}