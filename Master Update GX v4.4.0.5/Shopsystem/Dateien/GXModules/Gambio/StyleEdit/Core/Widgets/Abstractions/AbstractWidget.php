<?php
/* --------------------------------------------------------------
  AbstractWidget.php 2019-10-24
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Widgets\Abstractions;

use Gambio\StyleEdit\Core\Components\TextBox\Entities\TextBox;
use Gambio\StyleEdit\Core\Language\Entities\Language;
use Gambio\StyleEdit\Core\Options\Entities\FieldSet;
use Gambio\StyleEdit\Core\Options\Factories\FieldSetFactory;
use Gambio\StyleEdit\Core\SingletonPrototype;
use Gambio\StyleEdit\Core\Widgets\Abstractions\Interfaces\ContentGeneratorInterface;
use Gambio\StyleEdit\Core\Components\ContentZone\Interfaces\UpdatableContentZoneContentInterface;
use Gambio\StyleEdit\Core\Widgets\Abstractions\Interfaces\PersistableContentInterface;
use InvalidArgumentException;
use stdClass;

/**
 * Class AbstractWidget
 */
abstract class AbstractWidget
    implements ContentGeneratorInterface, PersistableContentInterface, UpdatableContentZoneContentInterface
{
    /**
     * @var string
     */
    protected $static_id;
    
    /**
     * ID
     *
     * @var TextBox
     */
    protected $id;
    
    /**
     * @var stdClass
     */
    protected $jsonObject;
    
    /**
     * @var FieldSet[]
     */
    protected $fieldsets = [];
    
    
    /**
     * AbstractWidget constructor.
     *
     * @param string     $static_id
     * @param FieldSet[] $fieldsets
     * @param stdClass   $jsonObject
     *
     * @throws \Exception
     */
    public function __construct(string $static_id, Array $fieldsets, stdClass $jsonObject)
    {
        $this->fieldsets  = $fieldsets;
        $this->jsonObject = $jsonObject;
        $this->static_id  = $static_id;
        $this->initializePropertiesFromFieldset();
    }
    
    
    /**
     * list all the options of a widget as $option->
     *
     * @param stdClass $widget
     *
     * @return array
     */
    protected static function createOptionsArray(stdClass $widget)
    {
        $result = [];
        foreach ($widget->fieldsets as $fieldset) {
            foreach ($fieldset->options as $option) {
                $result[$option->id] = $option;
            }
        }
        
        return $result;
    }
    
    
    /**
     * @return void
     * @throws \Exception
     */
    protected function initializePropertiesFromFieldset() : void
    {
        foreach ($this->fieldsets as $fieldset) {
            foreach ($fieldset->options() as $option) {
                if (property_exists($this, $option->id())) {
                    $this->{$option->id()} = $option;
                } else {
                    throw new \Exception("invalid Option {$option->id()}");
                }
            }
        }
    }
    
    
    /**
     * Used to provide specific validation criteria for passed object before creation
     *
     * @param stdClass $jsonObject
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected static function validateJsonObject(stdClass $jsonObject) : bool
    {
        if (!isset($jsonObject->id)) {
            throw new InvalidArgumentException('JSON object is missing an id');
        }
        
        return true;
    }
    
    
    /**
     * @param stdClass $jsonObject
     * @param array    $fieldSets
     *
     * @return static
     * @throws \Exception
     */
    protected static function createWidgetObject(stdClass $jsonObject, $fieldSets = [])
    {
        return new static($jsonObject->id, $fieldSets, $jsonObject);
    }
    
    
    /**
     *
     */
    public function persist() : void
    {
        //
    }
    
    
    /**
     * @param Language|null $currentLanguage
     *
     * @return string
     */
    abstract public function htmlContent(?Language $currentLanguage) : string;
    
    
    /**
     * @param stdClass $jsonObject
     *
     * @return ContentGeneratorInterface
     *
     * @throws \Exception
     * @throws \ReflectionException
     */
    public static function createFromJsonObject(stdClass $jsonObject) : ContentGeneratorInterface
    {
        static::validateJsonObject($jsonObject);
        /**
         * @var FieldSetFactory $fieldSetFactory
         */
        $fieldSetFactory = SingletonPrototype::instance()->get(FieldSetFactory::class);
        $fieldSets = [];
        
        foreach ($jsonObject->fieldsets as $fieldset) {
            $fieldSets[] = $fieldSetFactory->createFromJsonObject($fieldset);
        }
        
        return static::createWidgetObject($jsonObject, $fieldSets);
    }
    
    
    /**
     * @param Language|null $currentLanguage
     *
     * @return string
     */
    public function previewContent(?Language $currentLanguage) : string
    {
        return $this->htmlContent($currentLanguage);
    }
    
    
    public function update() : void
    {
        //
    }
}