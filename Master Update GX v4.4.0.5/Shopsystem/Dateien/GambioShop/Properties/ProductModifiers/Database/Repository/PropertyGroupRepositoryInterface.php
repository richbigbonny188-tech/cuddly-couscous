<?php


namespace Gambio\Shop\Properties\ProductModifiers\Database\Repository;


use Gambio\Shop\ProductModifiers\Database\Core\DTO\Modifiers\ModifierDTOCollectionInterface;
use Gambio\Shop\ProductModifiers\Groups\Collections\GroupCollection;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

interface PropertyGroupRepositoryInterface
{
    /**
     * @param SellingUnitId $id
     *
     * @return mixed
     */
    public function getGroupsBySellingUnit(SellingUnitId $id): GroupCollection;

}