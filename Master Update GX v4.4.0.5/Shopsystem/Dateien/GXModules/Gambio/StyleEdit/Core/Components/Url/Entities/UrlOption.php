<?php
/*--------------------------------------------------------------------------------------------------
    UrlOption.php 2020-12-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2019 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\StyleEdit\Core\Components\Url\Entities;

use Exception;
use Gambio\StyleEdit\Configurations\ShopBaseUrl;
use Gambio\StyleEdit\Core\Options\Entities\AbstractComponentOption;
use Gambio\StyleEdit\Core\SingletonPrototype;

/**
 * Class Url
 * @package Gambio\StyleEdit\Core\Components\TextBox\Entities
 */
class UrlOption extends AbstractComponentOption
{
    /**
     * @var string
     */
    protected const SHOP_BASE_URL_PATTERN = '#^__SHOP_BASE_URL__#';
    /**
     * @param $value
     *
     * @return boolean
     */
    protected function isValid($value): bool
    {
        return true;
    }
    
    
    /**
     * @param $value
     *
     * @return mixed
     * @throws Exception
     */
    protected function parseValue($value)
    {
        if (is_string($value)) {
    
            /** @var ShopBaseUrl $shopBaseUrl */
            $shopBaseUrl = SingletonPrototype::instance()->get(ShopBaseUrl::class);
            
            // the "__SHOP_BASE_URL__" token is set when a theme, that was saved before is exported
            if (preg_match(self::SHOP_BASE_URL_PATTERN, $value) === 1) {
    
                $value = preg_replace(self::SHOP_BASE_URL_PATTERN, $shopBaseUrl->value(), $value);
                
            } else if ($value !== '' && strpos($value, 'http') !== 0) {
                
                $value = $shopBaseUrl->value() . $value;
            }
        }
    
        return $value;
    }
    
    
    /**
     * @return string
     */
    public function type(): ?string
    {
        return 'url';
    }
}