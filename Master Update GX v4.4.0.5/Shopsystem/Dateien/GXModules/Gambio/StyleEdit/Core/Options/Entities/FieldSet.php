<?php
/*--------------------------------------------------------------------------------------------------
    FieldSet.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Options\Entities;

use Exception;
use Gambio\StyleEdit\Core\Components\Option\Entities\Option;
use Gambio\StyleEdit\Core\Components\Option\Entities\OptionCollection;
use Gambio\StyleEdit\Core\Repositories\SettingsRepository;
use Gambio\StyleEdit\Core\SingletonPrototype;

/**
 * Class FieldSet
 * @package Gambio\StyleEdit\Core\Components\Entities
 */
class FieldSet extends AbstractConfigurationType
{

    /**
     * @var SettingsRepository
     */
    protected $configurationRepository;
    /**
     * @var OptionCollection
     *
     */
    protected $options;


    /**
     * FieldSet constructor.
     *
     * @param string $id
     * @param string $title
     * @param string $type
     * @param string $basic
     * @param bool $hidden
     * @param string|null $pageNamespace
     * @param OptionCollection $options
     */
    public function __construct(
        string $id = null,
        string $title = null,
        string $type = null,
        string $basic = null,
        bool $hidden = null,
        string $pageNamespace = null,
        OptionCollection $options = null
    ) {
        parent::__construct($id, $title, $type, $basic, $hidden, $pageNamespace);
        $this->options = $options ?? new OptionCollection([]);
    }


    /**
     * @param                              $jsonFieldSet
     *
     * @param SettingsRepository|null $configurationRepository
     *
     * @return bool|mixed
     * @throws Exception
     */
    public static function createFromJsonObject($jsonFieldSet, SettingsRepository $configurationRepository = null)
    {
        $result = SingletonPrototype::instance()->get(FieldSet::class);
        if (!$result instanceof self) {
            throw new Exception('FieldSet instance must inherit ' . self::class . '!');
        }
        $result->configurationRepository = $configurationRepository;
        $result->initializeFromJsonObject($jsonFieldSet);

        return $result;
    }
    
    
    /**
     * @param $object
     *
     * @throws Exception
     */
    public function initializeFromJsonObject($object): void
    {
        parent::initializeFromJsonObject($object);
        if (!isset($object->options) || !is_array($object->options)) {
            throw new Exception('FieldSet must have a valid options list!');
        }
        foreach ($object->options as $option) {
            $this->options->addItem(Option::createFromJsonObject($option, '', $this->configurationRepository));
        }
    }
    
    
    /**
     * @return OptionCollection
     */
    public function options(): OptionCollection
    {
        return $this->options;
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
        $result = [];
        if ($this->id()) {
            $result['id'] = $this->id();
        }
    
        if ($this->title()) {
            $result['title'] = $this->title();
        }
        
        if ($this->pageNamespace()) {
            $result['pageNamespace'] = $this->pageNamespace();
        }

        if ($this->type()) {
            $result['type'] = $this->type();
        }
        
        if (count($this->options)) {
            $result['options'] = [];
            foreach ($this->options as $option) {
                $result['options'][] = $option;
            }
        }
        
        if ($this->hidden()) {
            $result['hidden'] = $this->hidden();
        }
        
        return (object)$result;
    }
    
    
    public function __clone()
    {
        $this->options = clone $this->options;
    }
}
