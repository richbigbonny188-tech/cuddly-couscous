<?php
/*--------------------------------------------------------------------------------------------------
    ModifierGroupsThemeContentView.php 2021-05-03
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\Shop\Price\Product\Database\ValueObjects\CustomersStatusShowPrice;
use Gambio\Shop\ProductModifiers\Groups\Collections\GroupCollectionInterface;
use Gambio\Shop\ProductModifiers\Groups\GroupInterface;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifiersCollectionInterface;
use Gambio\Shop\Properties\ProductModifiers\Database\PropertyGroup;
use Gambio\Shop\SellingUnit\Unit\SellingUnitInterface;

/**
 * Class ModifierGroupsThemeContentView
 */
class ModifierGroupsThemeContentView extends ThemeContentView
{
    /**
     * @var array
     */
    protected $groupsHtml = [];
    /**
     * @var IdType
     */
    protected $language_id;
    /**
     * @var ModifiersCollectionInterface
     */
    protected $modifiersGroups;
    /**
     * @var ModifierIdentifierCollectionInterface
     */
    protected $selected_modifier_ids;
    
    /**
     * @var SellingUnitInterface
     */
    protected $sellingUnit;
    /**
     * @var CustomersStatusShowPrice
     */
    protected $showPriceStatus;
    
    
    /**
     * ModifierGroupsThemeContentView constructor.
     *
     * @param SellingUnitInterface     $sellingUnit
     * @param CustomersStatusShowPrice $showPriceStatus
     */
    public function __construct(SellingUnitInterface $sellingUnit, CustomersStatusShowPrice $showPriceStatus)
    {
        parent::__construct();
        $this->set_flat_assigns(true);
        $this->sellingUnit     = $sellingUnit;
        $this->showPriceStatus = $showPriceStatus;
    }
    
    
    public function prepare_data()
    {
        /**
         * @var GroupInterface $group
         */
        foreach ($this->modifiersGroups as $group) {
            
            $view = $this->createModifierGroupThemeContentView($this->sellingUnit, $this->showPriceStatus);
            $view->set_group($group);
            $view->set_selected_modifier_ids($this->selected_modifier_ids);
            $this->groupsHtml[$group::source()][] = $view->get_html();
        }
        $this->set_content_data('groups', $this->groupsHtml);
    }
    
    
    /**
     * @param GroupCollectionInterface $groups
     */
    public function set_modifiers_groups(
        GroupCollectionInterface $groups
    ): void {
        $this->modifiersGroups = $groups;
    }
    
    
    /**
     * @param ModifierIdentifierCollectionInterface $modifier_ids
     */
    public function set_selected_modifier_ids(
        ModifierIdentifierCollectionInterface $modifier_ids
    ): void {
        $this->selected_modifier_ids = $modifier_ids;
    }
    
    
    /**
     * @param SellingUnitInterface $sellingUnit
     * @param CustomersStatusShowPrice $showPriceStatus
     *
     * @return ModifierGroupThemeContentView
     */
    protected function createModifierGroupThemeContentView(
        SellingUnitInterface $sellingUnit,
        CustomersStatusShowPrice $showPriceStatus
    ): ModifierGroupThemeContentView {
        return MainFactory::create('ModifierGroupThemeContentView', $sellingUnit, $showPriceStatus);
    }
}