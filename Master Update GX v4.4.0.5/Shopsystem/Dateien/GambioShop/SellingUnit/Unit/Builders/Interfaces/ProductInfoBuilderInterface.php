<?php
/*--------------------------------------------------------------------
 ProductInfoBuilderInterface.php 2021-03-29
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\Builders\Interfaces;

use Gambio\Shop\SellingUnit\Unit\Builders\Exceptions\UnfinishedBuildException;
use Gambio\Shop\SellingUnit\Unit\Entities\Collections\TabCollection;
use Gambio\Shop\SellingUnit\Unit\Entities\Interfaces\ProductInfoInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\AvailabilityDate;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Description;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\DiscountAllowed;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\LegalAgeFlag;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Name;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\NumberOfOrders;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ProductStatus;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ReleaseDate;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ShowAdditionalPriceInformation;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ShowWeight;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Url;

/**
 * Interface ProductInfoBuilderInterface
 * @package Gambio\Shop\SellingUnit\Unit\Builders\Interfaces
 */
interface ProductInfoBuilderInterface
{
    /**
     * @return ProductInfoBuilderInterface
     */
    public static function create(): ProductInfoBuilderInterface;
    
    
    /**
     * @return ProductInfoBuilderInterface
     */
    public function reset(): ProductInfoBuilderInterface;
    
    /**
     * @return ProductInfoInterface
     * @throws UnfinishedBuildException
     */
    public function build(): ProductInfoInterface;
    
    
    /**
     * @param Name $name
     *
     * @return ProductInfoBuilderInterface
     */
    public function withName(Name $name): ProductInfoBuilderInterface;
    
    
    /**
     * @param Description $description
     *
     * @return ProductInfoBuilderInterface
     */
    public function withDescription(Description $description) : ProductInfoBuilderInterface;
    
    
    /**
     * @param Url $url
     *
     * @return ProductInfoBuilderInterface
     */
    public function withUrl(Url $url): ProductInfoBuilderInterface;
    
    
    /**
     * @param TabCollection $tabs
     *
     * @return ProductInfoBuilderInterface
     */
    public function withTabs(TabCollection $tabs) : ProductInfoBuilderInterface;
    
    
    /**
     * @param NumberOfOrders $numberOfOrders
     *
     * @return ProductInfoBuilderInterface
     */
    public function withNumberOfOrders(NumberOfOrders $numberOfOrders) : ProductInfoBuilderInterface;
    
    
    /**
     * @param LegalAgeFlag $legalAgeFlag
     *
     * @return ProductInfoBuilderInterface
     */
    public function withLegalAgeFlag(LegalAgeFlag $legalAgeFlag) : ProductInfoBuilderInterface;
    
    
    /**
     * @param AvailabilityDate $availabilityDate
     *
     * @return ProductInfoBuilderInterface
     */
    public function withAvailabilityDate(AvailabilityDate $availabilityDate) : ProductInfoBuilderInterface;
    
    
    /**
     * @param ReleaseDate $releaseDate
     *
     * @return ProductInfoBuilderInterface
     */
    public function withReleaseDate(ReleaseDate $releaseDate) : ProductInfoBuilderInterface;
    
    
    /**
     * @param ProductStatus $status
     *
     * @return ProductInfoBuilderInterface
     */
    public function withStatus(ProductStatus $status) : ProductInfoBuilderInterface;
    
    
    /**
     * @param ShowWeight $showWeight
     *
     * @return ProductInfoBuilderInterface
     */
    public function withShowWeight(ShowWeight $showWeight): ProductInfoBuilderInterface;
    
    
    /**
     * @param ShowAdditionalPriceInformation $information
     *
     * @return ProductInfoBuilderInterface
     */
    public function withShowAdditionalPriceInformation(ShowAdditionalPriceInformation $information): ProductInfoBuilderInterface;
    
    
    /**
     * @param DiscountAllowed $discountAllowed
     *
     * @return ProductInfoBuilderInterface
     */
    public function withDiscountAllowed(DiscountAllowed $discountAllowed): ProductInfoBuilderInterface;
}
