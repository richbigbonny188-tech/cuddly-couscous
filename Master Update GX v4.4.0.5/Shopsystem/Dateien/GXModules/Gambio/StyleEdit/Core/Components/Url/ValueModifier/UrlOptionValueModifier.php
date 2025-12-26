<?php
/*--------------------------------------------------------------
   UrlOptionValueModifier.php 2020-12-16
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\StyleEdit\Core\Components\Url\ValueModifier;

use Exception;
use Gambio\StyleEdit\Configurations\ShopBaseUrl;
use Gambio\StyleEdit\Core\Options\Entities\AbstractValueModifier;
use Gambio\StyleEdit\Core\SingletonPrototype;
use stdClass;

/**
 * Class UrlOptionValueModifier
 * @package Gambio\Core\Configuration\Repositories\Components\Url\ValueModifier
 */
class UrlOptionValueModifier extends AbstractValueModifier
{
    
    /**
     * @var ShopBaseUrl
     */
    protected $shopBaseUrl;
    
    
    /**
     * UrlOptionValueModifier constructor.
     *
     * @param ShopBaseUrl $shopBaseUrl
     */
    public function __construct(ShopBaseUrl $shopBaseUrl)
    {
        $this->shopBaseUrl = $shopBaseUrl;
    }
    
    
    /**
     * @inheritDoc
     */
    protected function parseOptionData(stdClass $optionData): string
    {
        return str_replace($this->shopBaseUrl->value(), '', $optionData->value);
    }
}