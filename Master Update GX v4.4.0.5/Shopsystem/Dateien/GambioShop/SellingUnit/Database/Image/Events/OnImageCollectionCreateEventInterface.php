<?php
/*--------------------------------------------------------------------------------------------------
    OnImageCollectionCreateEventInterface.php 2020-02-17
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */
declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Database\Image\Events;

use Gambio\Shop\SellingUnit\Images\Builders\CollectionBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Interface OnCollectionCreateEventInterface
 *
 * @package Gambio\Shop\SellingUnit\Image\Events
 */
interface OnImageCollectionCreateEventInterface extends StoppableEventInterface
{
    /**
     * @return SellingUnitId
     */
    public function id(): SellingUnitId;


    /**
     * @return CollectionBuilderInterface
     */
    public function builder(): CollectionBuilderInterface;
    
    
    /**
     * @return void
     */
    public function stopPropagation(): void;

}