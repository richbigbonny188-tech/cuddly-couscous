<?php
/*--------------------------------------------------------------------------------------------------
    MenuBoxesContentControl.php 2020-07-03
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\Core\Configuration\ConfigurationService;

/**
 * Class MenuBoxesContentControl
 */
class MenuBoxesContentControl extends DataProcessing implements MenuBoxDataContainerInterface
{
    /**
     * @var ?string
     */
    protected $account_type;
    protected $c_path;
    protected $category_id;
    protected $customer_id;
    protected $error_message;
    protected $info_message;
    protected $request_type;
    protected $show_left_column;
    protected $v_content_data = [];
    protected $coo_product;
    protected $coo_xtc_price;
    
    /**
     * @var bool
     */
    protected $v_page;
    /**
     * @var TemplateControl
     */
    protected $coo_template_control;
    
    
    /**
     * MenuBoxesContentControl constructor.
     *
     * @param TemplateControl $p_templateControl
     * @param                 $p_page
     */
    public function __construct(TemplateControl $p_templateControl, $p_page)
    {
        $this->coo_template_control                 = $p_templateControl;
        $this->v_page                               = $p_page;
        $this->show_left_column                     = $this->show_left_column();
        $this->v_content_data['SHOW_LEFT_COLUMN']   = $this->show_left_column;
        $this->v_content_data['SHOW_ONLY_SUBCATEGORIES'] = $this->show_only_subcategories();
        $this->v_content_data['CAT_MENU_LEFT'] = $this->show_categories_menu();
    }
    
    
    protected function show_left_column()
    {
        
        
        $hideOnIndex          = $this->coo_template_control->findSettingValueByName('gx-index-full-width');
        $hideOnSearch         = $this->coo_template_control->findSettingValueByName('gx-advanced-search-result-full-width');
        $hideOnContent        = $this->coo_template_control->findSettingValueByName('gx-shop-content-full-width');
        $hideOnProductInfo    = $this->coo_template_control->findSettingValueByName('gx-product-info-full-width');
        $hideOnProductListing = $this->coo_template_control->findSettingValueByName('gx-product-listing-full-width');
        $hideOnCart           = $this->coo_template_control->findSettingValueByName('gx-shopping-cart-full-width');
        $hideOnWishlist       = $this->coo_template_control->findSettingValueByName('gx-wishlist-full-width');
        $hideOnCheckout       = $this->coo_template_control->findSettingValueByName('gx-checkout-full-width');
        $hideOnAccount        = $this->coo_template_control->findSettingValueByName('gx-account-full-width');
        $hideOnLogoff         = $this->coo_template_control->findSettingValueByName('gx-logoff-full-width');
        
        if (($this->v_page === PageType::INDEX && $hideOnIndex)
            || ($this->v_page === PageType::SEARCH && $hideOnSearch)
            || ($this->v_page === PageType::CONTENT && $hideOnContent)
            || ($this->v_page === PageType::PRODUCT_INFO && $hideOnProductInfo)
            || ($this->v_page === PageType::CAT && $hideOnProductListing)
            || ($this->v_page === PageType::CART && $hideOnCart)
            || ($this->v_page === PageType::WISH_LIST && $hideOnWishlist)
            || ($this->v_page === PageType::CHECKOUT && $hideOnCheckout)
            || ($this->v_page === PageType::ACCOUNT && $hideOnAccount)
            || ($this->v_page === PageType::ACCOUNT_HISTORY && $hideOnAccount)
            || ($this->v_page === PageType::ADDRESS_BOOK_PROCESS && $hideOnAccount)
            || ($this->v_page === PageType::LOGOFF && $hideOnLogoff)) {
            
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @return bool
     */
    public function proceed()
    {
        if (StaticGXCoreLoader::getThemeControl()->isThemeSystemActive()) {
            
            include(DIR_FS_CATALOG . 'GXMainComponents/View/Boxes/boxes.php');
        } else {
            include(DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/source/boxes.php');
        }
        
        return true;
    }
    
    
    /**
     * @param string $name
     * @param        $value
     */
    public function set_content_data(string $name, $value): void
    {
        $this->v_content_data[$name] = $value;
    }
    
    
    /**
     * @return void []
     */
    public function MenuBoxData(): array
    {
        return $this->v_content_data;
    }
    
    
    /**
     *
     */
    protected function set_validation_rules()
    {
        $this->validation_rules_array['c_path']           = ['type' => '?string', 'strict' => true];
        $this->validation_rules_array['account_type']     = ['type' => '?int'];
        $this->validation_rules_array['category_id']      = ['type' => '?int'];
        $this->validation_rules_array['customer_id']      = ['type' => '?int'];
        $this->validation_rules_array['error_message']    = ['type' => 'string', 'strict' => true];
        $this->validation_rules_array['info_message']     = ['type' => 'string', 'strict' => true];
        $this->validation_rules_array['request_type']     = ['type' => '?string', 'strict' => true];
        $this->validation_rules_array['show_left_column'] = ['type' => 'bool'];
        $this->validation_rules_array['coo_product']      = ['type' => 'object', 'object_type' => 'product'];
        $this->validation_rules_array['coo_xtc_price']    = ['type' => 'object', 'object_type' => 'xtcPrice'];
    }

    /**
     * @return bool
     */
    protected function show_categories_menu(): bool
    {
        $value = $this->getConfigurationService()
            ->find("gm_configuration/CAT_MENU_LEFT")
            ->value();

        return $value === 'true';
    }
    
    /**
     * @return bool
     */
    protected function show_only_subcategories(): bool
    {
        $value = $this->getConfigurationService()
            ->find("gm_configuration/SHOW_SUBCATEGORIES")
            ->value();
    
        return $value === 'true';
    }
    
    /**
     * @return ConfigurationService
     */
    protected function getConfigurationService(): ConfigurationService
    {
        return LegacyDependencyContainer::getInstance()->get(ConfigurationService::class);
    }
}