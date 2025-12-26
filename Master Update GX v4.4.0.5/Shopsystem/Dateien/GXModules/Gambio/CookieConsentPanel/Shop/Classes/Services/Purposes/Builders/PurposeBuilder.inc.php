<?php
/* --------------------------------------------------------------
  PurposeBuilder.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Builders;

use Gambio\CookieConsentPanel\Services\Purposes\Entities\Purpose;
use Gambio\CookieConsentPanel\Services\Purposes\Exceptions\UnfinishedBuildException;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\AliasInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\CategoryCategoryIdMapperInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\CategoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\DeletableInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\DescriptionInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\IdInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\NameInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeBuilderInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\StatusInterface;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\Alias;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\Deletable;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\Description;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\Id;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\LanguageCode;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\Name;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\Status;

/**
 * Class PurposeBuilder
 * @package Gambio\CookieConsentPanel\Services\Purposes\Builders
 */
class PurposeBuilder implements PurposeBuilderInterface
{
    /**
     * @var CategoryInterface
     */
    protected $category;
    
    /**
     * @var string
     */
    protected $alias;
    
    /**
     * @var int
     */
    protected $id;
    
    /**
     * @var bool
     */
    protected $deletable;
    
    /**
     * @var string
     */
    protected $description;
    
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var bool
     */
    protected $status;
    /**
     * @var CategoryCategoryIdMapperInterface
     */
    private $mapper;
    
    
    /**
     * @inheritDoc
     */
    public static function create(CategoryCategoryIdMapperInterface $mapper): PurposeBuilderInterface
    {
        return new static($mapper);
    }
    
    
    /**
     * PurposeBuilder constructor.
     *
     * @param CategoryCategoryIdMapperInterface $mapper
     */
    public function __construct(CategoryCategoryIdMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }
    
    
    /**
     * @inheritDoc
     */
    public function build(): PurposeInterface
    {
        $properties = [
            'category',
            'alias',
            'id',
            'deletable',
            'description',
            'name',
            'status',
        ];
        
        foreach ($properties as $property) {
            
            if ($this->$property === null) {
                
                throw new UnfinishedBuildException('Property ' . $property . ' is required but not set');
            }
        }
        
        $result = new Purpose($this->category,
                              $this->description,
                              $this->name,
                              $this->status,
                              $this->deletable,
                              $this->alias,
                              $this->id);
        
        foreach ($properties as $property) {
            $this->$property = null;
        }
        
        return $result;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withCategory(CategoryInterface $category): PurposeBuilderInterface
    {
        $this->category = $category;
        
        return $this;
    }
    
    
    /**
     * @param int          $categoryId
     * @param LanguageCode $languageCode
     *
     * @return PurposeBuilderInterface
     */
    public function withCategoryId(int $categoryId, LanguageCode $languageCode): PurposeBuilderInterface
    {
        $this->category = $this->mapper->CategoryFromCategoryId($categoryId, $languageCode);
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withAlias(string $alias = null): PurposeBuilderInterface
    {
        $this->alias = new Alias($alias);
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withId(int $id): PurposeBuilderInterface
    {
        $this->id = new Id($id);
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withDeletable(bool $deletable): PurposeBuilderInterface
    {
        $this->deletable = new Deletable($deletable);
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withDescriptions(array $description): PurposeBuilderInterface
    {
        $this->description = new Description($description);
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withNames(array $names): PurposeBuilderInterface
    {
        $this->name = new Name($names);
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withStatus(bool $status): PurposeBuilderInterface
    {
        $this->status = new Status($status);
        
        return $this;
    }
}