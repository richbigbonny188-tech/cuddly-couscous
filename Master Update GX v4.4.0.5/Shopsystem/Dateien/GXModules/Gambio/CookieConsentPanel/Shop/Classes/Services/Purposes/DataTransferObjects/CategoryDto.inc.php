<?php
/*--------------------------------------------------------------------------------------------------
    CategoryDto.php 2020-01-13
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */
namespace Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects;

/**
 * Class CategoryDto
 * @package Gambio\CookieConsentPanel\Services\Purposes\DataTransferObjects
 */
class CategoryDto
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    
    
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
    public function name(): string
    {
        return $this->name;
    }
    
    
    /**
     * CategoryDto constructor.
     *
     * @param string $name
     * @param int    $id
     */
    public function __construct(string $name, int $id)
    {
        $this->name = $name;
        $this->id   = $id;
    }
    
}