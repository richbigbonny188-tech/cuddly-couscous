<?php
/*------------------------------------------------------------------------------
 PriceBuilder.php 2020-11-02
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Unit\Builders;

use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\SellingUnit\Unit\Builders\Exceptions\UnfinishedBuildException;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\PriceBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\Entities\Price;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\PriceFormatted;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\PricePlain;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\PriceStatus;

require_once dirname(__DIR__, 4) . '/gm/classes/GMAttributesCalculator.php';


/**
 * Class PriceBuilder
 * @package Gambio\Shop\SellingUnit\Unit\Builders
 * @codeCoverageIgnore not testable due to the usage of the MainFactory
 */
class PriceBuilder implements PriceBuilderInterface
{
    /**
     * @var PricePlain
     */
    protected $pricePlain;
    
    /**
     * @var PriceFormatted
     */
    protected $priceFormatted = null;
    /**
     * @var PriceStatus
     */
    protected $status;
    /**
     * @var array
     */
    protected $data = [];
    /**
     * @var float
     */
    protected $quantity;
    /**
     * @var int
     */
    protected $taxClassId;
    /**
     * @var ProductId
     */
    protected $productId;
    
    
    /**
     * @inheritDoc
     */
    public static function create(): PriceBuilderInterface
    {
        return new static;
    }
    
    
    /**
     * @return mixed
     */
    protected function createCalculator()
    {
        /** @var \xtcPrice_ORIGIN $xtPrice */
        global  $xtPrice;
        

        $attributes = $this->data['attributes'] ?? [];
        $combination = $this->data['combination'] ?? 0;
        if ($this->data['cheapest'] ?? false) {
            $xtPrice->setShowFromAttributes(true);
        } else {
            $xtPrice->setShowFromAttributes(false);
        }
    
    
        return \MainFactory::create('GMAttributesCalculator',
                                   $this->productId->value(),
                                   $attributes,
                                   $this->taxClassId,
                                   $combination);
    }
    
    
    /**
     * @inheritDoc
     */
    public function build(): Price
    {
        $fields = [
            'productId',
            'taxClassId',
            'quantity',
            'status'
        ];
    
        foreach ($fields as $field) {
        
            if ($this->$field === null) {
            
                throw new UnfinishedBuildException($field . ' was not set');
            }
        }
        
        $calculator = $this->createCalculator();
        $formatted = $calculator->calculate($this->quantity, true);
        $plain     = $calculator->calculate($this->quantity, false);

        return new Price(new PricePlain($plain), new PriceFormatted($formatted), $this->status);
    }
    
    
    /**
     * @inheritDoc
     */
    public function reset(): PriceBuilderInterface
    {
        $this->pricePlain = $this->priceFormatted = $this->status = null;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withPricePlain(PricePlain $plain): PriceBuilderInterface
    {
        $this->pricePlain = $plain;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withPriceFormatted(PriceFormatted $formatted): PriceBuilderInterface
    {
        $this->priceFormatted = $formatted;
        
        return $this;
    }
    
    
    /**
     * @inheritDoc
     */
    public function withStatus(PriceStatus $status): PriceBuilderInterface
    {
        $this->status = $status;
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function withData(string $string, $value): PriceBuilderInterface
    {
        $this->data[$string] = $value;
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function withQuantity(float $value): PriceBuilderInterface
    {
        $this->quantity = $value;
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function withTaxClassId(int $taxClassId): PriceBuilderInterface
    {
        $this->taxClassId = $taxClassId;
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function withProductId(ProductId $productId): PriceBuilderInterface
    {
        $this->productId = $productId;
        
        return $this;
    }
}
