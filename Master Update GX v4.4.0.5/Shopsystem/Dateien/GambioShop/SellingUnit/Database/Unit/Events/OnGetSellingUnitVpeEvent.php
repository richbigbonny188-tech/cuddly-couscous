<?php
/*--------------------------------------------------------------------------------------------------
    OnGetSellingUnitVpeEvent.php 2021-01-25
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\SellingUnit\Database\Unit\Events;

use Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces\OnGetSellingUnitVpeEventInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\VpeBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Vpe;
use ProductDataInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Class OnGetSellingUnitVpeEvent
 * @package Gambio\Shop\SellingUnit\Database\Unit\Events
 */
class OnGetSellingUnitVpeEvent implements OnGetSellingUnitVpeEventInterface, StoppableEventInterface
{
    /**
     * @var SellingUnitId
     */
    protected $id;
    /**
     * @var ProductDataInterface
     */
    protected $product;
    /**
     * @var Vpe
     */
    protected $vpe = [];
    /**
     * @var bool
     */
    protected $stopped = false;
    
    
    /**
     * OnGetSellingUnitVpeEvent constructor.
     *
     * @param SellingUnitId        $id
     * @param ProductDataInterface $product
     */
    public function __construct(
        SellingUnitId $id,
        ProductDataInterface $product
    ) {
        $this->id      = $id;
        $this->product = $product;
    }
    
    
    /**
     * @inheritDoc
     */
    public function id(): SellingUnitId
    {
        return $this->id;
    }
    
    
    /**
     * @inheritDoc
     */
    public function product(): ProductDataInterface
    {
        return $this->product;
    }
    
    
    /**
     * @inheritDoc
     */
    public function setVpe(?Vpe $vpe, int $priority): void
    {
        $this->vpe[$priority] = $vpe;
        ksort($this->vpe);
    }

    
    /**
     * @inheritDoc
     */
    public function vpe(): ?Vpe
    {
        return empty($this->vpe) ? null : end($this->vpe);
    }
    
    
    /**
     * @inheritDoc
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }
    
    
    /**
     * @inheritDoc
     */
    public function stop(): void
    {
        $this->stopped = true;
    }
}
