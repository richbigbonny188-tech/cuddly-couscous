<?php
/*--------------------------------------------------------------------------------------------------
    BackgroundGradientGroupOption.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Components\BackgroundGradientGroup\Entities;

use Gambio\StyleEdit\Core\Components\DropdownSelect\Entities\DropdownSelectOption;
use Gambio\StyleEdit\Core\Components\Option\Entities\Option;
use Gambio\StyleEdit\Core\Options\Entities\AbstractComponentGroupOption;
use Gambio\StyleEdit\Core\SingletonPrototype;
use stdClass;

/**
 * Class BackgroundGradientGroupOption
 * @package Gambio\StyleEdit\Core\Components\BackgroundGradientGroupOption\Entities
 */
class BackgroundGradientGroupOption extends AbstractComponentGroupOption
{
    /**
     * @var Option
     */
    protected $enabled;
    
    /**
     * @var Option
     */
    protected $color1;
    
    /**
     * @var Option
     */
    protected $color2;
    
    /**
     * @var Option
     */
    protected $gradientType;
    
    /**
     * @var DropdownSelectOption
     */
    protected $angle;
    
    
    /**
     * @return DropdownSelectOption
     */
    public function angle(): DropdownSelectOption
    {
        return $this->angle;
    }
    
    
    /**
     * BackgroundGradientGroupOption constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->enabled      = SingletonPrototype::instance()->get('CheckboxOption');
        $this->color1       = SingletonPrototype::instance()->get('ColorPickerOption');
        $this->color2       = SingletonPrototype::instance()->get('ColorPickerOption');
        $this->gradientType = SingletonPrototype::instance()->get('DropdownSelectOption');
        $this->angle        = SingletonPrototype::instance()->get('DropdownSelectOption');
    }
    
    
    /**
     * clone inner objects
     */
    public function __clone()
    {
        parent::__clone();
        
        $this->enabled      = clone $this->enabled;
        $this->color1       = clone $this->color1;
        $this->color2       = clone $this->color2;
        $this->gradientType = clone $this->gradientType;
        $this->angle        = clone $this->angle;
    }
    
    
    /**
     * @return mixed
     */
    public function enabled()
    {
        return $this->enabled;
    }
    
    
    /**
     * @return mixed
     */
    public function color1()
    {
        return $this->color1;
    }
    
    
    /**
     * @return mixed
     */
    public function color2()
    {
        return $this->color2;
    }
    
    
    /**
     * @return mixed
     */
    public function gradientType()
    {
        return $this->gradientType;
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
        $result->type    = $this->type();
        $result->label   = $this->label();
        $result->items   = [
            'enabled'      => (bool)$this->enabled()->value(),
            'color1'       => $this->color1()->value(),
            'color2'       => $this->color2()->value(),
            'gradientType' => $this->gradientType()->value(),
            'angle'        => $this->angle()->value()
        ];
        $result->default = [
            'enabled'      => (bool)$this->enabled()->defaultValue(),
            'color1'       => $this->color1()->defaultValue(),
            'color2'       => $this->color2()->defaultValue(),
            'gradientType' => $this->gradientType()->defaultValue(),
            'angle'        => $this->angle()->defaultValue()
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
        
        $settings = ['id' => $this->id() . '-enabled'];
        if (isset($object->default->{'enabled'})) {
            $settings['default'] = $object->default->{'enabled'};
        }
        if (isset($object->items->{'enabled'})) {
            $settings['value'] = $object->items->{'enabled'};
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->enabled()
            ->withConfigurationRepository($this->configurationRepository)
            ->initializeFromJsonObject((object)$settings);
        
        /* color 1*/
        $settings = ['id' => $this->id() . '-color1'];
        if (isset($object->default->{'color1'})) {
            $settings['default'] = $object->default->{'color1'};
        }
        if (isset($object->items->{'color1'})) {
            $settings['value'] = $object->items->{'color1'};
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->color1()
            ->withConfigurationRepository($this->configurationRepository)
            ->initializeFromJsonObject((object)$settings);
        
        /*color 2*/
        $settings = ['id' => $this->id() . '-color2'];
        if (isset($object->default->{'color1'})) {
            $settings['default'] = $object->default->color2;
        }
        if (isset($object->items->color2)) {
            $settings['value'] = $object->items->color2;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->color2()
            ->withConfigurationRepository($this->configurationRepository)
            ->initializeFromJsonObject((object)$settings);
        
        /* type */
        $settings = ['id' => $this->id() . '-type'];
        if (isset($object->default->gradientType)) {
            $settings['default'] = $object->default->gradientType;
        }
        if (isset($object->items->gradientType)) {
            $settings['value'] = $object->items->gradientType;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->gradientType()
            ->withConfigurationRepository($this->configurationRepository)
            ->initializeFromJsonObject((object)$settings);
        
        /* angle*/
        $settings = ['id' => $this->id() . '-angle'];
        if (isset($object->default->angle)) {
            $settings['default'] = $object->default->angle;
        }
        if (isset($object->items->angle)) {
            $settings['value'] = $object->items->angle;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->angle()
            ->withConfigurationRepository($this->configurationRepository)
            ->initializeFromJsonObject((object)$settings);
    }
    
    
    /**
     * @return mixed
     */
    public function getGroupOptions()
    {
        return [
            $this->enabled(),
            $this->color1(),
            $this->color2(),
            $this->gradientType(),
            $this->angle()
        ];
    }
    
    
    /**
     * @return string
     */
    public function type(): ?string
    {
        return 'background-gradient';
    }
}
