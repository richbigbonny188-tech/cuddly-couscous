<?php

namespace Gambio\StyleEdit\Core\Repositories\Entities;

use EditableKeyValueCollection;
use Exception;
use Gambio\StyleEdit\Core\Options\Entities\CollectionTrait;
use Gambio\StyleEdit\Core\Repositories\ValueObjects\SettingsEnvironment;
use Gambio\StyleEdit\Core\SingletonPrototype;
use JsonSerializable;

/**
 * Class ThemeConfigurationCollection
 * @package Gambio\StyleEdit\Core\Repositories\Entities
 */
class ConfigurationCollection extends EditableKeyValueCollection implements JsonSerializable
{
    
    
    /**
     * @param mixed $value
     *
     * @return \Gambio\StyleEdit\Core\Repositories\Entities\ConfigurationCollection
     */
    public function addItem(Configuration $value)
    {
        $this->setValue($value->id(), $value);
        
        return $this;
    }
    
    
    /**
     * Get valid type.
     *
     * This method must be implemented in the child-collection classes.
     *
     * @return string
     */
    protected function _getValidType()
    {
        return Configuration::class;
    }
    
    
    /**
     * @param $jsonConfigurationList
     *
     * @return \Gambio\StyleEdit\Core\Repositories\Entities\ConfigurationCollection
     * @throws \Exception
     */
    
    public static function createFromJsonList($jsonConfigurationList)
    {
        
        if (!is_array($jsonConfigurationList)) {
            throw new Exception('Invalid configuration Object');
        }
        $result = new ConfigurationCollection([]);
        
        /** @var SettingsEnvironment $settingsEnvironment */
        $settingsEnvironment = SingletonPrototype::instance()->get(SettingsEnvironment::class);
        
        foreach ($jsonConfigurationList as $jsonConfiguration) {
            
            if ($settingsEnvironment->value() === 'shop') {
                
                $result->addItem(ScssConfiguration::createFromJson($jsonConfiguration));
                continue;
            }
            
            $result->addItem(Configuration::createFromJson($jsonConfiguration));
        }
        
        return $result;
    }
    
    
    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return array_values($this->collectionContentArray);
    }
    
    
    /**
     * @param string $p_keyName
     *
     * @return Configuration
     */
    public function getValue($p_keyName) : Configuration
    {
        return parent::getValue($p_keyName);
    }
}