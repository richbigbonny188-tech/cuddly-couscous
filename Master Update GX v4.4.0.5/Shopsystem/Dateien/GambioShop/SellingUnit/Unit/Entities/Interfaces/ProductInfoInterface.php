<?php
/*--------------------------------------------------------------------
 ProductInfoInterface.php 2020-2-24
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/
namespace Gambio\Shop\SellingUnit\Unit\Entities\Interfaces;

use Gambio\Shop\SellingUnit\Unit\Entities\Collections\TabCollection;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\AvailabilityDate;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Description;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\DiscountAllowed;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\LegalAgeFlag;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Name;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\NumberOfOrders;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ProductStatus;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ReleaseDate;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\ShowWeight;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Url;

/**
 * Interface ProductInfoInterface
 * @package Gambio\Shop\SellingUnit\Unit\Entities\Interfaces
 */
interface ProductInfoInterface
{
    /**
     * @return Name
     */
    public function name(): Name;
    
    
    /**
     * @return Url
     */
    public function url(): Url;
    
    
    /**
     * @return Description
     */
    public function description(): Description;
    
    
    /**
     * @return TabCollection
     */
    public function tabs() : TabCollection;
    
    
    /**
     * @return NumberOfOrders
     */
    public function numberOfOrders() : NumberOfOrders;
    
    
    /**
     * @return LegalAgeFlag
     */
    public function legalAgeFlag() : LegalAgeFlag;
    
    
    /**
     * @return AvailabilityDate
     */
    public function availabilityDate() : AvailabilityDate;

    /**
     * @return ReleaseDate
     */
    public function releaseDate() : ReleaseDate;
    
    
    /**
     * @return ProductStatus
     */
    public function status() : ProductStatus;
    
    
    /**
     * @return ShowWeight
     */
    public function showWeight(): ShowWeight;
    
    
    /**
     * @return DiscountAllowed
     */
    public function discountAllowed(): DiscountAllowed;
}
