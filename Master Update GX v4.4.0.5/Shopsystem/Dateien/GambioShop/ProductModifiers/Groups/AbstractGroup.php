<?php
/*--------------------------------------------------------------------------------------------------
    AbstractGroup.php 2020-08-26
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\Shop\ProductModifiers\Groups;

use Gambio\Shop\ProductModifiers\Groups\ValueObjects\GroupIdentifierInterface;
use Gambio\Shop\ProductModifiers\Groups\ValueObjects\GroupName;
use Gambio\Shop\ProductModifiers\Groups\ValueObjects\GroupStatus;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifiersCollection;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifiersCollectionInterface;
use Gambio\Shop\ProductModifiers\Modifiers\ModifierInterface;
use Gambio\Shop\ProductModifiers\Presentation\Core\PresentationTypeInterface;

/**
 * Class AbstractGroup
 * @package Gambio\Shop\ProductModifiers\Groups
 */
abstract class AbstractGroup implements GroupInterface
{
    /**
     * @var GroupIdentifierInterface
     */
    protected $id;
    /**
     * @var ModifiersCollectionInterface
     */
    protected $modifiers;
    /**
     * @var GroupName
     */
    protected $name;
    /**
     * @var PresentationTypeInterface
     */
    protected $type;

    /**
     * @var bool
     */
    protected $selected;

    /**
     * @var GroupStatus|null
     */
    protected   $status;


    /**
     * AbstractGroup constructor.
     *
     * @param GroupIdentifierInterface $id
     * @param PresentationTypeInterface $type
     * @param GroupName $name
     * @param GroupStatus|null $status
     * @param ModifiersCollectionInterface $modifiers
     */
    public function __construct(
        GroupIdentifierInterface $id,
        PresentationTypeInterface $type,
        GroupName $name,
        ?GroupStatus $status,
        ModifiersCollectionInterface $modifiers
    ) {
        $this->id        = $id;
        $this->name      = $name;
        $this->type      = $type;
        $this->modifiers = $modifiers;
        $this->setStatus($status);
    }

    /**
     * @param GroupStatus|null $status
     */
    protected function setStatus(?GroupStatus $status) {
        /**
         * This kind of validation should be preferably be executed in the factory,
         * but since there is no a factory but a mapper it can be executed in the
         * domain object, in order to make sure that the business rule will always be obeyed
         */

        $haveSomeSelectableModifier = false;
        /** @var ModifierInterface $modifier */
        foreach ($this->modifiers as $modifier) {
            if($modifier->selectable()->isSelectable() || $modifier->selected()->isSelected()){
                $haveSomeSelectableModifier = true;
                break;
            }
        }
        if($status && $status->isSelectable() && !$haveSomeSelectableModifier) {
            $status = new GroupStatus(false);
        }
        $this->status = $status;
    }


    
    /**
     * @inheritDoc
     */
    public function id(): GroupIdentifierInterface
    {
        return $this->id;
    }
    
    
    /**
     * @inheritDoc
     */
    public function modifiers(): ModifiersCollectionInterface
    {
        return new ModifiersCollection($this->modifiers);
    }
    
    
    /**
     * @inheritDoc
     */
    public function name(): GroupName
    {
        return $this->name;
    }
    
    
    /**
     * @inheritDoc
     */
    public function type(): PresentationTypeInterface
    {
        return $this->type;
    }
    
    
    /**
     * @return GroupStatus
     */
    public function status(): GroupStatus
    {
        return $this->status;
    }

    public function isSelected(): bool
    {
        if($this->selected === null) {
            $this->selected = false;
            /** @var ModifierInterface $modifier */
            foreach($this->modifiers() as $modifier) {
                if ($modifier->selected()) {
                    $this->selected = true;
                    break;
                }
            }
        }
        return $this->selected;
    }


}