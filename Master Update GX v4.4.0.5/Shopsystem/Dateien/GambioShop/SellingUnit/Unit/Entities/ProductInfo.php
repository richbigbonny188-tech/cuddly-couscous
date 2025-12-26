<?php
/*--------------------------------------------------------------------
 ProductInfo.php 2021-03-29
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/
namespace Gambio\Shop\SellingUnit\Unit\Entities;

use Gambio\Shop\SellingUnit\Unit\Entities\Collections\TabCollection;
use Gambio\Shop\SellingUnit\Unit\Entities\Interfaces\ProductInfoInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\AdditionDate;
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
 * Class ProductInfo
 * @package Gambio\Shop\SellingUnit\Unit\Entities
 */
class ProductInfo implements ProductInfoInterface
{
    /**
     * @var Name
     */
    protected $name;
    
    /**
     * @var Url
     */
    protected $url;
    
    /**
     * @var Description
     */
    protected $description;
    
    /**
     * @var TabCollection
     */
    protected $tabs;
    
    /**
     * @var NumberOfOrders
     */
    protected $numberOfOrders;
    
    /**
     * @var LegalAgeFlag
     */
    protected $legalAgeFlag;
    
    /**
     * @var AvailabilityDate
     */
    protected $availabilityDate;
    
    /**
     * @var ReleaseDate
     */
    protected $releaseDate;
    
    /**
     * @var ProductStatus
     */
    protected $status;
    
    /**
     * @var ShowWeight
     */
    protected $showWeight;
    /**
     * @var AdditionDate
     */
    protected $showReleaseDate;
    
    /**
     * @var ShowAdditionalPriceInformation
     */
    protected $showAdditionalPriceInformation;
    
    /**
     * @var DiscountAllowed
     */
    private $discountAllowed;
    
    
    /**
     * ProductInfo constructor.
     *
     * @param Name                           $name
     * @param Url                            $url
     * @param Description                    $description
     * @param TabCollection                  $tabs
     * @param NumberOfOrders                 $numberOfOrders
     * @param LegalAgeFlag                   $legalAgeFlag
     * @param AvailabilityDate               $availabilityDate
     * @param ReleaseDate                    $releaseDate
     * @param ProductStatus                  $status
     * @param ShowWeight                     $showWeight
     * @param ShowAdditionalPriceInformation $showAdditionalPriceInformation
     */
    public function __construct(
        Name $name,
        Url $url,
        Description $description,
        TabCollection $tabs,
        NumberOfOrders $numberOfOrders,
        LegalAgeFlag $legalAgeFlag,
        AvailabilityDate $availabilityDate,
        ReleaseDate $releaseDate,
        ProductStatus $status,
        ShowWeight $showWeight,
        ShowAdditionalPriceInformation $showAdditionalPriceInformation,
        DiscountAllowed $discountAllowed
    ) {
        $this->name                           = $name;
        $this->url                            = $url;
        $this->description                    = $description;
        $this->tabs                           = $tabs;
        $this->numberOfOrders                 = $numberOfOrders;
        $this->legalAgeFlag                   = $legalAgeFlag;
        $this->availabilityDate               = $availabilityDate;
        $this->releaseDate                    = $releaseDate;
        $this->status                         = $status;
        $this->showWeight                     = $showWeight;
        $this->showAdditionalPriceInformation = $showAdditionalPriceInformation;
        $this->discountAllowed                = $discountAllowed;
    }
    
    
    /**
     * @inheritDoc
     */
    public function name(): Name
    {
        return $this->name;
    }
    
    
    /**
     * @inheritDoc
     */
    public function url(): Url
    {
        return $this->url;
    }
    
    
    /**
     * @inheritDoc
     */
    public function description(): Description
    {
        return $this->description;
    }
    
    
    /**
     * @inheritDoc
     */
    public function tabs() : TabCollection
    {
        return $this->tabs;
    }
    
    
    /**
     * @inheritDoc
     */
    public function numberOfOrders() : NumberOfOrders
    {
        return $this->numberOfOrders;
    }
    
    
    /**
     * @inheritDoc
     */
    public function legalAgeFlag() : LegalAgeFlag
    {
        return $this->legalAgeFlag;
    }
    
    
    /**
     * @inheritDoc
     */
    public function availabilityDate() : AvailabilityDate
    {
        return $this->availabilityDate;
    }


    /**
     * @inheritDoc
     */
    public function releaseDate() : ReleaseDate
    {
        return $this->releaseDate;
    }
    
    
    /**
     * @inheritDoc
     */
    public function status() : ProductStatus
    {
        return $this->status;
    }
    
    
    /**
     * @inheritDoc
     */
    public function showWeight(): ShowWeight
    {
        return $this->showWeight;
    }
    
    
    /**
     * @return ShowAdditionalPriceInformation
     */
    public function showAdditionalPriceInformation(): ShowAdditionalPriceInformation
    {
        return $this->showAdditionalPriceInformation;
    }
    
    
    /**
     * @return DiscountAllowed
     */
    public function discountAllowed(): DiscountAllowed
    {
        return $this->discountAllowed;
    }
}
