<?php
/* --------------------------------------------------------------
   SettingsController.php 2019-04-15
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

namespace Gambio\StyleEdit\Core\Options\Entities;

use Exception;

/**
 * Class AbstractComponentOption
 * @package Gambio\StyleEdit\Core\Components\Entities\Options
 */
abstract class AbstractComponentOption extends AbstractOption
{
    /**
     * @var ComponentOptionAttributeCollection
     */
    protected $attributes;
    /**
     * @var string
     */
    protected $label;
    
    
    /**
     * AbstractComponentOption constructor.
     *
     * @param string                             $label
     * @param ComponentOptionAttributeCollection $attributes
     */
    public function __construct(string $label = null, ComponentOptionAttributeCollection $attributes = null)
    {
        parent::__construct();
        $this->label = $label;
        $this->attributes = $attributes ?? new ComponentOptionAttributeCollection([]);
    }
    
    
    /**
     * s     * Clone the object
     */
    public function __clone()
    {
        $this->attributes = clone $this->attributes;
    }
    
    
    /**
     * @return ComponentOptionAttributeCollection
     */
    public function attributes(): ComponentOptionAttributeCollection
    {
        return $this->attributes;
    }
    
    
    /**
     * @param $object
     *
     * @throws Exception
     */
    public function initializeFromJsonObject($object): void
    {
        parent::initializeFromJsonObject($object);
        
        if (isset($object->label)) {
            $this->label = $object->label;
        }
        
        if (isset($object->attributes)) {
            foreach ($object->attributes as $key => $attribute) {
                $this->attributes->setValue($key, ComponentOptionAttribute::create($key, $attribute));
            }
        }
    
        if (isset($object->value) && $this->isValid($object->value)) {
            $this->value = $this->parseValue($object->value);
        }
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
        $result = parent::jsonSerialize();
        
        if ($this->label()) {
            $result->label = $this->label();
        }
        if ($this->attributes->count()) {
            $result->attributes = $this->attributes;
        }
        
        return (object)$result;
    }
    
    
    /**
     * @return string
     */
    public function label(): ?string
    {
        return $this->label;
    }
    
}
