<?php
/*------------------------------------------------------------------------------
 PropertyModifierReaderInterface.php 2020-10-07
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/
namespace Gambio\Shop\Properties\ProductModifiers\Database\Readers;


use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

interface PropertyModifierReaderInterface
{
    
    /**
     * @param SellingUnitId $id
     *
     * @return mixed
     */
    public function getModifierBySellingUnit(SellingUnitId $id);
}