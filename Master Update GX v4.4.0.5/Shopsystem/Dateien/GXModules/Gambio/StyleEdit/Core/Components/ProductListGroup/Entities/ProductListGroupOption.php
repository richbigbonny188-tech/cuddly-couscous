<?php
/*--------------------------------------------------------------------------------------------------
    ProductListGroupOption.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Components\ProductListGroup\Entities;

use Gambio\StyleEdit\Core\Components\Checkbox\Entities\CheckboxOption;
use Gambio\StyleEdit\Core\Components\DropdownSelect\Entities\DropdownSelectOption;
use Gambio\StyleEdit\Core\Components\Option\Entities\Option;
use Gambio\StyleEdit\Core\Components\CategorySearchBox\Entities\CategorySearchBoxOption;
use Gambio\StyleEdit\Core\Components\ProductSearchBox\Entities\ProductSearchBoxOption;
use Gambio\StyleEdit\Core\Options\Entities\AbstractComponentGroupOption;
use Gambio\StyleEdit\Core\SingletonPrototype;
use stdClass;

class ProductListGroupOption extends AbstractComponentGroupOption
{
    /**
     * @var Option
     */
    protected $listType;
    
    /**
     * @var Option
     */
    protected $categorySearchBox;
    
    /**
     * @var Option
     */
    protected $productSearchBox;
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->listType          = SingletonPrototype::instance()->get('DropdownSelectOption');
        $this->categorySearchBox = SingletonPrototype::instance()->get('CategorysearchboxOption');
        $this->productSearchBox  = SingletonPrototype::instance()->get('ProductsearchboxOption');
    }
    
    
    /**
     * clone inner objects
     */
    public function __clone()
    {
        parent::__clone();
        
        $this->listType          = clone $this->listType;
        $this->categorySearchBox = clone $this->categorySearchBox;
        $this->productSearchBox  = clone $this->productSearchBox;
    }
    
    
    /**
     * @return DropdownSelectOption
     */
    public function listType(): DropdownSelectOption
    {
        return $this->listType;
    }
    
    
    /**
     * @return CategorySearchBoxOption
     */
    public function categorySearchBox(): CategorySearchBoxOption
    {
        return $this->categorySearchBox;
    }
    
    
    /**
     * @return ProductSearchBoxOption
     */
    public function productSearchBox(): ProductSearchBoxOption
    {
        return $this->productSearchBox;
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
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $result        = new stdClass();
        $result->id    = $this->id();
        $result->type  = 'productlist';
        $result->label = $this->label();
    
        if ($this->pageNamespace()) {
            $result->pageNamespace = $this->pageNamespace();
        }
        
        $listType = json_decode(json_encode($this->listType()));
        $products = json_decode(json_encode($this->productSearchBox()));
        $category = json_decode(json_encode($this->categorySearchBox()));
        
        $result->items   = [
            'listType' => $listType,
            'products' => $products,
            'category' => $category
        ];
        $result->default = [
            'listType' => $this->listType()->defaultValue(),
            'products' => $this->productSearchBox()->defaultValue(),
            'category' => $this->categorySearchBox()->defaultValue()
        ];
        
        return $result;
    }
    
    
    /**
     * @param $object
     *
     * @throws \Exception
     */
    public function initializeFromJsonObject($object): void
    {
        if (isset($object->id)) {
            $this->id = $object->id;
        }
        
        if (isset($object->label)) {
            $this->label = $object->label;
        }
        if (isset($object->pageNamespace)) {
            $this->pageNamespace = $object->pageNamespace;
        }

        $settings = ['id' => $this->id() . '-listType'];
        if (isset($object->default->listType)) {
            $settings['default'] = $object->default->listType;
        }
        if (isset($object->items->listType->options)) {
            $settings['options'] = $object->items->listType->options;
        }
        if (isset($object->items->listType->attributes)) {
            $settings['attributes'] = $object->items->listType->attributes;
        }
        if (isset($object->items->listType->label)) {
            $settings['label'] = $object->items->listType->label;
        }
        if (isset($object->items->listType->value)) {
            $settings['value'] = $object->items->listType->value;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->listType()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-products', 'default' => []];
        if (isset($object->items->products->default)) {
            $settings['default'] = $object->items->products->default;
        }
        if (isset($object->items->products->attributes)) {
            $settings['attributes'] = $object->items->products->attributes;
        }
        if (isset($object->items->products->label)) {
            $settings['label'] = $object->items->products->label;
        }
        if (isset($object->items->products->value)) {
            $settings['value'] = $object->items->products->value;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->productSearchBox()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
        
        $settings = ['id' => $this->id() . '-category'];
        if (isset($object->items->category->default)) {
            $settings['default'] = $object->items->category->default;
        }
        if (isset($object->items->category->attributes)) {
            $settings['attributes'] = $object->items->category->attributes;
        }
        if (isset($object->items->category->label)) {
            $settings['label'] = $object->items->category->label;
        }
        if (isset($object->items->category->value)) {
            $settings['value'] = $object->items->category->value;
        }
        if (isset($object->for)) {
            $settings['for'] = $object->for;
        }
        $this->categorySearchBox()
            ->withConfigurationRepository($this->configurationRepository())
            ->initializeFromJsonObject((object)$settings);
    }
    
    
    /**
     * @return mixed
     */
    public function getGroupOptions()
    {
        return [
            $this->listType(),
            $this->productSearchBox(),
            $this->categorySearchBox(),
        ];
    }
    
    
    /**
     * @return string
     */
    public function type(): ?string
    {
        return 'productlist';
    }
}