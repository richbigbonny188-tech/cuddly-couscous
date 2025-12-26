<?php
/*--------------------------------------------------------------------------------------------------
    GoogleMapsGroupOption.php 2020-08-14
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace GXModules\Gambio\StyleEdit\Core\Components\GoogleMapsGroup\Entities;

use Gambio\StyleEdit\Core\Components\Slider\Entities\SliderOption;
use Gambio\StyleEdit\Core\Components\TextBox\Entities\TextBox;
use Gambio\StyleEdit\Core\Options\Entities\AbstractComponentGroupOption;
use Gambio\StyleEdit\Core\SingletonPrototype;
use GXModules\Gambio\StyleEdit\Core\Components\GapiKey\Entities\GapiKeyOption;

class GoogleMapsGroupOption extends AbstractComponentGroupOption
{
    
    
    /**
     * @var SliderOption
     */
    protected $zoom;
    
    
    /**
     * @var TextBox
     */
    protected $latitude;
    
    
    /**
     * @var TextBox
     */
    protected $longitude;
    
    
    /**
     * @var GapiKeyOption
     */
    protected $apiKey;
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->zoom      = SingletonPrototype::instance()->get('SliderOption');
        $this->latitude  = SingletonPrototype::instance()->get('TextOption');
        $this->longitude = SingletonPrototype::instance()->get('TextOption');
        $this->apiKey    = SingletonPrototype::instance()->get('GapiKeyOption');
    }
    
    
    public function __clone()
    {
        parent::__clone();
        
        $this->zoom      = clone $this->zoom;
        $this->latitude  = clone $this->latitude;
        $this->longitude = clone $this->longitude;
        $this->apiKey    = clone $this->apiKey;
    }
    
    
    /**
     * @return SliderOption
     */
    public function zoom(): SliderOption
    {
        return $this->zoom;
    }
    
    
    /**
     * @return TextBox
     */
    public function latitude(): TextBox
    {
        return $this->latitude;
    }
    
    
    /**
     * @return TextBox
     */
    public function longitude(): TextBox
    {
        return $this->longitude;
    }
    
    
    /**
     * @return GapiKeyOption
     */
    public function apiKey(): GapiKeyOption
    {
        return $this->apiKey;
    }
    
    
    /**
     * @inheritcDoc
     */
    protected function isValid($value): bool
    {
        return true;
    }
    
    
    /**
     * @inheritcDoc
     */
    protected function parseValue($value)
    {
        return $value;
    }
    
    
    /**
     * @param $object
     *
     * @throws \Exception
     */
    public function initializeFromJsonObject($object): void
    {
        parent::initializeFromJsonObject($object);
        
        if (isset($object->id)) {
            $this->id = $object->id;
        }
        
        if (isset($object->label)) {
            $this->label = $object->label;
        }
        
        $this->initZoomOption($object)
            ->initLatitudeOption($object)
            ->initLongitudeOption($object)
            ->initApiKeyOption($object);
    }
    
    
    /**
     * @inheritcDoc
     */
    public function getGroupOptions()
    {
        return [
            $this->zoom(),
            $this->latitude(),
            $this->longitude(),
            $this->apiKey()
        ];
    }
    
    
    /**
     * @param $object
     *
     * @return GoogleMapsGroupOption
     * @throws \Exception
     */
    protected function initZoomOption($object): self
    {
        $settings = ['id' => $this->id() . '-zoom'];
        if (isset($object->default->zoom)) {
            if (isset($object->default->zoom->attributes)) {
                $settings['attributes'] = $object->default->zoom->attributes;
                unset($object->default->zoom->attributes);
            }
            
            if (isset($object->default->zoom->value)) {
                $settings['default'] = $object->default->zoom->value;
            }
            
            if (isset($object->default->zoom->label)) {
                $settings['label'] = $object->default->zoom->label;
            }
        }
        
        if (isset($object->items->zoom)) {
            if (isset($object->items->zoom->attributes)) {
                $settings['attributes'] = $object->items->zoom->attributes;
                unset($object->items->zoom->attributes);
            }
            
            if (isset($object->items->zoom->value)) {
                $settings['value'] = $object->items->zoom->value;
            }
        }
        
        $this->zoom()
            ->withConfigurationRepository($this->configurationRepository)
            ->initializeFromJsonObject((object)$settings);
        
        return $this;
    }
    
    
    /**
     * @param $object
     *
     * @return GoogleMapsGroupOption
     * @throws \Exception
     */
    protected function initLatitudeOption($object): self
    {
        $settings = ['id' => $this->id() . '-latitude'];
        if (isset($object->default->latitude)) {
            $settings['default'] = $object->default->latitude;
        }
        
        if (isset($object->items->latitude)) {
            $settings['value'] = $object->items->latitude;
        }
        
        $this->latitude()
            ->withConfigurationRepository($this->configurationRepository)
            ->initializeFromJsonObject((object)$settings);
        
        return $this;
    }
    
    
    /**
     * @param $object
     *
     * @return GoogleMapsGroupOption
     * @throws \Exception
     */
    protected function initLongitudeOption($object): self
    {
        $settings = ['id' => $this->id() . '-longitude'];
        if (isset($object->default->longitude)) {
            $settings['default'] = $object->default->longitude;
        }
        
        if (isset($object->items->longitude)) {
            $settings['value'] = $object->items->longitude;
        }
        
        $this->longitude()
            ->withConfigurationRepository($this->configurationRepository)
            ->initializeFromJsonObject((object)$settings);
        
        return $this;
    }
    
    
    /**
     * @param $object
     *
     * @return GoogleMapsGroupOption
     * @throws \Exception
     */
    protected function initApiKeyOption($object): self
    {
        $settings = ['id' => $this->id() . '-apiKey'];
        if (isset($object->default->apiKey)) {
            $settings['default'] = $object->default->apiKey;
        }
        
        if (isset($object->items->apiKey)) {
            $settings['value'] = $object->items->apiKey;
        }
        
        $this->apiKey()
            ->withConfigurationRepository($this->configurationRepository)
            ->initializeFromJsonObject((object)$settings);
        
        return $this;
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
        $result          = new \stdClass;
        $result->id      = $this->id();
        $result->type    = $this->type();
        $result->label   = $this->label();
        $result->items   = (object)[
            'zoom'      => (object)[
                'value'      => $this->zoom()->value(),
                'attributes' => $this->zoom()->attributes()->jsonSerialize()
            ],
            'latitude'  => $this->latitude()->value(),
            'longitude' => $this->longitude()->value(),
            'apiKey'    => $this->apiKey()->value(),
        ];
        $result->default = (object)[
            'zoom'      => (object)[
                'value'      => $this->zoom()->defaultValue(),
                'attributes' => $this->zoom()->attributes()->jsonSerialize()
            ],
            'latitude'  => $this->latitude()->defaultValue(),
            'longitude' => $this->longitude()->defaultValue(),
            'apiKey'    => $this->apiKey()->defaultValue(),
        ];
        
        if ($this->type()) {
            $result->type = $this->type();
        }
        
        if ($this->pageNamespace()) {
            $result->pageNamespace = $this->pageNamespace();
        }
        
        return $result;
    }
    
    
    /**
     * @inheritcDoc
     */
    public function type(): ?string
    {
        return 'googlemaps';
    }
}