<?php
/*--------------------------------------------------------------------
 OnGetSellingUnitModelEventInterface.php 2020-2-20
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces;

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\ModelBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Model;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use ProductDataInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Interface OnGetSellingUnitModelEventInterface
 * @package Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces
 */
interface OnGetSellingUnitModelEventInterface extends StoppableEventInterface
{
    /**
     * @return ProductDataInterface
     */
    public function product(): ProductDataInterface;


    /**
     * @return SellingUnitId
     */
    public function id() : SellingUnitId;

    /**
     * @return void
     */
    public function stopPropagation(): void;

    /**
     * @return ModelBuilderInterface
     */
    public function builder() : ModelBuilderInterface;
}