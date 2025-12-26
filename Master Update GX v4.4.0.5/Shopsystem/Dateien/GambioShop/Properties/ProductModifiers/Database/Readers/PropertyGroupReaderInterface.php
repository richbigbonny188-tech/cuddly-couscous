<?php


namespace Gambio\Shop\Properties\ProductModifiers\Database\Readers;


use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

interface PropertyGroupReaderInterface
{

    public function getGroupsBySellingUnit(SellingUnitId $id);
}