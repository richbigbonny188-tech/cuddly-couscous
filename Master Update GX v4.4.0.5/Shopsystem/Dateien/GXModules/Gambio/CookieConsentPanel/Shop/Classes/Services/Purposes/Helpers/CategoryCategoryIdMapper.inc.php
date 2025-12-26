<?php
/* --------------------------------------------------------------
  CategoryCategoryIdMapper.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\Helpers;

use Gambio\CookieConsentPanel\Services\Purposes\Exceptions\CategoryDoesNotExistsException;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\CategoryCategoryIdMapperInterface;
use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\CategoryInterface;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\CategoryEssential;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\CategoryFunctional;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\CategoryMarketing;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\CategoryMiscellaneous;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\CategoryStatistics;
use Gambio\CookieConsentPanel\Services\Purposes\ValueObjects\LanguageCode;

/**
 * Class CategoryCategoryIdMapper
 * @package Gambio\CookieConsentPanel\Services\Purposes\Helpers
 */
class CategoryCategoryIdMapper implements CategoryCategoryIdMapperInterface
{
    /**
     * @var array
     */
    protected $map = [
        CategoryEssential::ID     => CategoryEssential::class,
        CategoryFunctional::ID    => CategoryFunctional::class,
        CategoryStatistics::ID    => CategoryStatistics::class,
        CategoryMarketing::ID     => CategoryMarketing::class,
        CategoryMiscellaneous::ID => CategoryMiscellaneous::class,
    ];
    
    
    /**
     * @inheritDoc
     * @throws CategoryDoesNotExistsException
     */
    public function CategoryFromCategoryId(int $id, LanguageCode $code): CategoryInterface
    {
        if (array_key_exists($id, $this->map) === false) {
            
            throw new CategoryDoesNotExistsException($id);
        }
        
        return $this->map[$id]::create($code);
    }
    
    
    /**
     * @inheritDoc
     */
    public function allCategories(LanguageCode $code): array
    {
        $result = [];
    
        if (count($this->map)) {
            
            foreach ($this->map as $item) {
                
                $result[] = $item::create($code);
            }
        }
        
        return $result;
    }
}