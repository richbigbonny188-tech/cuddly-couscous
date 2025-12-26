<?php
/* --------------------------------------------------------------
  Purpose.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Entities;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\AliasInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\CategoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\DeletableInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\DescriptionInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\IdInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\NameInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\StatusInterface;
use JsonSerializable;

/**
 * Class Purpose
 * @package Gambio\CookieConsentPanel\Services\Purposes\Entities
 */
class Purpose implements PurposeInterface, JsonSerializable
{
    /**
     * @var CategoryInterface
     */
    protected $category;
    
    /**
     * @var DescriptionInterface
     */
    protected $description;
    
    /**
     * @var NameInterface
     */
    protected $name;
    
    /**
     * @var StatusInterface
     */
    protected $status;
    
    /**
     * @var DeletableInterface
     */
    protected $deletable;
    
    /**
     * @var AliasInterface
     */
    protected $alias;
    
    /**
     * @var IdInterface|null
     */
    protected $id;
    
    
    /**
     * Purpose constructor.
     *
     * @param CategoryInterface    $category
     * @param DescriptionInterface $description
     * @param NameInterface        $name
     * @param StatusInterface      $status
     * @param DeletableInterface   $deletable
     * @param AliasInterface       $alias
     * @param IdInterface|null     $id
     */
    public function __construct(
        CategoryInterface $category,
        DescriptionInterface $description,
        NameInterface $name,
        StatusInterface $status,
        DeletableInterface $deletable,
        AliasInterface $alias,
        IdInterface $id
    ) {
        $this->category    = $category;
        $this->description = $description;
        $this->name        = $name;
        $this->status      = $status;
        $this->deletable   = $deletable;
        $this->alias       = $alias;
        $this->id          = $id;
    }
    
    
    /**
     * @inheritDoc
     */
    public function category(): CategoryInterface
    {
        return $this->category;
    }
    
    
    /**
     * @inheritDoc
     */
    public function description(): DescriptionInterface
    {
        return $this->description;
    }
    
    
    /**
     * @inheritDoc
     */
    public function name(): NameInterface
    {
        return $this->name;
    }
    
    
    /**
     * @inheritDoc
     */
    public function status(): StatusInterface
    {
        return $this->status;
    }
    
    
    /**
     * @inheritDoc
     */
    public function deletable(): DeletableInterface
    {
        return $this->deletable;
    }
    
    
    /**
     * @inheritDoc
     */
    public function alias(): AliasInterface
    {
        return $this->alias;
    }
    
    
    /**
     * @inheritDoc
     */
    public function id(): IdInterface
    {
        return $this->id;
    }
    
    
    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return (object)[
            'id'          => $this->id()->value(),
            'name'        => current($this->name()->value()),
            'description' => current($this->description()->value()),
            'category'    => $this->category()->id(),
            'value'       => $this->category()->value()
        ];
    }
}