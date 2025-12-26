<?php
/**
 * OnImageCollectionCreateEvent.php 2020-08-05
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2020 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

namespace Gambio\Shop\SellingUnit\Database\Image\Events;

use Gambio\Shop\SellingUnit\Core\Events\SellingUnitEventTrait;
use Gambio\Shop\SellingUnit\Images\Builders\CollectionBuilder;
use Gambio\Shop\SellingUnit\Images\Builders\CollectionBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

/**
 * Class OnImageCollectionCreateEvent
 *
 * @package Gambio\Shop\SellingUnit\Database\Image\Events
 */
class OnImageCollectionCreateEvent implements OnImageCollectionCreateEventInterface
{
    use SellingUnitEventTrait;

    /**
     * @var CollectionBuilder
     */
    protected $builder;
    /**
     * @var bool
     */
    protected $isStopped = false;


    /**
     * OnCollectionCreateEvent constructor.
     *
     * @param SellingUnitId $id
     * @param CollectionBuilderInterface|null $collectionBuilder
     */
    public function __construct(SellingUnitId $id, CollectionBuilderInterface $collectionBuilder = null)
    {
        $this->id      = $id;
        $this->builder = $collectionBuilder ?? new CollectionBuilder;
    }


    /**
     * @inheritDoc
     */
    public function builder(): CollectionBuilderInterface
    {
        return $this->builder;
    }

    /**
     * @inheritDoc
     */
    public function stopPropagation(): void
    {
        $this->isStopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->isStopped;
    }
}