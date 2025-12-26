<?php
/* --------------------------------------------------------------
  CategoryMarketing.php 2020-01-10
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Services\Purposes\ValueObjects;

use Gambio\CookieConsentPanel\Services\Purposes\Interfaces\CategoryInterface;

/**
 * Class CategoryMarketing
 * @package Gambio\CookieConsentPanel\Services\Purposes\ValueObjects
 */
class CategoryMarketing extends Category
{
    public const CATEGORY_NAME = 'Marketing';
    
    public const ID = 4;
    
    protected const TRANSLATION_KEY_TEXT = 'label_cpc_category_04_text';
    
    protected const TRANSLATION_KEY_DESC = 'label_cpc_category_04_desc';
    
    
    /**
     * @inheritDoc
     */
    public static function create(LanguageCode $code): CategoryInterface
    {
        return new static(self::ID,
                          self::translatedName(self::TRANSLATION_KEY_TEXT, $code),
                          self::translatedName(self::TRANSLATION_KEY_DESC, $code));
    }
}