<?php
/*------------------------------------------------------------------------------
 OnGetSellingUnitEanEventInterface.php 2020-11-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

declare(strict_types=1);

namespace Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces;

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\SellingUnit\Unit\Builders\Interfaces\EanBuilderInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use ProductDataInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Ean;

/**
 * Interface OnGetSellingUnitEanEventInterface
 * @package Gambio\Shop\SellingUnit\Database\Unit\Events\Interfaces
 */
interface OnGetSellingUnitEanEventInterface
{
    /**
     * @return ProductDataInterface
     */
    public function product() : ProductDataInterface;
    
    
    
    /**
     * @return SellingUnitId
     */
    public function id() : SellingUnitId;
    
    
    /**
     * @return EanBuilderInterface
     */
    public function builder() : EanBuilderInterface;
}