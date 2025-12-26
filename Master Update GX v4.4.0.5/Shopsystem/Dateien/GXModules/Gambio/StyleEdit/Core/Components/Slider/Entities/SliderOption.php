<?php
/*--------------------------------------------------------------------------------------------------
    SliderOptionTest.php 2019-6-13
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\StyleEdit\Core\Components\Slider\Entities;

use Gambio\StyleEdit\Core\Options\Entities\AbstractComponentOption;
use Gambio\StyleEdit\Core\Options\Entities\ComponentOptionAttribute;

/**
 * Class SliderOption
 * @package Gambio\StyleEdit\Core\Components\Slider\Entities
 */
class SliderOption extends AbstractComponentOption
{
    
    /**
     * @param $value
     *
     * @return boolean
     */
    protected function isValid($value): bool
    {
        return is_numeric($value);
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
        
        if (isset($object->label)) {
            $this->label = $object->label;
        }
        
        if (isset($object->attributes)) {
            foreach ($object->attributes as $key => $attribute) {
                if ($key === 'id') {
                    throw new \InvalidArgumentException('Id attribute is not accepted!');
                }
                
                $this->attributes->setValue($key, ComponentOptionAttribute::create($key, $attribute));
            }
        }
    }
    
    
    /**
     * @return string
     */
    public function type(): ?string
    {
        return 'slider';
    }
}