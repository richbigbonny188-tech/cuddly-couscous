<?php
/*--------------------------------------------------------------------------------------------------
    FontGroupOption.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Components\FontGroup\Entities;

use Gambio\StyleEdit\Core\Components\ColorPicker\Entities\ColorPickerOption;
use Gambio\StyleEdit\Core\Options\Entities\AbstractComponentGroupOption;
use Gambio\StyleEdit\Core\Components\TextBox\Entities\TextBox;
use Gambio\StyleEdit\Core\SingletonPrototype;
use Gambio\StyleEdit\Core\Components\DropdownSelect\Entities\DropdownSelectOption;
use Gambio\StyleEdit\Core\Components\Checkbox\Entities\CheckboxOption;
use stdClass;

/**
 * Class FontGroupOption
 * @package Gambio\StyleEdit\Core\Components\FontGroup\Entities
 */
class FontGroupOption extends AbstractComponentGroupOption
{
    /**
     * @var DropdownSelectOption
     */
    private $textAlign;
    
    /**
     * @var TextBox
     */
    private $family;
    
    /**
     * @var TextBox
     */
    private $size;
    
    /**
     * @var CheckboxOption
     */
    private $textDecorationUnderline;
    
    /**
     * @var CheckboxOption
     */
    private $textTransformUppercase;
    
    /**
     * @var DropdownSelectOption
     */
    private $style;
    
    /**
     * @var ColorpickerOption
     */
    private $color;
    
    /**
     * @var CheckboxOption
     */
    protected $enableCustomization;
    
    
    /**
     * FontGroupOption constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->family                  = SingletonPrototype::instance()->get('TextOption');
        $this->size                    = SingletonPrototype::instance()->get('TextOption');
        $this->style                   = SingletonPrototype::instance()->get('DropdownSelectOption');
        $this->textAlign               = SingletonPrototype::instance()->get('DropdownSelectOption');
        $this->textDecorationUnderline = SingletonPrototype::instance()->get('CheckboxOption');
        $this->textTransformUppercase  = SingletonPrototype::instance()->get('CheckboxOption');
        $this->enableCustomization     = SingletonPrototype::instance()->get('CheckboxOption');
        $this->color                   = SingletonPrototype::instance()->get('ColorPickerOption');
    }
    
    
    public function __clone()
    {
        parent::__clone();
        
        $this->family                  = clone $this->family;
        $this->size                    = clone $this->size;
        $this->style                   = clone $this->style;
        $this->textAlign               = clone $this->textAlign;
        $this->textDecorationUnderline = clone $this->textDecorationUnderline;
        $this->enableCustomization     = clone $this->enableCustomization;
        $this->textTransformUppercase  = clone $this->textTransformUppercase;
        $this->color                   = clone $this->color;
    }
    
    
    /**
     * @return DropdownSelectOption
     */
    public function textAlign(): DropdownSelectOption
    {
        return $this->textAlign;
    }
    
    
    /**
     * @return TextBox
     */
    public function family(): TextBox
    {
        return $this->family;
    }
    
    
    /**
     * @return TextBox
     */
    public function size(): TextBox
    {
        return $this->size;
    }
    
    
    /**
     * @return CheckboxOption
     */
    public function textDecorationUnderline(): CheckboxOption
    {
        return $this->textDecorationUnderline;
    }
    
    
    /**
     * @return CheckboxOption
     */
    public function textTransformUppercase(): CheckboxOption
    {
        return $this->textTransformUppercase;
    }
    
    
    /**
     * @return DropdownSelectOption
     */
    public function style(): DropdownSelectOption
    {
        return $this->style;
    }
    
    
    /**
     * @return ColorPickerOption
     */
    public function color(): ColorPickerOption
    {
        return $this->color;
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
        $result        = new stdClass;
        $result->id    = $this->id();
        $result->type  = 'font';
        $result->label = $this->label();
    
        if ($this->pageNamespace()) {
            $result->pageNamespace = $this->pageNamespace();
        }
        
        $result->items   = [
            'family'                  => $this->family()->value(),
            'color'                   => $this->color()->value(),
            'size'                    => $this->size()->value(),
            'style'                   => $this->style()->value(),
            'textAlign'               => $this->textAlign()->value(),
            'textDecorationUnderline' => (bool)$this->textDecorationUnderline()->value(),
            'textTransformUppercase'  => (bool)$this->textTransformUppercase()->value(),
            'enableCustomization'     => (bool)$this->enableCustomization()->value(),
        ];
        $result->default = [
            'family'                  => $this->family()->defaultValue(),
            'color'                   => $this->color()->defaultValue(),
            'size'                    => $this->size()->defaultValue(),
            'style'                   => $this->style()->defaultValue(),
            'textAlign'               => $this->textAlign()->defaultValue(),
            'textDecorationUnderline' => (bool)$this->textDecorationUnderline()->defaultValue(),
            'textTransformUppercase'  => (bool)$this->textTransformUppercase()->defaultValue(),
            'enableCustomization'     => (bool)$this->enableCustomization()->defaultValue()
        ];
        
        return $result;
    }
    
    
    /**
     * @return CheckboxOption
     */
    public function enableCustomization(): CheckboxOption
    {
        return $this->enableCustomization;
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
        
        $settings = ['id' => $this->id() . '-family'];
        if (isset($object->default->family)) {
            $settings['default'] = $object->default->family;
        }
        if (isset($object->items->family)) {
            $settings['value'] = $object->items->family;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        
        $this->family()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-size'];
        if (isset($object->default->size)) {
            $settings['default'] = $object->default->size;
        }
        if (isset($object->items->size)) {
            $settings['value'] = $object->items->size;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        
        $this->size()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
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
        
        $settings = ['id' => $this->id() . '-text-align'];
        if (isset($object->default->textAlign)) {
            $settings['default'] = $object->default->textAlign;
        }
        if (isset($object->items->textAlign)) {
            $settings['value'] = $object->items->textAlign;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->textAlign()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-style'];
        if (isset($object->default->style)) {
            $settings['default'] = $object->default->style;
        }
        if (isset($object->items->style)) {
            $settings['value'] = $object->items->style;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->style()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-text-decoration-underline'];
        if (isset($object->default->textDecorationUnderline)) {
            $settings['default'] = $object->default->textDecorationUnderline;
        }
        if (isset($object->items->textDecorationUnderline)) {
            $settings['value'] = $object->items->textDecorationUnderline;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->textDecorationUnderline()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
    
        $settings = ['id' => $this->id() . '-enable-customization'];
        if (isset($object->default->enableCustomization)) {
            $settings['default'] = $object->default->enableCustomization;
        }
        if (isset($object->items->enableCustomization)) {
            $settings['value'] = $object->items->enableCustomization;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->enableCustomization()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
        
        $settings = ['id' => $this->id() . '-text-transform-uppercase'];
        if (isset($object->default->textTransformUppercase)) {
            $settings['default'] = $object->default->textTransformUppercase;
        }
        if (isset($object->items->textTransformUppercase)) {
            $settings['value'] = $object->items->textTransformUppercase;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->textTransformUppercase()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
    }
    
    
    /**
     * @return mixed
     */
    public function getGroupOptions()
    {
        return [
            $this->family(),
            $this->color(),
            $this->size(),
            $this->style(),
            $this->textAlign(),
            $this->enableCustomization(),
            $this->textDecorationUnderline(),
            $this->textTransformUppercase()
        ];
    }
    
    
    /**
     * @return string
     */
    public function type(): ?string
    {
        return 'font';
    }
}
