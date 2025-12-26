<?php
/* --------------------------------------------------------------
   DateFormatter.inc.php 2020-11-26
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

/**
 * Class DateFormatter
 */
class DateFormatter
{
    /**
     * @param DateTime     $date
     * @param LanguageCode $code
     *
     * @return string
     */
    public static function formatAsFullDate(DateTime $date, LanguageCode $code): string
    {
        $format = new DateTimeFormat(IntlDateFormatter::FULL, IntlDateFormatter::NONE);
        
        return self::formatDate($date, $code, $format);
    }
    
    
    /**
     * @param DateTime     $date
     * @param LanguageCode $code
     *
     * @return string
     */
    public static function formatAsLongDate(DateTime $date, LanguageCode $code): string
    {
        $format = new DateTimeFormat(IntlDateFormatter::LONG, IntlDateFormatter::NONE);
        
        return self::formatDate($date, $code, $format);
    }
    
    
    /**
     * @param DateTime     $date
     * @param LanguageCode $code
     *
     * @return string
     */
    public static function formatAsMediumDate(DateTime $date, LanguageCode $code): string
    {
        $format = new DateTimeFormat(IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
        
        return self::formatDate($date, $code, $format);
    }
    
    
    /**
     * @param DateTime     $date
     * @param LanguageCode $code
     *
     * @return string
     */
    public static function formatAsShortDate(DateTime $date, LanguageCode $code): string
    {
        $format = new DateTimeFormat(IntlDateFormatter::SHORT, IntlDateFormatter::NONE);
        
        return self::formatDate($date, $code, $format);
    }
    
    
    /**
     * @param DateTime       $date
     * @param LanguageCode   $code
     * @param DateTimeFormat $format
     *
     * @return string
     */
    protected static function formatDate(DateTime $date, LanguageCode $code, DateTimeFormat $format): string
    {
        return IntlDateFormatter::formatObject($date, $format->toArray(), self::getLocale($code));
    }
    
    
    /**
     * Gets the corresponding locale for the provided language code.
     *
     * @param LanguageCode $code
     *
     * @return string
     */
    protected static function getLocale(LanguageCode $code): string
    {
        switch (strtolower($code->asString())) {
            case 'de':
                $locale = 'de_DE';
                break;
            case 'fr':
                $locale = 'fr_FR';
                break;
            case 'es':
                $locale = 'es_ES';
                break;
            case 'tr':
                $locale = 'tr_TR';
                break;
            case 'ru':
                $locale = 'ru_RU';
                break;
            case 'en':
            default:
                $locale = 'en_EN';
        }
        
        return $locale;
    }
}