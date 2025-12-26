<?php
/*------------------------------------------------------------------------------
 PriceBuilderInterface.php 2020-10-28
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\Builders\Interfaces;

use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Unit\Builders\Exceptions\UnfinishedBuildException;
use Gambio\Shop\SellingUnit\Unit\Entities\Price;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\PriceFormatted;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\PricePlain;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\PriceStatus;

/**
 * Interface PriceBuilderInterface
 * @package Gambio\Shop\SellingUnit\Unit\Builders\Interfaces
 */
interface PriceBuilderInterface
{
    /**
     * @return self
     */
    public static function create(): self;
    
    
    /**
     * @return Price
     * @throws UnfinishedBuildException
     */
    public function build(): Price;
    
    
    /**
     * @return self
     */
    public function reset(): self;
    
    /**
     * @param PriceStatus $status
     * @return self
     */
    public function withStatus(PriceStatus $status): self;
    
    
    /**
     * @param string $string
     * @param mixed  $value
     *
     * @return mixed
     */
    public function withData(string $string, $value): self;
    
    
    /**
     * @param float $value
     *
     * @return self
     */
    public function withQuantity(float $value): self;
    
    
    /**
     * @param int $getTaxClassId
     *
     * @return self
     */
    public function withTaxClassId(int $getTaxClassId): self;
    
    
    /**
     * @param ProductId $productId
     *
     * @return self
     */
    public function withProductId(ProductId $productId): self;
}