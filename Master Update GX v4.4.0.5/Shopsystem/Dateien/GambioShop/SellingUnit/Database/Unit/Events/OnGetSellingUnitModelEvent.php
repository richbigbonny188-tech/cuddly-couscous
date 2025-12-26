<?php
/*--------------------------------------------------------------------
 OnGetSellingUnitModelEvent.php 2020-06-24
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Database\Unit\Events;

use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitModelEventInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\ModelBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\ModelBuilder;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use ProductDataInterface;

/**
 * Class OnGetSellingUnitModelEvent
 *
 * @package Gambio\Shop\SellingUnit\Database\Unit\Events
 */
class OnGetSellingUnitModelEvent implements OnGetSellingUnitModelEventInterface
{
    /**
     * @var
     */
    protected $isPropagationStopped = false;
    /**
     * @var SellingUnitId
     */
    private $sellingUnitId;
    /**
     * @var ProductDataInterface
     */
    protected $product;
    /**
     * @var ModelBuilder
     */
    protected $builder;

    /**
     * OnGetSellingUnitModelEvent constructor.
     *
     * @param ProductDataInterface $product
     * @param SellingUnitId $sellingUnitId
     * @param ModelBuilder|null $builder
     */
    public function __construct(
        ProductDataInterface $product,
        SellingUnitId $sellingUnitId,
        ModelBuilder $builder = null
    ) {
        $this->product       = $product;
        $this->sellingUnitId = $sellingUnitId;
        $this->builder       = $builder ?? new ModelBuilder();
    }

    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }

    public function product(): ProductDataInterface
    {
        return $this->product;
    }

    public function id(): SellingUnitId
    {
        return $this->sellingUnitId;
    }

    public function stopPropagation(): void
    {
        $this->isPropagationStopped = true;
    }

    public function builder(): ModelBuilderInterface
    {
        return $this->builder;
    }
}