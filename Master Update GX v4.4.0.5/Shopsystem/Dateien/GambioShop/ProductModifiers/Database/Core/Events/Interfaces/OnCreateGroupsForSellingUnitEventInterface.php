<?php


namespace Gambio\Shop\ProductModifiers\Database\Core\Events\Interfaces;


use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Groups\Collections\GroupCollection;
use Gambio\Shop\ProductModifiers\Groups\Collections\GroupCollectionInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

interface OnCreateGroupsForSellingUnitEventInterface
{
    public function sellingUnitId() : SellingUnitId;

    public function groups() : GroupCollectionInterface;
}