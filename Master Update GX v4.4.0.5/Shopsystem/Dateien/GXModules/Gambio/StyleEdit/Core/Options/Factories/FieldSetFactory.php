<?php
/*--------------------------------------------------------------------------------------------------
    FieldSetFactory.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Options\Factories;

use Gambio\StyleEdit\Core\BuildStrategies\Exceptions\NotFoundException;
use Gambio\StyleEdit\Core\Components\Option\Entities\Option;
use Gambio\StyleEdit\Core\Components\Option\Entities\OptionCollection;
use Gambio\StyleEdit\Core\Options\Entities\FieldSet;
use Gambio\StyleEdit\Core\Repositories\SettingsRepository;
use Gambio\StyleEdit\Core\SingletonPrototype;
use ReflectionException;
use stdClass;
use Exception;

/**
 * Class FieldSetFactory
 * @package Gambio\StyleEdit\Core\Options\Factories
 */
class FieldSetFactory
{

    /**
     * @param stdClass $object
     * @return FieldSet
     * @throws ReflectionException
     */
    public function createFromJsonObject(stdClass $object): FieldSet
    {
        return $this->createFromJsonAndRepository($object, null);
    }


    /**
     * @param stdClass $object
     * @param SettingsRepository|null $repository
     *
     * @return FieldSet
     * @throws ReflectionException
     */
    public function createFromJsonAndRepository(stdClass $object, SettingsRepository $repository = null): FieldSet
    {
        $options = new OptionCollection([]);
        if (!isset($object->options) || !is_array($object->options)) {
            throw new \Exception('FieldSet must have a valid options list!');
        }

        foreach ($object->options as $jsonOption) {

            try {
                $optionFactoryNameName = ucfirst(str_replace('-', '',
                        ucwords($jsonOption->type, '-'))) . 'OptionFactory';
                $optionFactory = SingletonPrototype::instance()->get($optionFactoryNameName);
                $option = $optionFactory->createFromJson($jsonOption, '', $repository);
            } catch (NotFoundException $e) {
                $option = Option::createFromJsonObject($jsonOption, '', $repository);
            }
            $options->addItem($option);
        }
        
        return new FieldSet($object->id ?? null,
                            $object->title ?? null,
                            $object->type ?? null,
                            $object->basic ?? null,
                            $object->hidden ?? null,
                            $object->pageNamespace ?? null,
                            $options);
    }
}