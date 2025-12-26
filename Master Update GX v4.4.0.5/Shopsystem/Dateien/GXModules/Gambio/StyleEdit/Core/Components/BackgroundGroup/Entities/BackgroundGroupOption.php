<?php
/*--------------------------------------------------------------------------------------------------
    BackgroundGroupOption.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Components\BackgroundGroup\Entities;

use Gambio\StyleEdit\Core\Components\BackgroundGradientGroup\Entities\BackgroundGradientGroupOption;
use Gambio\StyleEdit\Core\Components\BackgroundImageGroup\Entities\BackgroundImageGroupOption;
use Gambio\StyleEdit\Core\Components\ColorPicker\Entities\ColorPickerOption;
use Gambio\StyleEdit\Core\Options\Entities\AbstractComponentGroupOption;
use Gambio\StyleEdit\Core\SingletonPrototype;
use Gambio\StyleEdit\Core\Components\Option\Entities\Option;
use stdClass;

/**
 * Class BackgroundGradientGroupOption
 * @package Gambio\StyleEdit\Core\Components\BackgroundGradientGroupOption\Entities
 */
class BackgroundGroupOption extends AbstractComponentGroupOption
{
    /**
     * @var ColorPickerOption
     */
    protected $color;
    
    /**
     * @var BackgroundImageGroupOption
     */
    protected $image;
    
    /**
     * @var BackgroundGradientGroupOption
     */
    protected $gradient;
    
    
    /**
     * BackgroundGradientGroupOption constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->color    = SingletonPrototype::instance()->get('ColorPickerOption');
        $this->image    = SingletonPrototype::instance()->get('BackgroundImageOption');
        $this->gradient = SingletonPrototype::instance()->get('BackgroundGradientOption');
    }
    
    
    /**
     * clone inner objects
     */
    public function __clone()
    {
        parent::__clone();
        
        $this->color    = clone $this->color;
        $this->image    = clone $this->image;
        $this->gradient = clone $this->gradient;
    }
    
    
    /**
     * @return ColorPickerOption
     */
    public function color(): ColorPickerOption
    {
        return $this->color;
    }
    
    
    /**
     * @return BackgroundImageGroupOption
     */
    public function image(): BackgroundImageGroupOption
    {
        return $this->image;
    }
    
    
    /**
     * @return BackgroundGradientGroupOption
     */
    public function gradient(): BackgroundGradientGroupOption
    {
        return $this->gradient;
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
        $result          = new stdClass();
        $result->id      = $this->id();
        $result->type    = $this->type();
        $result->label   = $this->label();
        $image           = json_decode(json_encode($this->image()));
        $gradient        = json_decode(json_encode($this->gradient()));
        
        if ($this->pageNamespace()) {
            $result->pageNamespace = $this->pageNamespace();
        }
        
        $result->items   = [
            'color'    => $this->color()->value(),
            'image'    => $image->items,
            'gradient' => $gradient->items
        ];
        $result->default = [
            'color'    => $this->color()->defaultValue(),
            'image'    => $image->items,
            'gradient' => $gradient->items
        ];
        
        return $result;
    }
    
    
    /**
     * @param $value
     *
     * @return boolean
     */
    protected function isValid($value): bool
    {
        return true;
    }
    
    
    /**
     * @param $value
     *
     * @return mixed
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
        
        $settings = ['id' => $this->id() . '-color'];
        
        if (isset($object->default->color)) {
            $settings['default'] = $object->default->color;
        }
        if (isset($object->items->color)) {
            $settings['value'] = $object->items->color;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->color()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-image'];
        if (isset($object->default->image)) {
            $settings['default'] = $object->default->image;
        }
        if (isset($object->items->image)) {
            $settings['items'] = $object->items->image;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->image()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-gradient'];
        if (isset($object->default->gradient)) {
            $settings['default'] = $object->default->gradient;
        }
        if (isset($object->items->gradient)) {
            $settings['items'] = $object->items->gradient;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->gradient()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
    }
    
    
    /**
     * @return mixed
     */
    public function getGroupOptions()
    {
        $result = [
            $this->color()
        ];
        $result = array_merge($result, $this->gradient()->getGroupOptions());
        $result = array_merge($result, $this->image()->getGroupOptions());
        
        return $result;
    }
    
    
    /**
     * @return string
     */
    public function type(): ?string
    {
        return 'background';
    }
}
