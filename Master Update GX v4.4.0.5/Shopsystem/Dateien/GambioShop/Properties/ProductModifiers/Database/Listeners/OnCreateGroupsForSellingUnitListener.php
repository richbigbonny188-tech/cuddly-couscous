<?php


namespace Gambio\Shop\Properties\ProductModifiers\Database\Listeners;


use Gambio\Shop\ProductModifiers\Database\Core\Events\Interfaces\OnCreateGroupsForSellingUnitEventInterface;
use Gambio\Shop\Properties\ProductModifiers\Database\Repository\PropertyGroupRepositoryInterface;

class OnCreateGroupsForSellingUnitListener
{
    /**
     * @var PropertyGroupRepositoryInterface
     */
    private $propertyGroupRepository;

    public function __invoke(OnCreateGroupsForSellingUnitEventInterface $event) : OnCreateGroupsForSellingUnitEventInterface
    {
        $groups = $this->propertyGroupRepository->getGroupsBySellingUnit($event->sellingUnitId());
        if($groups->count()){
            $event->groups()->addGroups($groups);
        }
        return $event;
    }

    public function __construct(PropertyGroupRepositoryInterface $propertyGroupRepository)
    {

        $this->propertyGroupRepository = $propertyGroupRepository;
    }
}