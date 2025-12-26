<?php
/* --------------------------------------------------------------
  PurposeWriterDataTransferObject.php 2020-01-13
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects;

use Gambio\CookieConsentPanel\Services\Purposes\Exceptions\PurposeNameCanNotBeEmptyException;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\PurposeWriterDtoInterface;

/**
 * Class PurposeWriterDataTransferObject
 * @package Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects
 */
class PurposeWriterDto implements PurposeWriterDtoInterface
{
    /**
     * @var int
     */
    protected $category;
    
    /**
     * @var array
     */
    protected $description;
    
    /**
     * @var array
     */
    protected $name;
    
    /**
     * @var bool
     */
    protected $status;
    
    /**
     * @var bool
     */
    protected $deletable;
    
    /**
     * @var string|null
     */
    protected $alias;
    
    
    /**
     * PurposeWriterDto constructor.
     *
     * @param int         $category
     * @param array       $description
     * @param array       $name
     * @param bool        $status
     * @param bool        $deletable
     * @param string|null $alias
     *
     * @throws PurposeNameCanNotBeEmptyException
     */
    public function __construct(
        int $category,
        array $description,
        array $name,
        bool $status,
        bool $deletable,
        ?string $alias
    ) {
        foreach ($name as $value) {
            
            if (!is_string($value) || strlen($value) === 0) {
                
                throw new PurposeNameCanNotBeEmptyException;
            }
        }
        
        $this->category    = $category;
        $this->description = $description;
        $this->name        = $name;
        $this->status      = $status;
        $this->deletable   = $deletable;
        $this->alias       = $alias;
    }
    
    
    /**
     * @return int
     */
    public function category(): int
    {
        return $this->category;
    }
    
    
    /**
     * @return array
     */
    public function description(): array
    {
        return $this->description;
    }
    
    
    /**
     * @return array
     */
    public function name(): array
    {
        return $this->name;
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
    
    
}