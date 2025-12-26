<?php
/*--------------------------------------------------------------------
 AttributeDTO.php 2020-12-21
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\Attributes\SellingUnit\Database\Repository\DTO;

/**
 * Class AttributeDTO
 * @package Gambio\Shop\Attributes\SellingUnit\Database\Repository\DTO
 * @codeCoverageIgnore
 */
class AttributeDTO
{
    /**
     * @var string
     */
    protected $model;
    /**
     * @var int
     */
    protected $vpeId;
    /**
     * @var int
     */
    protected $vpeValue;
    /**
     * @var int
     */
    protected $sortOrder;
    /**
     * @var int
     */
    protected $groupSortOrder;
    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $weightPrefix;
    /**
     * @var float
     */
    protected $weightValue;
    /**
     * @var string|null
     */
    private $vpeName;
    
    
    /**
     * AttributeDTO constructor.
     *
     * @param int         $id
     * @param string      $model
     * @param int|null    $vpeId
     * @param string|null $vpeName
     * @param float|null  $vpeValue
     * @param int         $sortOrder
     * @param int         $groupSortOrder
     * @param string      $weightPrefix
     * @param float       $weightValue
     */
    public function __construct(
        int $id,
        string $model,
        ?int $vpeId,
        ?string $vpeName,
        ?float $vpeValue,
        int $sortOrder,
        int $groupSortOrder,
        string $weightPrefix,
        float $weightValue
    ) {
        $this->model          = $model;
        $this->vpeId          = $vpeId;
        $this->vpeValue       = $vpeValue;
        $this->sortOrder      = $sortOrder;
        $this->groupSortOrder = $groupSortOrder;
        $this->id             = $id;
        $this->weightPrefix   = $weightPrefix;
        $this->weightValue         = $weightValue;
        $this->vpeName = $vpeName;
    }
    
    
    /**
     * @return string
     */
    public function model(): string
    {
        return $this->model;
    }
    
    
    /**
     * @return int
     */
    public function vpeId(): ?int
    {
        return $this->vpeId;
    }
    
    
    /**
     * @return float
     */
    public function vpeValue(): ?float
    {
        return $this->vpeValue;
    }
    
    
    /**
     * @return int
     */
    public function groupSortOrder(): int
    {
        return $this->groupSortOrder;
    }
    
    
    /**
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }
    
    
    /**
     * @return string
     */
    public function weightPrefix(): string
    {
        return $this->weightPrefix;
    }
    
    
    /**
     * @return float
     */
    public function weight(): float
    {
        return $this->weightValue;
    }
    
    
    public function vpeName(): ?string
    {
        return $this->vpeName;
    }
}