<?php
/*--------------------------------------------------------------------------------------------------
    BackgroundImageGroupOption.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Components\BackgroundImageGroup\Entities;

use Gambio\StyleEdit\Core\Components\Checkbox\Entities\CheckboxOption;
use Gambio\StyleEdit\Core\Components\DropdownSelect\Entities\DropdownSelectOption;
use Gambio\StyleEdit\Core\Components\Option\Entities\Option;
use Gambio\StyleEdit\Core\Components\Url\Entities\UrlOption;
use Gambio\StyleEdit\Core\Options\Entities\AbstractComponentGroupOption;
use Gambio\StyleEdit\Core\SingletonPrototype;
use stdClass;

/**
 * Class BackgroundImageGroupOption
 * @package Gambio\StyleEdit\Core\Components\BackgroundImageGroup\Entities
 */
class BackgroundImageGroupOption extends AbstractComponentGroupOption
{
    /**
     * @var CheckboxOption
     */
    protected $enabled;
    
    /**
     * @var UrlOption
     */
    protected $url;
    
    /**
     * @var DropdownSelectOption
     */
    protected $position;
    
    /**
     * @var DropdownSelectOption
     */
    protected $repeat;
    
    /**
     * @var DropdownSelectOption
     */
    protected $size;
    
    /**
     * @var DropdownSelectOption
     */
    protected $attachment;
    
    
    /**
     * BackgroundImageGroupOption constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->enabled    = SingletonPrototype::instance()->get('CheckboxOption');
        $this->url        = SingletonPrototype::instance()->get('UrlOption');
        $this->position   = SingletonPrototype::instance()->get('DropdownSelectOption');
        $this->repeat     = SingletonPrototype::instance()->get('DropdownSelectOption');
        $this->size       = SingletonPrototype::instance()->get('DropdownSelectOption');
        $this->attachment = SingletonPrototype::instance()->get('DropdownSelectOption');
    }
    
    
    /**
     * clone inner objects
     */
    public function __clone()
    {
        parent::__clone();
        
        $this->enabled    = clone $this->enabled;
        $this->url        = clone $this->url;
        $this->position   = clone $this->position;
        $this->repeat     = clone $this->repeat;
        $this->size       = clone $this->size;
        $this->attachment = clone $this->attachment;
    }
    
    
    /**
     * @return CheckboxOption
     */
    public function enabled(): CheckboxOption
    {
        return $this->enabled;
    }
    
    
    /**
     * @return UrlOption
     */
    public function url(): UrlOption
    {
        return $this->url;
    }
    
    
    /**
     * @return DropdownSelectOption
     */
    public function position(): DropdownSelectOption
    {
        return $this->position;
    }
    
    
    /**
     * @return DropdownSelectOption
     */
    public function repeat(): DropdownSelectOption
    {
        return $this->repeat;
    }
    
    
    /**
     * @return DropdownSelectOption
     */
    public function size(): DropdownSelectOption
    {
        return $this->size;
    }
    
    
    /**
     * @return DropdownSelectOption
     */
    public function attachment(): DropdownSelectOption
    {
        return $this->attachment;
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
    
        if ($this->pageNamespace()) {
            $result->pageNamespace = $this->pageNamespace();
        }
        
        $result->items   = [
            'enabled'    => (bool)$this->enabled()->value(),
            'url'        => $this->url()->value(),
            'position'   => $this->position()->value(),
            'repeat'     => $this->repeat()->value(),
            'size'       => $this->size()->value(),
            'attachment' => $this->attachment()->value()
        ];
        $result->default = [
            'enabled'    => (bool)$this->enabled()->defaultValue(),
            'url'        => $this->url()->defaultValue(),
            'position'   => $this->position()->defaultValue(),
            'repeat'     => $this->repeat()->defaultValue(),
            'size'       => $this->size()->defaultValue(),
            'attachment' => $this->attachment()->defaultValue()
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
        if (isset($object->default->enabled)) {
            $settings['default'] = $object->default->enabled;
        }
        if (isset($object->items->enabled)) {
            $settings['value'] = $object->items->enabled;
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
        
        $settings = ['id' => $this->id() . '-url'];
        if (isset($object->default->url)) {
            $settings['default'] = $object->default->url;
        }
        if (isset($object->items->url)) {
            $settings['value'] = $object->items->url;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->url()
            ->withConfigurationRepository($this->configurationRepository)
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-position'];
        if (isset($object->default->position)) {
            $settings['default'] = $object->default->position;
        }
        if (isset($object->items->position)) {
            $settings['value'] = $object->items->position;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->position()
            ->withConfigurationRepository($this->configurationRepository)
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-repeat'];
        if (isset($object->default->repeat)) {
            $settings['default'] = $object->default->repeat;
        }
        if (isset($object->items->repeat)) {
            $settings['value'] = $object->items->repeat;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->repeat()
            ->withConfigurationRepository($this->configurationRepository)
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
            ->withConfigurationRepository($this->configurationRepository)
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-attachment'];
        if (isset($object->default->attachment)) {
            $settings['default'] = $object->default->attachment;
        }
        if (isset($object->items->attachment)) {
            $settings['value'] = $object->items->attachment;
        }
        if (isset($object->group)) {
            $settings['group'] = $object->group;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->attachment()
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
            $this->url(),
            $this->position(),
            $this->repeat(),
            $this->size(),
            $this->attachment()
        ];
    }
    
    
    /**
     * @return string
     */
    public function type(): ?string
    {
        return 'background-image';
    }
}
