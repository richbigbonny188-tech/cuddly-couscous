<?php


namespace Gambio\Shop\ProductModifiers\Database\Core\Events;


use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Database\Core\Events\Interfaces\OnCreateGroupsForSellingUnitEventInterface;
use Gambio\Shop\ProductModifiers\Groups\Collections\GroupCollectionInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;

class OnCreateGroupsForSellingUnitEvent implements OnCreateGroupsForSellingUnitEventInterface
{

    /**
     * @var SellingUnitId
     */
    protected $sellingUnitId;
    /**
     * @var GroupCollectionInterface
     */
    protected $groups;

    /**
     * OnCreateGroupsForSellingUnitEvent constructor.
     *
     * @param SellingUnitId $sellingUnitId
     * @param GroupCollectionInterface $groups
     */
    public function __construct(SellingUnitId $sellingUnitId, GroupCollectionInterface $groups)
    {
        $this->sellingUnitId = $sellingUnitId;
        $this->groups = $groups;
    }

    /**
     * @return SellingUnitId
     */
    public function sellingUnitId(): SellingUnitId
    {
        return $this->sellingUnitId;
    }

    /**
     * @return GroupCollectionInterface
     */
    public function groups(): GroupCollectionInterface
    {
        return $this->groups;
    }
}