<?php
/*--------------------------------------------------------------------------------------------------
    ProductDataInterface.php 2020-12-21
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

declare(strict_types=1);

/**
 * Interface ProductDataInterface
 *
 * used for accessing public data array of product_Origin
 */
interface ProductDataInterface
{
    /**
     * @return int
     */
    public function getTaxClassId(): int;
    
    
    /**
     * @return float
     */
    public function getPrice(): float;
    
    
    /**
     * @return string|null
     */
    public function getProductsName(): ?string;
    
    
    /**
     * @return string|null
     */
    public function getProductsDescription() : ?string;
    
    
    /**
     * @return int
     */
    public function getPropertiesQuantityCheck(): int;
    
    
    /**
     * @return string|null
     */
    public function getProductsUrl(): ?string;
    
    
    /**
     * @return array
     */
    public function getProductTabs() : array;
    
    
    /**
     * @return float
     */
    public function getNumberOfOrders() : float;
    
    
    /**
     * @return bool
     */
    public function getLegalAgeFlag() : bool;
    
    
    /**
     * @return string|null
     */
    public function getAvailabilityDate() : ?string;
    
    
    /**
     * @return string
     */
    public function getReleaseDate() : string;

    /**
     * @return bool
     */
    public function isShowReleaseDate() : bool;


    /**
     * @return bool
     */
    public function getStatus() : bool;
    
    
    /**
     * @return string
     */
    public function getModel(): string;
    
    
    /**
     * @return string|null
     */
    public function getEan() : ?string;
    
    
    /**
     * @return string
     */
    public function measureUnit(): string;
    
    
    /**
     * @return float
     */
    public function getWeight(): float;

    /**
     * @return int
     */
    public function priceStatus() : int;


    /**
     * @return bool
     */
    public function usePropertiesCombisWeight(): bool;
    
    
    /**
     * @return bool
     */
    public function showWeight(): bool;
    
    
    /**
     * @return float
     */
    public function getMinOrder() : float;
    
    
    /**
     * @return float
     */
    public function getGranularity(): float;

    /**
     * @return float
     */
    public function getVpeValue(): float;
    
    
    /**
     * @return string|null
     */
    public function getVpeName(): ?string;

    /**
     * @return int|null
     */
    public function getVpeId(): ?int;

    /**
     * @return int
     */
    public function getVpeStatus(): int;

    /**
     * @return int
     */
    public function getProductType(): int;

    /**
     * @return float
     */
    public function getProductQuantity(): float;
    
    /**
     * @return bool
     */
    public function isPurchasable(): bool;
    
    /**
     * @return bool
     */
    public function showPropertyPrice(): bool;
    
    
    /**
     * @return int
     */
    public function shippingTime(): int;
    
    /**
     * @return bool
     */
    public function useCombinationShippingTime(): bool;
    
    
    /**
     * @return float
     */
    public function discountAllowed(): float;
}
