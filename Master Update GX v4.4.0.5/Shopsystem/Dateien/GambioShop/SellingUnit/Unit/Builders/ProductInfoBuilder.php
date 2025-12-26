<?php
/*--------------------------------------------------------------------
 ProductInfoBuilder.php 2021-03-29
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2021 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\Builders;

use Gambio\Shop\SellingUnit\Unit\Builders\Exceptions\UnfinishedBuildException;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\ProductInfoBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\Entities\Collections\TabCollection;
use Gambio\Shop\SellingUnit\Unit\Entities\Interfaces\ProductInfoInterface;
use Gambio\Shop\SellingUnit\Unit\Entities\ProductInfo;
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
 * Class ProductInfoBuilder
 * @package Gambio\Shop\SellingUnit\Unit\Builders
 */
class ProductInfoBuilder implements ProductInfoBuilderInterface
{
    /**
     * @var Name
     */
    protected $name;
    
    /**
     * @var Description
     */
    protected $description;
    
    /**
     * @var Url
     */
    protected $url;
    
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
     * @var bool
     */
    protected $showReleaseDate;
    
    /**
     * @var ShowAdditionalPriceInformation
     */
    protected $showAdditionalPriceInformation;
    
    /**
     * @var DiscountAllowed
     */
    protected $discountAllowed;
    
    
    /**
     * @inheritDoc
     */
    public static function create(): ProductInfoBuilderInterface
    {
        return new static;
    }
    
    
    /**
     * @inheritDoc
     */
    public function build(): ProductInfoInterface
    {
        $properties = [
            'name',
            'description',
            'url',
            'tabs',
            'numberOfOrders',
            'legalAgeFlag',
            'availabilityDate',
            'releaseDate',
            'status',
            'showWeight',
        ];
    
        foreach ($properties as $property) {
        
            if ($this->$property === null) {
            
                throw new UnfinishedBuildException(static::class . ' is missing a ' . $property);
            }
        }
    
        return new ProductInfo(
            $this->name,
            $this->url,
            $this->description,
            $this->tabs,
            $this->numberOfOrders,
            $this->legalAgeFlag,
            $this->availabilityDate,
            $this->releaseDate,
            $this->status,
            $this->showWeight,
            $this->showAdditionalPriceInformation,
            $this->discountAllowed
        );
    }
    
    
    /**
     * @inheritDoc
     */
    public function withName(Name $name): ProductInfoBuilderInterface
    {
        $this->name = $name;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withDescription(Description $description): ProductInfoBuilderInterface
    {
        $this->description = $description;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withTabs(TabCollection $tabs): ProductInfoBuilderInterface
    {
        $this->tabs = $tabs;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function reset(): ProductInfoBuilderInterface
    {
        $this->name = $this->description = $this->url = $this->tabs = $this->numberOfOrders = $this->legalAgeFlag = $this->availabilityDate = $this->releaseDate = $this->status = $this->showWeight = $this->showAdditionalPriceInformation = $this->discountAllowed = null;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withUrl(Url $url): ProductInfoBuilderInterface
    {
        $this->url = $url;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withNumberOfOrders(NumberOfOrders $numberOfOrders): ProductInfoBuilderInterface
    {
        $this->numberOfOrders = $numberOfOrders;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withLegalAgeFlag(LegalAgeFlag $legalAgeFlag): ProductInfoBuilderInterface
    {
        $this->legalAgeFlag = $legalAgeFlag;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withAvailabilityDate(AvailabilityDate $availabilityDate): ProductInfoBuilderInterface
    {
        $this->availabilityDate = $availabilityDate;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withReleaseDate(ReleaseDate $releaseDate): ProductInfoBuilderInterface
    {
        $this->releaseDate = $releaseDate;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withStatus(ProductStatus $status): ProductInfoBuilderInterface
    {
        $this->status = $status;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withShowWeight(ShowWeight $showWeight): ProductInfoBuilderInterface
    {
        $this->showWeight = $showWeight;
        
        return $this;
    }
    
    
    public function withShowAdditionalPriceInformation(ShowAdditionalPriceInformation $information
    ): ProductInfoBuilderInterface {
        
        $this->showAdditionalPriceInformation = $information;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withDiscountAllowed(DiscountAllowed $discountAllowed): ProductInfoBuilderInterface
    {
        $this->discountAllowed = $discountAllowed;
    
        return $this;
    }
}
