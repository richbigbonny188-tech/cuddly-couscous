<?php
/*--------------------------------------------------------------------------------------------------
    CookieConsetPurposeDTO.php 2020-10-30
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

/**
 * Class CookieConsetPurposeDTO
 */
class CookieConsentPurposeDTO
{
    /**
     * @var string
     */
    private $alias;
    /**
     * @var int
     */
    private $category;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $name;
    /**
     * @var boolean
     */
    private $status;
    
    
    /**
     * CookieConsetPurposeDTO constructor.
     *
     * @param int    $category
     * @param string $name
     * @param string $description
     * @param string $alias
     * @param bool   $status
     */
    public function __construct(int $category, string $name, string $description, string $alias, bool $status = true)
    {
        $this->name        = $name;
        $this->description = $description;
        $this->alias       = $alias;
        $this->category    = $category;
        $this->status      = $status;
    }
    
    
    /**
     * @return string
     */
    public function alias(): string
    {
        return $this->alias;
    }
    
    
    /**
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }
    
    
    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
    
    
    /**
     * @return int
     */
    public function category(): int
    {
        return $this->category;
    }
    
    
    /**
     * @return bool
     */
    public function status(): bool
    {
        return $this->status;
    }
    
}