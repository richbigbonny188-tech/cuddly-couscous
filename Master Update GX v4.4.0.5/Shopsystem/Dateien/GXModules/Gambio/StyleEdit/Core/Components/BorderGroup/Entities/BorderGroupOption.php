<?php
/*--------------------------------------------------------------------------------------------------
    BorderGroupOption.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Components\BorderGroup\Entities;

use Gambio\StyleEdit\Core\Components\ColorPicker\Entities\ColorPickerOption;
use Gambio\StyleEdit\Core\Components\TextBox\Entities\TextBox;
use Gambio\StyleEdit\Core\Components\DropdownSelect\Entities\DropdownSelectOption;
use Gambio\StyleEdit\Core\Options\Entities\AbstractComponentGroupOption;
use Gambio\StyleEdit\Core\SingletonPrototype;

use stdClass;

/**
 * Class BorderGroupOption
 */
class BorderGroupOption extends AbstractComponentGroupOption
{
    /**
     * @var ColorPickerOption
     */
    private $color;
    
    /**
     * @var TextBox
     */
    private $top;
    
    /**
     * @var TextBox
     */
    private $right;
    
    /**
     * @var TextBox
     */
    private $bottom;
    
    /**
     * @var TextBox
     */
    private $left;
    
    /**
     * @var DropdownSelectOption
     */
    private $style;
    
    
    /**
     * BorderGroupOption constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->top    = SingletonPrototype::instance()->get('TextOption');
        $this->right  = SingletonPrototype::instance()->get('TextOption');
        $this->bottom = SingletonPrototype::instance()->get('TextOption');
        $this->left   = SingletonPrototype::instance()->get('TextOption');
        $this->style  = SingletonPrototype::instance()->get('DropdownSelectOption');
        $this->color  = SingletonPrototype::instance()->get('ColorPickerOption');
    }
    
    
    /**
     * clone inner objects
     */
    public function __clone()
    {
        parent::__clone();
        
        $this->top    = clone $this->top;
        $this->right  = clone $this->right;
        $this->bottom = clone $this->bottom;
        $this->left   = clone $this->left;
        $this->style  = clone $this->style;
        $this->color  = clone $this->color;
    }
    
    
    /**
     * @return ColorPickerOption
     */
    public function color(): ColorPickerOption
    {
        return $this->color;
    }
    
    
    /**
     * @return TextBox
     */
    public function top(): TextBox
    {
        return $this->top;
    }
    
    
    /**
     * @return TextBox
     */
    public function right(): TextBox
    {
        return $this->right;
    }
    
    
    /**
     * @return TextBox
     */
    public function bottom(): TextBox
    {
        return $this->bottom;
    }
    
    
    /**
     * @return TextBox
     */
    public function left(): TextBox
    {
        return $this->left;
    }
    
    
    /**
     * @return DropdownSelectOption
     */
    public function style(): DropdownSelectOption
    {
        return $this->style;
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
        $result          = new stdClass;
        $result->id      = $this->id();
        $result->type    = 'border';
        $result->label   = $this->label();
        
        if ($this->pageNamespace()) {
            $result->pageNamespace = $this->pageNamespace();
        }
        
        $result->items   = [
            'top'    => $this->top()->value(),
            'right'  => $this->right()->value(),
            'bottom' => $this->bottom()->value(),
            'left'   => $this->left()->value(),
            'style'  => $this->style()->value(),
            'color'  => $this->color()->value()
        ];
        $result->default = [
            'top'    => $this->top()->defaultValue(),
            'right'  => $this->right()->defaultValue(),
            'bottom' => $this->bottom()->defaultValue(),
            'left'   => $this->left()->defaultValue(),
            'style'  => $this->style()->defaultValue(),
            'color'  => $this->color()->defaultValue()
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
        
        if (empty($object->items)) {
            $object->items = $object->value->distances ?? null;
        }
        
        unset($object->value);
        
        if (isset($object->id)) {
            $this->id = $object->id;
        }
        
        if (isset($object->label)) {
            $this->label = $object->label;
        }
        
        $settings = ['id' => $this->id() . '-top'];
        if (isset($object->default->top)) {
            $settings['default'] = $object->default->top;
        }
        if (isset($object->items->top)) {
            $settings['value'] = $object->items->top;
        }
        if (isset($object->type)) {
            $settings['type'] = $object->type;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        
        $this->top()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-right'];
        if (isset($object->default->right)) {
            $settings['default'] = $object->default->right;
        }
        if (isset($object->items->right)) {
            $settings['value'] = $object->items->right;
        }
        if (isset($object->type)) {
            $settings['type'] = $object->type;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        
        $this->right()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-bottom'];
        if (isset($object->default->bottom)) {
            $settings['default'] = $object->default->bottom;
        }
        if (isset($object->items->bottom)) {
            $settings['value'] = $object->items->bottom;
        }
        if (isset($object->type)) {
            $settings['type'] = $object->type;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->bottom()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-left'];
        if (isset($object->default->left)) {
            $settings['default'] = $object->default->left;
        }
        if (isset($object->items->left)) {
            $settings['value'] = $object->items->left;
        }
        if (isset($object->type)) {
            $settings['type'] = $object->type;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->left()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-style'];
        if (isset($object->default->style)) {
            $settings['default'] = $object->default->style;
        }
        if (isset($object->items->style)) {
            $settings['value'] = $object->items->style;
        }
        if (isset($object->type)) {
            $settings['type'] = $object->type;
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
    }
    
    
    /**
     * @return mixed
     */
    public function getGroupOptions()
    {
        return [
            $this->style(),
            $this->color(),
            $this->top(),
            $this->bottom(),
            $this->left(),
            $this->right()
        ];
    }
    
    
    /**
     * @return string
     */
    public function type(): ?string
    {
        return 'border';
    }
}
