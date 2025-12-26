<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitEanEvent.php 2020-11-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Database\Unit\Events;

use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitEanEventInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\EanBuilder;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\EanBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use ProductDataInterface;

/**
 * Class OnGetSellingUnitEanEvent
 * @package Gambio\Shop\SellingUnit\Database\Unit\Events
 */
class OnGetSellingUnitEanEvent implements OnGetSellingUnitEanEventInterface
{
    /**
     * @var EanBuilderInterface
     */
    protected $builder;
    /**
     * @var ProductDataInterface
     */
    private $product;
    /**
     * @var SellingUnitId
     */
    private $id;
    
    
    /**
     * OnGetSellingUnitEanEvent constructor.
     *
     * @param ProductDataInterface     $product
     * @param SellingUnitId            $id
     * @param EanBuilderInterface|null $builder
     */
    public function __construct(
        ProductDataInterface $product,
        SellingUnitId $id,
        ?EanBuilderInterface $builder = null
    
    ) {
        
        $this->product = $product;
        $this->id      = $id;
        $this->builder = $builder ?? new EanBuilder();
    }
    
    
    /**
     * @inheritDoc
     */
    public function builder(): EanBuilderInterface
    {
        return $this->builder;
    }
    
    
    public function product(): ProductDataInterface
    {
        return $this->product;
    }
    
    
    public function id(): SellingUnitId
    {
        return $this->id;
    }
}