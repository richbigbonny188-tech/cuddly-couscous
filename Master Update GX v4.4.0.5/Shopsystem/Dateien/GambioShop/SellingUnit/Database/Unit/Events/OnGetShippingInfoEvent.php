<?php
/*--------------------------------------------------------------------
 OnGetShippingInfoEvent.php 2020-11-25
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/
declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Database\Unit\Events;

use Gambio\Shop\SellingUnit\Core\Events\SellingUnitEventTrait;
use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetShippingInfoEventInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\ShippingBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\ShippingBuilder;
use Gambio\Shop\SellingUnit\Unit\Entities\Price;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use MainFactory;
use ProductDataInterface;
use ProductsShippingStatusSource;

/**
 * Class OnGetShippingInfoEvent
 * @package Gambio\Shop\SellingUnit\Database\Unit\Events
 */
class OnGetShippingInfoEvent implements OnGetShippingInfoEventInterface
{
    use SellingUnitEventTrait;
    
    /**
     * @var ShippingBuilderInterface
     */
    protected $builder;
    
    /**
     * @var Price
     */
    private $price;
    
    
    /**
     * OnSellingUnitCreateEvent constructor.
     *
     * @param SellingUnitId                 $id
     * @param ProductDataInterface          $product
     * @param Price                         $price
     * @param ShippingBuilderInterface|null $builder
     */
    public function __construct(
        SellingUnitId $id,
        ProductDataInterface $product,
        Price $price,
        ShippingBuilderInterface $builder = null
    ) {
        $this->id      = $id;
        $this->product = $product;
        $this->price   = $price;
        $this->builder = $builder ?? $this->createBuilder();
    }
    
    
    /**
     * @inheritDoc
     */
    public function builder(): ShippingBuilderInterface
    {
        return $this->builder;
    }
    
    
    /**
     * @return ShippingBuilder
     * @codeCoverageIgnore
     */
    protected function createBuilder()
    {
        return new ShippingBuilder(new ProductsShippingStatusSource(), MainFactory::create_object('main'));
    }
    
    
    /**
     * @inheritDoc
     */
    public function price(): Price
    {
        return $this->price;
    }
}