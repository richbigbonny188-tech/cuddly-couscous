<?php
/*--------------------------------------------------------------------------------------------------
    VariantOption.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Components\Variant\Entities;

use Exception;
use Gambio\StyleEdit\Core\Language\Entities\Language;
use Gambio\StyleEdit\Core\Options\Entities\AbstractOption;

/**
 * Class Variant
 * @package Gambio\StyleEdit\Core\Components
 */
class VariantOption extends AbstractOption
{
    /**
     * @var string
     */
    protected $options;


    /**
     * @return string
     */
    public function options()
    {
        return $this->options;
    }

    /**
     * @return bool
     */
    public function requiresReload() : bool
    {
        return true;
    }


    /**
     * @return object
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        if ($this->options()) {
            $result->options = $this->options;
        }

        return (object)$result;
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
     * @param Language|null $language
     *
     * @return VariantValue
     */
    public function value(?Language $language = null): VariantValue
    {
        return parent::value();
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
        if (!isset($object->requiresReload)) {

            $object->requiresReload = true;
        }

        if (isset($object->pageNamespace)) {
            $this->pageNamespace = $object->pageNamespace;
        }

        parent::initializeFromJsonObject($object);

        if (isset($object->value)) {
            $this->value = VariantValue::createFromJsonObject($object->value);
        }
        if (isset($object->default)) {
            $this->default = VariantValue::createFromJsonObject($object->default);
        }

        if (!isset($object->id)) {
            throw new Exception('Id is a mandatory property for variant options');
        }

        if (isset($object->options)) {
            $this->options = $object->options;
        }
    }


    /**
     * @return string|null
     */
    public function group(): ?string
    {
        return 'variant';
    }


    /**
     * @return string
     */
    public function type(): ?string
    {
        return 'variant';
    }
}