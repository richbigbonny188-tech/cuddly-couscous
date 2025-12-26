<?php
/*--------------------------------------------------------------------------------------------------
    AttributeModifierReaderInterface.php 2020-08-04
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/
namespace Gambio\Shop\Attributes\ProductModifiers\Database\Readers;

use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOCollectionInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

interface AttributeModifierReaderInterface
{
    /**
     * @param SellingUnitId $id
     *
     * @return mixed
     */
    public function getModifierBySellingUnit(SellingUnitId $id): ModifierDTOCollectionInterface;
}