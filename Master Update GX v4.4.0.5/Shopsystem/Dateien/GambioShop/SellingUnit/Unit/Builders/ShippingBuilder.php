<?php
/*------------------------------------------------------------------------------
 ShippingBuilder.php 2021-01-05
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/
declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\Builders;

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\ShippingBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ShippingInfo;
use main_ORIGIN;
use ProductsShippingStatusSource;

class ShippingBuilder implements ShippingBuilderInterface
{
    private const DEFAULT_PREFIX = 'images/icons/status/';
    private const DEFAULT_IMAGE  = 'images/icons/status/gray.png';
    protected $status = [];
    /**
     * @var ProductsShippingStatusSource
     */
    private $shippingStatusSource;
    /**
     * @var LanguageId
     */
    protected $languageId;
    /**
     * @var main_ORIGIN
     */
    private $main;
    /**
     * @var ProductId
     */
    protected $productId;
    
    
    /**
     * ShippingBuilder constructor.
     *
     * @param ProductsShippingStatusSource $shippingStatusSource
     * @param mixed                        $main
     */
    public function __construct(ProductsShippingStatusSource $shippingStatusSource, $main)
    {
        $this->shippingStatusSource = $shippingStatusSource;
        $this->main                 = $main;
    }
    
    
    /**
     * @inheritDoc
     */
    function withStatus(int $status, int $priority = 0): ShippingBuilderInterface
    {
        $this->status[$priority] = $status;
        ksort($this->status);
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    function build(): ShippingInfo
    {
        $statusCode = end($this->status);
        $linkAbroad = $this->main::get_abroad_shipping_info_link();
        if ($statusCode) {
            $shippingStatus = $this->shippingStatusSource->get_shipping_status($statusCode, $this->languageId->value());
            
            $link                 = $this->main->getShippingLink(true, $this->productId->value());
            $abroadInfoLinkActive = $this->main->getShippingStatusInfoLinkActive($statusCode);
            
            return new ShippingInfo(
                static::DEFAULT_PREFIX . $shippingStatus['shipping_status_image'] ??
                static::DEFAULT_IMAGE,
                $shippingStatus['shipping_status_name'] ?? '',
                $link,
                $linkAbroad,
                (bool)$abroadInfoLinkActive
            );
        } else {
            return new ShippingInfo(static::DEFAULT_IMAGE, '', '', $linkAbroad, false);
        }
    }
    
    
    /**
     * @inheritDoc
     */
    function withLanguage(LanguageId $languageId): ShippingBuilderInterface
    {
        $this->languageId = $languageId;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    function withProductId(ProductId $id): ShippingBuilderInterface
    {
        $this->productId = $id;
        
        return $this;
    }
}