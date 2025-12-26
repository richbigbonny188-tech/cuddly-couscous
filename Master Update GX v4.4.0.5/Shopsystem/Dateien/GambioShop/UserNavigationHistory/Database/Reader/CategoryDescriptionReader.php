<?php
/*--------------------------------------------------------------
   CategoryDescriptionReader.php 2020-09-02
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\UserNavigationHistory\Database\Reader;

use Gambio\Shop\UserNavigationHistory\Database\DTO\CategoryKeywordDto;

/**
 * Interface CategoryDescriptionReader
 * @package Gambio\Shop\UserNavigationHistory\Database\Reader
 */
interface CategoryDescriptionReader
{
    /**,
     * @param CategoryKeywordDto $dto
     *
     * @return int|null
     */
    public function categoryIdBySeoKeyword(CategoryKeywordDto $dto): ?int;
}