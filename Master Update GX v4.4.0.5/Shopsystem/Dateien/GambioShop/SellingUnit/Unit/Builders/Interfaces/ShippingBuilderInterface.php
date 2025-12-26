<?php
/*------------------------------------------------------------------------------
 ShippingBuilderInterface.php 2020-11-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/
declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\Builders\Interfaces;

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ShippingInfo;

/**
 * Class ShippingBuilderInterface
 * @package Gambio\Shop\SellingUnit\Unit\Builders\Interfaces
 */
interface ShippingBuilderInterface
{
    /**
     * @param LanguageId $languageId
     *
     * @return ShippingBuilderInterface
     */
    function withLanguage(LanguageId $languageId): ShippingBuilderInterface;
    
    /**
     * @param int $status
     * @param int $priority
     *
     * @return ShippingBuilderInterface
     */
    function withStatus(int $status, int $priority = 0): ShippingBuilderInterface;
    
    
    /**
     * @param ProductId $id
     *
     * @return ShippingBuilderInterface
     */
    function withProductId(ProductId $id): ShippingBuilderInterface;
    
    /**
     * @return ShippingInfo
     */
    function build(): ShippingInfo;
    
}