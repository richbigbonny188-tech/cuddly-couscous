<?php
/*--------------------------------------------------------------------------------------------------
    MarginGroupOption.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Components\MarginGroup\Entities;

use Exception;
use Gambio\StyleEdit\Core\Components\TextBox\Entities\TextBox;
use Gambio\StyleEdit\Core\Options\Entities\AbstractComponentGroupOption;
use Gambio\StyleEdit\Core\SingletonPrototype;
use stdClass;

/**
 * Class MarginGroupOption
 * @package Gambio\StyleEdit\Core\Components\MarginGroup\Entities
 */
class MarginGroupOption extends AbstractComponentGroupOption
{
    /**
     * @var TextBox
     */
    protected $marginTop;
    
    /**
     * @var TextBox
     */
    protected $marginRight;
    
    /**
     * @var TextBox
     */
    protected $marginBottom;
    
    /**
     * @var TextBox
     */
    protected $marginLeft;
    
    
    /**
     * MarginGroupOption constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->marginTop    = SingletonPrototype::instance()->get('TextOption');
        $this->marginRight  = SingletonPrototype::instance()->get('TextOption');
        $this->marginBottom = SingletonPrototype::instance()->get('TextOption');
        $this->marginLeft   = SingletonPrototype::instance()->get('TextOption');
    }
    
    
    public function __clone()
    {
        parent::__clone();
        
        $this->marginTop    = clone $this->marginTop;
        $this->marginRight  = clone $this->marginRight;
        $this->marginBottom = clone $this->marginBottom;
        $this->marginLeft   = clone $this->marginLeft;
    }
    
    
    /**
     * @return TextBox
     */
    public function marginTop(): TextBox
    {
        return $this->marginTop;
    }
    
    
    /**
     * @return TextBox
     */
    public function marginRight(): TextBox
    {
        return $this->marginRight;
    }
    
    
    /**
     * @return TextBox
     */
    public function marginBottom(): TextBox
    {
        return $this->marginBottom;
    }
    
    
    /**
     * @return TextBox
     */
    public function marginLeft(): TextBox
    {
        return $this->marginLeft;
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
        $result->type    = 'margin';
        $result->label   = $this->label();
    
        if ($this->pageNamespace()) {
            $result->pageNamespace = $this->pageNamespace();
        }
        
        $result->items   = [
            'top'    => $this->marginTop()->value(),
            'right'  => $this->marginRight()->value(),
            'bottom' => $this->marginBottom()->value(),
            'left'   => $this->marginLeft()->value()
        ];
        $result->default = [
            'top'    => $this->marginTop()->defaultValue(),
            'right'  => $this->marginRight()->defaultValue(),
            'bottom' => $this->marginBottom()->defaultValue(),
            'left'   => $this->marginLeft()->defaultValue()
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
     * @throws Exception
     */
    public function initializeFromJsonObject($object): void
    {
        if (empty($object->items)) {
            $object->items = $object->value->distances ?? null;
            unset($object->value);
        }
        
        if (isset($object->id)) {
            $this->id = $object->id;
        }
        
        if (isset($object->label)) {
            $this->label = $object->label;
        }

        if (isset($object->pageNamespace)) {
            $this->pageNamespace = $object->pageNamespace;
        }
        
        $subObject = ['id' => $this->id() . '-top'];
        if (isset($object->default->{'top'})) {
            $subObject['default'] = $object->default->{'top'};
        }
        if (isset($object->items->{'top'})) {
            $subObject['value'] = $object->items->{'top'};
        }
        if (isset($object->type)) {
            $subObject['type'] = $object->type;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->marginTop()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$subObject);
        
        $subObject = ['id' => $this->id() . '-right'];
        if (isset($object->default->{'right'})) {
            $subObject['default'] = $object->default->{'right'};
        }
        if (isset($object->items->{'right'})) {
            $subObject['value'] = $object->items->{'right'};
        }
        if (isset($object->type)) {
            $subObject['type'] = $object->type;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->marginRight()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$subObject);
        
        $subObject = ['id' => $this->id() . '-bottom'];
        if (isset($object->default->{'bottom'})) {
            $subObject['default'] = $object->default->{'bottom'};
        }
        if (isset($object->items->{'bottom'})) {
            $subObject['value'] = $object->items->{'bottom'};
        }
        if (isset($object->type)) {
            $subObject['type'] = $object->type;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->marginBottom()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$subObject);
        
        $subObject = ['id' => $this->id() . '-left'];
        if (isset($object->default->{'left'})) {
            $subObject['default'] = $object->default->{'left'};
        }
        if (isset($object->items->{'left'})) {
            $subObject['value'] = $object->items->{'left'};
        }
        if (isset($object->type)) {
            $subObject['type'] = $object->type;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->marginLeft()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$subObject);
    }
    
    
    /**
     * @return array|mixed
     */
    public function getGroupOptions()
    {
        return [
            $this->marginTop(),
            $this->marginBottom(),
            $this->marginLeft(),
            $this->marginRight()
        ];
    }
    
    
    /**
     * @return string
     */
    public function type(): ?string
    {
        return 'margin';
    }
}
