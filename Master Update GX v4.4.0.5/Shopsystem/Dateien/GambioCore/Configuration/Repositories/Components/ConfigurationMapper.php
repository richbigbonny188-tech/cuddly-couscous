<?php
/* --------------------------------------------------------------
 ConfigurationMapper.php 2019-10-21
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2019 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Repositories\Components;

use Gambio\Core\Configuration\Models\Read\Collections\ConfigurationGroup;
use Gambio\Core\Configuration\Models\Read\ConfigurationGroupItem;

/**
 * Class ConfigurationMapper
 * @package Gambio\Core\ConfigurationBak\Repository
 */
class ConfigurationMapper
{
    private const CONFIGURATION_PREFIX = ['gm_configuration/', 'configuration/'];
    private const FALLBACK_SECTIONS    = [
        'admin_general',
        'configuration'
    ];
    
    /**
     * @var OptionsResolver
     */
    private $resolver;
    
    /**
     * @var ConfigurationFactory
     */
    private $factory;
    
    
    /**
     * ConfigurationMapper constructor.
     *
     * @param OptionsResolver      $resolver
     * @param ConfigurationFactory $factory
     */
    public function __construct(OptionsResolver $resolver, ConfigurationFactory $factory)
    {
        $this->resolver = $resolver;
        $this->factory  = $factory;
    }
    
    
    /**
     * Maps the data array from storage into the configurations domain model.
     *
     * @param array $data
     *
     * @return ConfigurationGroup
     */
    public function map(array $data): ConfigurationGroup
    {
        $configurations = [];
        
        foreach ($data as $dataSet) {
            $configurations[] = $this->mapItem($dataSet);
        }
        
        return $this->factory->createConfigurations(...$configurations);
    }
    
    
    /**
     * Maps data of a configuration item into the domain model.
     *
     * @param array  $dataSet
     * @param string $section
     *
     * @return ConfigurationGroupItem
     */
    public function mapItem(array $dataSet, $section = 'configuration'): ConfigurationGroupItem
    {
        $key   = $dataSet['key'];
        $value = $dataSet['value'] ?? null;
        $lang  = $dataSet['lang'] ?? null;
        
        $txtKey      = str_replace(self::CONFIGURATION_PREFIX, '', $key);
        $title       = $this->getText("{$txtKey}_TITLE", $section, self::FALLBACK_SECTIONS);
        $description = $this->getText("{$txtKey}_DESC", $section, self::FALLBACK_SECTIONS);
        
        $configType = $this->factory->createConfigurationType($dataSet['type']);
        
        return $this->factory->createConfiguration($key,
                                                   $configType->inputType(),
                                                   $title,
                                                   $description,
                                                   $value,
                                                   $configType->toOptions($this->resolver, $value),
                                                   $lang);
    }
    
    
    /**
     * Resolves a text from the given phrase.
     *
     * If the text value equals the phrase, it means that no text was
     * found in the given section and the function uses the fallback
     * sections to get the text. If nothing was found, the phrase will
     * be returned.
     *
     * @param string $phrase
     * @param string $section
     * @param array  $fallbackSections
     *
     * @return string
     */
    private function getText(string $phrase, string $section, array $fallbackSections): string
    {
        $text = $this->resolver->getText($phrase, $section);
        if ($text !== $phrase) {
            return $text;
        }
        
        foreach ($fallbackSections as $fallbackSection) {
            $text = $this->resolver->getText($phrase, $fallbackSection);
            if ($text !== $phrase) {
                return $text;
            }
        }
        
        return $text;
    }
}