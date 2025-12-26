<?php
/*--------------------------------------------------------------------------------------------------
    AttributeGroupReaderInterface.php 2020-08-04
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/
namespace Gambio\Shop\Attributes\ProductModifiers\Database\Readers;

use Gambio\Shop\ProductModifiers\Database\Core\DTO\Groups\GroupDTOCollectionInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

interface AttributeGroupReaderInterface
{
    
    /**
     * @param SellingUnitId $id
     *
     * @return GroupDTOCollectionInterface
     */
    public function getGroupsBySellingUnit(
        SellingUnitId $id
    ): GroupDTOCollectionInterface;
    
}