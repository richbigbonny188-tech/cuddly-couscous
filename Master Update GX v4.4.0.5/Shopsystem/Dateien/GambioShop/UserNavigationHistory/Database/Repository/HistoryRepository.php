<?php
/*--------------------------------------------------------------
   HistoryRepository.php 2020-09-02
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\UserNavigationHistory\Database\Repository;

use Gambio\Shop\UserNavigationHistory\Database\DTO\CategoryKeywordDto;
use Gambio\Shop\UserNavigationHistory\Database\Reader\CategoryDescriptionReader;

/**
 * Class HistoryRepository
 * @package Gambio\Shop\UserNavigationHistory\Database\Repository
 */
class HistoryRepository
{
    /**
     * @var CategoryDescriptionReader
     */
    protected $categoryReader;
    
    
    /**
     * HistoryRepository constructor.
     *
     * @param CategoryDescriptionReader $categoryReader
     */
    public function __construct(CategoryDescriptionReader $categoryReader)
    {
        $this->categoryReader = $categoryReader;
    }
    
    /**,
     * @param CategoryKeywordDto $dto
     *
     * @return int|null
     */
    public function categoryIdBySeoKeyword(CategoryKeywordDto $dto): ?int
    {
        return $this->categoryReader->categoryIdBySeoKeyword($dto);
    }
}