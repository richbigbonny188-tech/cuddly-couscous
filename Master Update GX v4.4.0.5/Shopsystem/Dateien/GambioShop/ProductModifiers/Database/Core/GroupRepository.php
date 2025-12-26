<?php
/*--------------------------------------------------------------------------------------------------
    GroupRepository.php 2020-06-10
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\ProductModifiers\Database\Core;

use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Database\Core\Events\OnCreateGroupsForSellingUnitEvent;
use Gambio\Shop\ProductModifiers\Groups\Collections\GroupCollection;
use Gambio\Shop\ProductModifiers\Groups\Collections\GroupCollectionInterface;
use Gambio\Shop\ProductModifiers\Groups\Repositories\GroupRepositoryInterface;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollection;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class GroupRepository
 *
 * @package Gambio\Shop\ProductModifiers\Database\Core
 */
class GroupRepository implements GroupRepositoryInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {

        $this->dispatcher = $dispatcher;
    }


    /**
     * @inheritDoc
     *
     */
    public function getGroupsByProduct(ProductId $id, LanguageId $languageId): GroupCollectionInterface
    {
        $sellingUnitId = new SellingUnitId(new ModifierIdentifierCollection([]), $id, $languageId);
        $groups        = new GroupCollection();
        $event         = new OnCreateGroupsForSellingUnitEvent($sellingUnitId, $groups);

        /** @var OnCreateGroupsForSellingUnitEvent $event */
        $event = $this->dispatcher->dispatch($event);
        return $event->groups();
    }

    /**
     * @inheritDoc
     */
    public function getGroupsBySellingUnit(SellingUnitId $id): GroupCollectionInterface
    {
        $groups = new GroupCollection();
        $event  = new OnCreateGroupsForSellingUnitEvent($id, $groups);
        /** @var OnCreateGroupsForSellingUnitEvent $event */
        $event = $this->dispatcher->dispatch($event);
        return $event->groups();
        /*
        foreach ($modifiers as $modifier) {

            $group = $groups->getById($modifier->groupId());
            if ($group) {
                $group->addModifier($modifier);
            }
        }

        $result = new GroupCollection();
        foreach ($groups as $group) {
            $result->addGroup($this->groupMapper->mapGroup($group));
        }

        return $result;
        */
    }
}