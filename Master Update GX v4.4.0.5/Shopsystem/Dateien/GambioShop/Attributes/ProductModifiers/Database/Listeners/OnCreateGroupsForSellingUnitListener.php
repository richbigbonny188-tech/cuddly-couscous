<?php
/*--------------------------------------------------------------------------------------------------
    OnCreateGroupsForSellingUnitListener.php 2020-08-04
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/
namespace Gambio\Shop\Attributes\ProductModifiers\Database\Listeners;

use Gambio\Shop\ProductModifiers\Database\Core\Events\Interfaces\OnCreateGroupsForSellingUnitEventInterface;
use Gambio\Shop\Attributes\ProductModifiers\Database\Repository\AttributeGroupRepositoryInterface;

class OnCreateGroupsForSellingUnitListener
{
    /**
     * @var AttributeGroupRepositoryInterface
     */
    private $attributeGroupRepository;
    
    public function __invoke(OnCreateGroupsForSellingUnitEventInterface $event) : OnCreateGroupsForSellingUnitEventInterface
    {
        $groups = $this->attributeGroupRepository->getGroupsBySellingUnit($event->sellingUnitId());
        if($groups->count()){
            $event->groups()->addGroups($groups);
        }
        return $event;
    }
    
    
    /**
     * OnCreateGroupsForSellingUnitListener constructor.
     *
     * @param AttributeGroupRepositoryInterface $propertyGroupRepository
     */
    public function __construct(AttributeGroupRepositoryInterface $propertyGroupRepository)
    {
        
        $this->attributeGroupRepository = $propertyGroupRepository;
    }
}