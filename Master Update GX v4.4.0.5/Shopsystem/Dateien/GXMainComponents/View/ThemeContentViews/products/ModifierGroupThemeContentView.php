<?php
/*--------------------------------------------------------------------------------------------------
    ModifierGroupThemeContentView.php 2020-08-26
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\Shop\Attributes\ProductModifiers\Database\AttributeModifier;
use Gambio\Shop\Price\Product\Database\ValueObjects\CustomersStatusShowPrice;
use Gambio\Shop\ProductModifiers\Groups\GroupInterface;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollectionInterface;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifiersCollectionInterface;
use Gambio\Shop\ProductModifiers\Modifiers\ModifierInterface;
use Gambio\Shop\ProductModifiers\Presentation\Implementations\Image\ImageType;
use Gambio\Shop\Properties\ProductModifiers\Database\PropertyModifier;
use Gambio\Shop\SellingUnit\Unit\SellingUnitInterface;

/**
 * Class ModifierGroupThemeContentView
 */
class ModifierGroupThemeContentView extends ThemeContentView
{
    /**
     * @var GroupInterface
     */
    protected $group;
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
     * ModifierGroupThemeContentView constructor.
     *
     * @param SellingUnitInterface     $sellingUnit
     * @param CustomersStatusShowPrice $showPriceStatus
     */
    public function __construct(SellingUnitInterface $sellingUnit, CustomersStatusShowPrice $showPriceStatus)
    {
        parent::__construct(false, false);
        $this->sellingUnit = $sellingUnit;
        $this->showPriceStatus = $showPriceStatus;
    }
    
    
    /**
     * @param GroupInterface $group
     */
    public function set_group(GroupInterface $group)
    {
        $this->group = $group;
    }


    /**
     * @param ModifierIdentifierCollectionInterface $selected_modifier_ids
     */
    public function set_selected_modifier_ids(ModifierIdentifierCollectionInterface $selected_modifier_ids): void
    {
        $this->selected_modifier_ids = $selected_modifier_ids;
    }

    /**
     * @throws Exception
     */
    public function prepare_data()
    {
        $filepath   = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getProductOptionsTemplatePath();
        /*
         * Types are defined at src/GXMainComponents/Services/Core/ProductModifierDisplayTypes/ValueObjects
         */
        $templateName = strtolower($this->group->type()::type());
        $c_template = $this->get_default_template($filepath, 'modifier_group_type_', "{$templateName}.html");
        $this->set_content_template($c_template);

        $this->set_content_data('modifier', $this->parse_content_data());
        $this->set_content_data('show_additional_price', $this->showAdditionalPrice());
        

        parent::prepare_data();
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function parse_content_data()
    {
        $parsedModifiers = $this->parse_modifiers();

        return [
            'id'    => "modifier_group_{$this->group->id()->id()}",
            'name'  => "modifiers[{$this->group->id()->type()}][{$this->group->id()->id()}]",
            'type'  => $this->group->id()->type(),
            'label' => $this->group->name()->value(),
            'items' => $parsedModifiers['items'],
            'selected' => $parsedModifiers['selected']
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function parse_modifiers()
    {
        $parsedData = [
            'items' => [],
            'selected' => []
        ];

        /**
         * @var ModifierInterface $modifier
         */
        foreach ($this->group->modifiers() as $key => $modifier) {


                $data = [
                    'id'             => $modifier::source() . '_' . $modifier->id()->value(),
                    'value'          => $modifier->id()->value(),
                    'selectable'     => $modifier->selectable()->isSelectable(),
                    'info'           => $modifier->info()->label()->value(),
                    'additionalInfo' => $this->calculateAdditionalInfo($modifier),
                    'label'          => $modifier->name()->name()
                ];

                if ($modifier->selected()->isSelected()) {

                    $parsedData['selected'] = [
                        'id'             => $data['id'],
                        'value'          => $data['value'],
                        'info'           => $data['info'],
                        'label'          => $data['label'],
                        'additionalInfo' => $data['additionalInfo']
                    ];
                }


                if ($this->group->type()::type() === ImageType::type()) {
                    $data['path'] = $modifier->info()->path()->public();
                }

                $parsedData['items'][] = $data;
            }

        return $parsedData;
    }

    /**
     * @param ModifierInterface $modifier
     * @return string
     */
    protected function calculateAdditionalInfo(ModifierInterface $modifier): string
    {
        $additionalPrice = $modifier->additionalInfo()->price();
    
        if ($additionalPrice && $this->showPriceStatus->value() && $this->validatePriceStatus()) {
        
            $xtcPrice = $this->sellingUnit->xtcPrice();
            
            if ($additionalPrice > 0) {

                //  Property price already has the taxes included in its price
                $taxClassId = $modifier instanceof AttributeModifier ? $this->sellingUnit->taxInfo()->taxClassId() : 0;

                // Remove tax from property price if customer group shows net prices
                if ($xtcPrice->cStatus['customers_status_show_price_tax'] === '0'
                    && $modifier instanceof PropertyModifier) {
                    $additionalPrice = $xtcPrice->xtcRemoveTax($additionalPrice,
                                                               $xtcPrice->getTaxRateByTaxClassId($this->sellingUnit->taxInfo()
                                                                                                     ->taxClassId()));
                }
    
                if ($xtcPrice->cStatus['customers_status_discount_attributes'] == 1 &&
                    $discountAllowed = $this->sellingUnit->productInfo()->discountAllowed()->value()
                ) {
                    $additionalPrice -= $additionalPrice / 100 * $discountAllowed;
                }
                
                $priceFormatted = $xtcPrice->xtcFormat($additionalPrice, true, $taxClassId, true, true)['formated'];
    
                return $modifier->additionalInfo()->pricePrefix() . ' ' . $priceFormatted;
            }
        }
        
        return '';
    }
    
    
    /**
     * @return bool
     */
    protected function validatePriceStatus(): bool
    {
        $productId = $this->sellingUnit->id()->productId()->value();
        $xtcPrice  = $this->sellingUnit->xtcPrice();
        
        $checkPriceStatusResult = (int)$xtcPrice->gm_check_price_status($productId);
    
        return $checkPriceStatusResult === 0
               || ($checkPriceStatusResult === 2 && $xtcPrice->getPprice($productId) > 0);
    }
    
    
    /**
     * @return bool
     */
    protected function showAdditionalPrice(): bool
    {
        if ($this->group->id()->type() !== 'property') {
            
            return true;
        }
        
        return $this->sellingUnit->productInfo()->showAdditionalPriceInformation()->value();
    }
}
