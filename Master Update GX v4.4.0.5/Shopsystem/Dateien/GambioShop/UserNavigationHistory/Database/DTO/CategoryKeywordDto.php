<?php
/*--------------------------------------------------------------
   CategoryKeywordDto.php 2020-09-02
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\UserNavigationHistory\Database\DTO;

/**
 * Class CategoryKeywordDto
 * @package Gambio\Shop\UserNavigationHistory\Database\DTO
 */
class CategoryKeywordDto
{
    protected const TRAILING_SLASH_PATTERN = '#/$#';
    
    /**
     * @var string
     */
    protected $category;
    
    /**
     * @var int
     */
    protected $languageId;
    
    
    /**
     * CategoryKeywordDto constructor.
     *
     * @param string $category
     * @param int    $languageId
     */
    public function __construct(string $category, int $languageId)
    {
        if (preg_match(static::TRAILING_SLASH_PATTERN, $category)) {
            
            $category = preg_replace(static::TRAILING_SLASH_PATTERN, '', $category);
        }
    
        $this->category   = $category;
        $this->languageId = $languageId;
    }
    
    
    /**
     * @return string
     */
    public function category(): string
    {
        return $this->category;
    }
    
    
    /**
     * @return int
     */
    public function languageId(): int
    {
        return $this->languageId;
    }
}