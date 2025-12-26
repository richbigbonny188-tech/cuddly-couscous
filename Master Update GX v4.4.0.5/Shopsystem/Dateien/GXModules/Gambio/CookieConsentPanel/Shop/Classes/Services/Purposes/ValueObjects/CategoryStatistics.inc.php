<?php
/* --------------------------------------------------------------
  CategoryStatistics.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\ValueObjects;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\CategoryInterface;

/**
 * Class CategoryStatistics
 * @package Gambio\CookieConsentPanel\Services\Purposes\ValueObjects
 */
class CategoryStatistics extends Category
{
    public const CATEGORY_NAME = 'Statistics';
    
    public const ID = 3;
    
    protected const TRANSLATION_KEY_TEXT = 'label_cpc_category_03_text';
    
    protected const TRANSLATION_KEY_DESC = 'label_cpc_category_03_desc';
    /**
     * @inheritDoc
     */
    public static function create(LanguageCode $code): CategoryInterface
    {
        return new static(self::ID, self::translatedName(self::TRANSLATION_KEY_TEXT, $code), self::translatedName(self::TRANSLATION_KEY_DESC, $code));
    }
}