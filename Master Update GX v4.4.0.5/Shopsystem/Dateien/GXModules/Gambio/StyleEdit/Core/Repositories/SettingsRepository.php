<?php
/*--------------------------------------------------------------------------------------------------
    SettingsRepository.php 2020-12-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\StyleEdit\Core\Repositories;

use Exception;
use Gambio\StyleEdit\Core\BuildStrategies\Exceptions\NotFoundException;
use Gambio\StyleEdit\Core\BuildStrategies\Interfaces\SingletonStrategyInterface;
use Gambio\StyleEdit\Core\Components\Theme\Entities\BasicTheme;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\CurrentThemeInterface;
use Gambio\StyleEdit\Core\Components\ValueModifier\AbstractValueModifier;
use Gambio\StyleEdit\Core\Json\FileIO;
use Gambio\StyleEdit\Core\Options\Entities\OptionInterface;
use Gambio\StyleEdit\Core\Repositories\Entities\Configuration;
use Gambio\StyleEdit\Core\Repositories\Entities\ConfigurationCollection;
use Gambio\StyleEdit\Core\SingletonPrototype;
use Gambio\StyleEdit\StyleEditConfiguration;
use ReflectionClass;
use stdClass;

/**
 * Class ConfigurationRepository
 * @package Gambio\StyleEdit\Core\Repositories
 */
class SettingsRepository extends BasicFileRepository implements SingletonStrategyInterface
{
    /**
     * @var string
     */
    public const SETTINGS_FILE_NAME = 'settings.json';
    /**
     * @var ConfigurationCollection
     */
    protected $configurationList;

    /**
     * @var string
     */
    protected $settingsFileName;
    /**
     * @var string
     */
    protected $themeId;
    
    
    /**
     * ConfigurationRepository constructor.
     *
     * @param FileIO|null                 $fileIO
     * @param CurrentThemeInterface       $themeConfig
     * @param StyleEditConfiguration|null $configurations
     *
     * @throws Exception
     */
    public function __construct(
        FileIO $fileIO = null,
        StyleEditConfiguration $configurations = null,
        CurrentThemeInterface $themeConfig = null
    ) {
        parent::__construct($fileIO, $configurations);
        if ($themeConfig) {
            $this->themeId = $themeConfig->id();
        }
    }


    /**
     * @param string $themeId
     *
     * @return SettingsRepository
     * @throws Exception
     */
    public static function createForTheme(string $themeId): self
    {
        try {
            $currentTheme = SingletonPrototype::instance()->get(CurrentThemeInterface::class);
        } catch (Exception $e) {
            $currentTheme = new BasicTheme('//////invalid\\\\\\');
        }
        
        if ($currentTheme->id() === $themeId) {
            $result = SingletonPrototype::instance()->get(static::class);
        } else {
            $result = SingletonPrototype::instance()->get(static::class,
                null,
                null,
                new BasicTheme($themeId));
        }
        
        return $result;
    }
    
    
    /**
     * @param $filename
     *
     * @return static
     * @throws Exception
     */
    public static function createForFile($filename): SettingsRepository
    {
        $result = new static();
        $result->settingsFileName = $filename;
        $result->configurationList = null;

        if (!$result->fileIO()->exists($filename)) {
            $result->fileIO()->write([], $filename);
        }

        return $result;
    }


    /**
     * @param string $name
     *
     * @return Configuration|null
     * @throws Exception
     */
    public function getJsonConfigurationFrom(string $name): ?Configuration
    {
        // Transform camelCase to dash-case because the settings.json is dash-cased and theme.json is camelCased
        $name = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $name));

        if ($this->configurationsList()->keyExists($name)) {
            return $this->configurationsList()->getValue($name);
        }

        return null;
    }
    
    
    /**
     * @return ConfigurationCollection
     * @throws Exception
     */
    public function configurationsList(): ConfigurationCollection
    {
        if ($this->configurationList === null) {
            $this->configurationList = ConfigurationCollection::createFromJsonList($this->loadSettingsObject());
        }
        
        return $this->configurationList;
    }
    
    
    /**
     * @return array|mixed
     * @throws Exception
     */
    private function loadSettingsObject()
    {
        $file = $this->getExistentSettingsFilename($this->getSettingsFilename());

        return ($file !== null) ? $this->loadJsonFilesFromDisk($file) : [];
    }
    
    
    /**
     * @return string
     * @throws Exception
     */
    protected function getSettingsFilename(): string
    {
        if ($this->settingsFileName === null) {
            $this->settingsFileName = $this->styleEditConfiguration->themesFolderPath() . $this->themeId
                . DIRECTORY_SEPARATOR . static::SETTINGS_FILE_NAME;
        }

        return $this->settingsFileName;
    }


    /**
     * @return bool
     * @throws Exception
     */
    public function hasConfiguration(): bool
    {
        return $this->getExistentSettingsFilename($this->getSettingsFilename()) !== null;
    }


    /**
     * @return ConfigurationCollection
     * @throws Exception
     */
    public function getAll(): ConfigurationCollection
    {
        return $this->configurationsList();
    }


    /**
     * @param Configuration[] $configurations
     * @throws Exception
     */
    public function saveJsonConfigurationFrom(Configuration ...$configurations)
    {
        foreach($configurations as $configuration) {
            $this->configurationsList()->setValue($configuration->id(), $configuration);
            if ($configuration->type() === 'variant') {
                $this->loadVariantSettings($configuration->id(), $configuration->value()->id);
            }
        }
        $this->configurationsList()->sort();
        $this->saveJsonFilesToDisk($this->configurationsList(), $this->getSettingsFilename());
    }


    /**
     * @param $variantId
     * @param $variantOptionId
     *
     * @throws Exception
     */
    protected function loadVariantSettings($variantId, $variantOptionId)
    {
        $variantSettingsPath = implode(DIRECTORY_SEPARATOR,
            [
                $this->configuration()->themesFolderPath(),
                $this->themeId,
                'variants',
                $variantId,
                $variantOptionId,
                static::SETTINGS_FILE_NAME
            ]);
        $variantSettingsPath = $this->getExistentSettingsFilename($variantSettingsPath);
        if ($variantSettingsPath !== null) {
            $jsonArray = $this->fileIO->read($variantSettingsPath);
            foreach ($jsonArray as $configuration) {
                $configuration = Configuration::createFromJson($configuration);
                $this->configurationsList()->setValue($configuration->id(), $configuration);
            }
        }
    }
    
    
    /**
     * @param OptionInterface $option
     *
     * @throws Exception
     */
    public function saveOptionToConfiguration(OptionInterface $option): void
    {
        if ($this->configurationsList()->keyExists($option->id())) {
            $jsonConfiguration = $this->configurationsList()->getValue($option->id());
            $jsonConfiguration = $jsonConfiguration->jsonSerialize();
            if (!isset($jsonConfiguration->type)) {
                $jsonConfiguration->type = $option->type();
            }
        } else {
            $jsonConfiguration        = new stdClass();
            $jsonConfiguration->name  = $option->id();
            $jsonConfiguration->type  = $option->type();
            $jsonConfiguration->group = $option->group() ?? 'bootstrap';
        }
    
        try {
        
            // short name is the class name without namespace
            $className    = (new ReflectionClass($option))->getShortName();
            $modifierName = $className . 'ValueModifier';
            $modifier     = SingletonPrototype::instance()->get($modifierName);
        
            /** @var AbstractValueModifier $modifier */
            $value = $modifier->setOption($option)->modify();
        } catch (NotFoundException $exception) {
            unset($exception);
            $value = $option->value();
        }
        
        $jsonConfiguration->value = $value;
        
        $configuration = Configuration::createFromJson($jsonConfiguration);
        $this->configurationsList()->setValue($configuration->id(), $configuration);

        if ($option->type() === 'variant') {
            $this->loadVariantSettings($option->id(), $option->value()->id());
        }
        
        $this->saveJsonFilesToDisk($this->configurationsList(), $this->getSettingsFilename());
        
        // variant specific option
        if ($option->for() !== null) {
            $this->saveConfigurationToVariant($configuration, $option->for());
        }
    }
    
    
    /**
     * @param Configuration $configuration
     *
     * @param string        $variant
     *
     * @throws Exception
     */
    protected function saveConfigurationToVariant(Configuration $configuration, string $variant): void
    {
        [$variantId, $variantOptionId] = explode('/', $variant);
        $variantSettingsFilename = $this->variantSettingsFilename($variantId, $variantOptionId);
        
        try {
            $variantJson = $this->loadJsonFilesFromDisk($variantSettingsFilename);
        } catch (Exception $exception) { // if file was not found
            $variantJson = [];
        }
        
        $variantConfiguration = ConfigurationCollection::createFromJsonList($variantJson);
        $variantConfiguration->setValue($configuration->id(), $configuration);
        $this->saveJsonFilesToDisk($variantConfiguration, $variantSettingsFilename);
    }
    
    
    /**
     * @param string $variantId
     * @param string $variantOptionId
     *
     * @return string
     * @throws Exception
     */
    protected function variantSettingsFilename(string $variantId, string $variantOptionId): string
    {
        $configurations = SingletonPrototype::instance()->get(StyleEditConfiguration::class);

        $variantsDirectoryPath = $configurations->themesFolderPath() . $this->themeId . DIRECTORY_SEPARATOR
            . 'variants';

        return implode(DIRECTORY_SEPARATOR,
            [$variantsDirectoryPath, $variantId, $variantOptionId, static::SETTINGS_FILE_NAME]);
    }
    
    
}