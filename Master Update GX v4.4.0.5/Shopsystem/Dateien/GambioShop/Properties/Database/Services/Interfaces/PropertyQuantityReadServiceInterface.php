<?php
/*------------------------------------------------------------------------------
 PropertyQuantityReadServiceInterface.php 2020-10-26
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\Properties\Database\Services\Interfaces;

use Gambio\Shop\Properties\Database\Exceptions\CombinationNotFoundException;
use Gambio\Shop\Properties\Database\Exceptions\IncompletePropertyListException;
use Gambio\Shop\Properties\Database\Exceptions\ProductDoesntHavePropertiesException;
use Gambio\Shop\Properties\Properties\ValueObjects\AbstractPropertyQuantity;
use Gambio\Shop\SellingUnit\Unit\Exceptions\InvalidQuantityException;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use ProductDataInterface;

interface PropertyQuantityReadServiceInterface
{
    
    /**
     * @param SellingUnitId        $id
     * @param ProductDataInterface $product
     *
     * @param QuantityInterface    $requested
     *
     * @return AbstractPropertyQuantity
     * @throws InvalidQuantityException
     * @throws ProductDoesntHavePropertiesException
     * @throws IncompletePropertyListException
     */
    public function getAvailableQuantityBy(
        SellingUnitId $id,
        ProductDataInterface $product,
        QuantityInterface $requested
    ): ?AbstractPropertyQuantity;
    
}