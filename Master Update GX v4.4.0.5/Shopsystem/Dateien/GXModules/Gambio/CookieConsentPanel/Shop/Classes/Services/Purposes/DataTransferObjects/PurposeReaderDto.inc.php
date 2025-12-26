<?php
/* --------------------------------------------------------------
  PurposeReaderDto.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeDtoInterface;

/**
 * Class PurposeReaderDto
 * @package Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects
 */
class PurposeReaderDto implements PurposeDtoInterface
{
    /**
     * @var string|null
     */
    protected $alias;
    /**
     * @var int
     */
    protected $categoryId;
    /**
     * @var bool
     */
    protected $deletable;
    /**
     * @var array
     */
    protected $descriptions;
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var array
     */
    protected $names;
    /**
     * @var bool
     */
    protected $status;
    /**
     * @var array
     */
    private $categoryNames;
    
    
    /**
     * PurposeReaderDataTransferObject constructor.
     *
     * @param int         $categoryId
     * @param array       $categoryNames
     * @param array       $description
     * @param array       $name
     * @param bool        $status
     * @param bool        $deletable
     * @param string|null $alias
     * @param int|null    $id
     */
    public function __construct(
        int $categoryId,
        array $categoryNames,
        array $description,
        array $name,
        bool $status,
        bool $deletable,
        ?string $alias,
        ?int $id
    ) {
        $this->categoryId    = $categoryId;
        $this->descriptions  = $description;
        $this->names         = $name;
        $this->status        = $status;
        $this->deletable     = $deletable;
        $this->alias         = $alias;
        $this->id            = $id;
        $this->categoryNames = $categoryNames;
    }
    
    
    /**
     * @return int
     */
    public function categoryId(): int
    {
        return $this->categoryId;
    }
    
    
    /**
     * @return array
     */
    public function descriptions(): array
    {
        return $this->descriptions;
    }
    
    
    /**
     * @param int $languageId
     *
     * @return string
     */
    public function description(int $languageId): ?string
    {
        return $this->descriptions[$languageId];
    }
    
    
    /**
     * @return array
     */
    public function names(): array
    {
        return $this->names;
    }
    
    
    /**
     * @param int $languageId
     *
     * @return string
     */
    public function name(int $languageId): ?string
    {
        return $this->names[$languageId];
    }
    
    
    /**
     * @return bool
     */
    public function status(): bool
    {
        return $this->status;
    }
    
    
    /**
     * @return bool
     */
    public function deletable(): bool
    {
        return $this->deletable;
    }
    
    
    /**
     * @return string|null
     */
    public function alias(): ?string
    {
        return $this->alias;
    }
    
    
    /**
     * @return int|null
     */
    public function id(): ?int
    {
        return $this->id;
    }
    
    
    /**
     * @param int $languageId
     *
     * @return string
     */
    public function categoryName(int $languageId): ?string
    {
        return $this->categoryNames[$languageId];
    }
    
    /**
     * @return array
     */
    public function categoryNames(): array
    {
        return $this->categoryNames;
    }
}