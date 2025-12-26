<?php
/* --------------------------------------------------------------
  CookieConsentPanelStorage.php 2019-12-18
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\CookieConsentPanel\Storage;

use ConfigurationStorage;

/**
 * Class CookieConsentPanelStorage
 */
class CookieConsentPanelStorage extends ConfigurationStorage
{
    /**
     * Configuration storage namespace
     */
    const CONFIG_STORAGE_NAMESPACE = 'modules/GambioCookieConsentPanel';
    
    
    /**
     * CookieConsentPanelStorage constructor.
     */
    public function __construct()
    {
        parent::__construct(self::CONFIG_STORAGE_NAMESPACE);
    }
    
    
    /**
     * @inheritDoc
     */
    public function get($key)
    {
        $value = parent::get($key);
    
        return $this->parseValue($value);
    }
    
    
    /**
     * @param $value
     *
     * @return mixed
     */
    protected function parseValue($value)
    {
        if ($this->isJson($value)) {
            $decoded = json_decode($value, true);
    
            foreach ($decoded as $index => $item) {
                $decoded[$index] = $this->replaceNewLineByBR($item);
            }
    
            return json_encode($decoded);
        }
        return $value;
    }
    
    
    /**
     * @param $string
     *
     * @return string|string[]|null
     */
    protected function replaceNewLineByBR($string)
    {
        return preg_replace('/(\\r\\n)/', '<br>', $string);
    }
    
    
    /**
     * @param $string
     *
     * @return bool
     */
    protected function isJson($string)
    {
        if (is_string($string)) {
            return is_object(json_decode($string)) && json_last_error() === JSON_ERROR_NONE;
        }
        
        return false;
    }
}