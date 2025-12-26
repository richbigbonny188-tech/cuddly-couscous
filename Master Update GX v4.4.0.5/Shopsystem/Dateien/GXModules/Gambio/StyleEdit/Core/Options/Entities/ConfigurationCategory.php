<?php
/*--------------------------------------------------------------------------------------------------
    ConfigurationCategory.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Options\Entities;

use Exception;

/**
 * Class Category
 * @package Gambio\StyleEdit\Core\Components\Entities
 */
class ConfigurationCategory extends AbstractConfigurationType
{
    /**
     * @var ConfigurationCategoryCollection
     */
    protected $categories;
    
    /**
     * @var FieldSetCollection
     */
    protected $fieldSets;
    /**
     * Categories which are only active for
     * a specific variant have this property
     *
     * @var string
     */
    protected $for;
    /**
     * @var string
     */
    protected $selector;


    /**
     * ConfigurationCategory constructor.
     *
     * @param string $id
     * @param string $title
     * @param string $type
     * @param string $basic
     * @param bool $hidden
     * @param string $for
     * @param string|null $selector
     * @param string|null $pageNamespace
     * @param ConfigurationCategoryCollection $categories
     * @param FieldSetCollection $fieldSets
     * @throws Exception
     */
    public function __construct(
        string $id = null,
        string $title = null,
        string $type = null,
        string $basic = null,
        bool $hidden = null,
        string $for = null,
        string $selector = null,
        string $pageNamespace = null,
        ConfigurationCategoryCollection $categories = null,
        FieldSetCollection $fieldSets = null
    ) {
        if (!isset($categories) && !isset($fieldSets)) {
            throw new Exception('Category must have FieldSets or Categories!');
        }
    
        if (isset($categories, $fieldSets)) {
            throw new Exception('Category must have FieldSets or Categories but not both!');
        }
    
        if (!isset($id)) {
            throw new Exception('Category must have an id!');
        }
        
        parent::__construct($id, $title, $type, $basic, $hidden, $pageNamespace);
        $this->categories = $categories;
        $this->fieldSets  = $fieldSets;
        $this->for        = $for;
        $this->selector   = $selector;
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
        if (isset($this->categories)) {
            $result['categories'] = $this->categories();
        }
        
        if (isset($this->fieldSets)) {
            $result['fieldsets'] = $this->fieldSets();
        }
        
        $result['id'] = $this->id();
        
        if ($this->type()) {
            $result['type'] = $this->type();
        }
        
        if ($this->basic()) {
            $result['basic'] = $this->basic();
        }
        
        if ($this->title()) {
            $result['title'] = $this->title();
        }
        
        if ($this->for() !== null) {
            
            $result['for'] = $this->for();
        }
        
        if ($this->hidden() !== null) {
            
            $result['hidden'] = $this->hidden();
        }
        
        if ($this->selector() !== null) {
            
            $result['selector'] = $this->selector();
        }

        if ($this->pageNamespace()) {
            $result['pageNamespace'] = $this->pageNamespace();
        }
        
        return (object)$result;
    }
    
    
    /**
     * @return ConfigurationCategoryCollection
     */
    public function categories(): ConfigurationCategoryCollection
    {
        return $this->categories;
    }
    
    
    /**
     * @return FieldSetCollection
     */
    public function fieldSets(): ?FieldSetCollection
    {
        return $this->fieldSets;
    }
    
    
    /**
     * @return string
     */
    public function for(): ?string
    {
        return $this->for;
    }
    
    
    /**
     * @return string
     */
    public function selector(): ?string
    {
        return $this->selector;
    }
    
    
    public function __clone()
    {
        if ($this->categories) {
            $this->categories = clone $this->categories;
        }
        
        if ($this->fieldSets) {
            $this->fieldSets = clone $this->fieldSets;
        }
    }
}
