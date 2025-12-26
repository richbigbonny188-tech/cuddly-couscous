<?php
/*------------------------------------------------------------------------------
 SellingUnitIdWithDefaultValuesBuilder.php 2020-11-09
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 -----------------------------------------------------------------------------*/

namespace Gambio\Shop\SellingUnit\Unit\Builders;

use Gambio\Core\Event\EventDispatcher;
use Gambio\Shop\ProductModifiers\Database\Core\Events\OnCreateGroupsForSellingUnitEvent;
use Gambio\Shop\ProductModifiers\Groups\Collections\GroupCollection;
use Gambio\Shop\ProductModifiers\Groups\GroupInterface;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollection;
use Gambio\Shop\ProductModifiers\Modifiers\ModifierInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use Psr\EventDispatcher\EventDispatcherInterface;

class SellingUnitIdWithDefaultValuesBuilder extends SellingUnitIdBuilder
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;
    
    
    public function __construct(EventDispatcherInterface$dispatcher)
    {
        parent::__construct();
        $this->dispatcher = $dispatcher;
    }
    
    
    /**
     * @return SellingUnitId
     * @throws \Throwable
     */
    public function build(): SellingUnitId
    {
        $unit = parent::build();
        /** @var OnCreateGroupsForSellingUnitEvent $event */
        $event = $this->dispatcher->dispatch(new OnCreateGroupsForSellingUnitEvent($unit, new GroupCollection()));
        $list  = [];
        
        foreach ($unit->modifiers() as $modifierId){
            $list [$modifierId->value().'_'.$modifierId->type()] = $modifierId;
        }
        
        /** @var GroupInterface $group */
        foreach ($event->groups() as $group) {
            /** @var ModifierInterface $modifier */
            foreach ($group->modifiers() as $modifier) {
                if($modifier->selected()->isSelected())
                    $list [$modifier->id()->value().'_'.$modifier->id()->type()] = $modifier->id();
            }
        }
        
        return new SellingUnitId(
            new ModifierIdentifierCollection($list),
            $unit->productId(),
            $unit->language()
        );
        
    }

    
    
}