<?php
/* --------------------------------------------------------------
  PurposeUpdateDto.php 2020-05-06
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeUpdateDtoInterface;

/**
 * Class PurposeUpdateDto
 * @package Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects
 */
class PurposeUpdateDto implements PurposeUpdateDtoInterface
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
     * PurposeReaderDataTransferObject constructor.
     *
     * @param int         $categoryId
     * @param array       $descriptions
     * @param array       $names
     * @param bool        $status
     * @param string|null $alias
     * @param int|null    $id
     */
    public function __construct(
        int $categoryId,
        array $descriptions,
        array $names,
        bool $status,
        ?string $alias,
        ?int $id
    ) {
        $this->categoryId    = $categoryId;
        $this->descriptions  = $descriptions;
        $this->names         = $names;
        $this->status        = $status;
        $this->alias         = $alias;
        $this->id            = $id;
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
     * @return array
     */
    public function names(): array
    {
        return $this->names;
    }
    
    
   
    
    /**
     * @return bool
     */
    public function status(): bool
    {
        return $this->status;
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
     * @inheritDoc
     */
    public function description(int $languageId)
    {
        return $this->descriptions[$languageId]?: null;
    }
    
    
    /**
     * @inheritDoc
     */
    public function name(int $languageId)
    {
        return $this->names[$languageId]?: null;
    }
}