<?php
/* --------------------------------------------------------------
  xtc_date_long.inc.php 2020-11-26
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------

  based on:
  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
  (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
  (c) 2003	 nextcommerce (xtc_date_long.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: xtc_date_long.inc.php 899 2013-03-13 02:40:57Z hhgag $)

  Released under the GNU General Public License
  -------------------------------------------------------------- */

include_once DIR_FS_INC . 'get_locale_by_language_id.inc.php';

// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
function xtc_date_long($raw_date, ?int $languageId = null)
{
	if($raw_date === '0000-00-00 00:00:00' || $raw_date === '1000-01-01 00:00:00' || $raw_date == '')
	{
		return false;
	}
    
    if (extension_loaded('intl')) {
        $languageProvider = MainFactory::create(LanguageProvider::class,
                                                   StaticGXCoreLoader::getDatabaseQueryBuilder());
        $nonEmptyLanguageId       = $languageId ?? (int)$_SESSION['languages_id'];
        $languageCode = $languageProvider->getCodeById(new IdType($nonEmptyLanguageId));
        
        return utf8_encode_wrapper(DateFormatter::formatAsFullDate(new DateTime($raw_date), $languageCode));
    }

    $locale = $languageId !== null ? get_locale_by_language_id($languageId) : null;

    if ($locale !== null) {
        $currentLocale = setlocale(LC_TIME, 0);
        setlocale(LC_TIME, ...$locale);
    }

    $dateFormat = DATE_FORMAT_LONG;

    if ($languageId !== null) {
        $db               = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $languageSettings = $db->select()->from('languages')->where('languages_id', $languageId)->get()->row_array();
        if ($languageSettings !== null) {
            $dateFormat = $languageSettings['date_format_long'];
        }
    }
	
	$year   = (int)substr($raw_date, 0, 4);
	$month  = (int)substr($raw_date, 5, 2);
	$day    = (int)substr($raw_date, 8, 2);
	$hour   = (int)substr($raw_date, 11, 2);
	$minute = (int)substr($raw_date, 14, 2);
	$second = (int)substr($raw_date, 17, 2);

    $date = utf8_encode_wrapper(strftime($dateFormat, mktime($hour, $minute, $second, $month, $day, $year)));

    if (isset($currentLocale)) {
        setlocale(LC_TIME, $currentLocale);
    }
    
    return $date;
}