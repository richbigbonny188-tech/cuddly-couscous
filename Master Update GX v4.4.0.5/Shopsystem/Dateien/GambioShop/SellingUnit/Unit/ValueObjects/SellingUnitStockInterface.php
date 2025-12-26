<?php
/*--------------------------------------------------------------------------------------------------
    SellingUnitStock.php 2020-03-18
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */
namespace Gambio\Shop\SellingUnit\Unit\ValueObjects;


use Gambio\Shop\ProductModifiers\Modifiers\ValueObjects\ModifierIdentifierInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;

interface SellingUnitStockInterface
{
   /**
     * @return AvailableQuantity
     */
    public function availableQuantity(): AvailableQuantity;


    /**
     * @param ModifierIdentifierInterface $id
     * @return QuantityInterface
     */
    public function getQuantityByModifier(ModifierIdentifierInterface $id) : QuantityInterface;

}