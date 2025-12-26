<?php

/* --------------------------------------------------------------
   GoogleAnalyticsApplicationBottom.inc.php 2020-07-17
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class representing the application bottom extender overload for Google Analytics
 */
class GoogleAnalyticsApplicationBottom extends GoogleAnalyticsApplicationBottom_parent
{
    /**
     * Configuration read service
     * @var GoogleAnalyticsConfigurationReadService
     */
    private $configReadService;
    
    /**
     * Tracking service
     * @var GoogleAnalyticsTrackingService
     */
    private $trackingService;
    
    
    /**
     * Proceed
     */
    public function proceed()
    {
        parent::proceed();
        
        $this->configReadService = GoogleAnalyticsConfigurationServiceFactory::readService();
        $this->trackingService   = GoogleAnalyticsServiceFactory::trackingService();
        
        if (!$this->configReadService->enabled()) {
            return;
        }
        
        switch ($this->v_page) {
            case PageType::PRODUCT_INFO:
                $this->addProductDetailSnippets();
                break;
            case PageType::CAT:
                $this->addCategorySnippets();
                break;
            case PageType::CART:
                $this->addCartSnippets();
                break;
        }
    }
    
    
    /**
     * Add the snippets for the product info
     */
    private function addProductDetailSnippets()
    {
        $isProductDetailsTrackingEnabled = $this->configReadService->trackingEnabled(GoogleAnalyticsTracking::productDetails());
        $isCartTrackingEnabled           = $this->configReadService->trackingEnabled(GoogleAnalyticsTracking::shoppingCart());
        
        if (!$isProductDetailsTrackingEnabled) {
            return;
        }
        
        if (array_key_exists('pageNotFound', $GLOBALS)) {
            // we are using application_top.php instead of application_top_main.php
            if ($GLOBALS['pageNotFound']) {
                return false;
            }
        }
        
        if (!array_key_exists('products_id', $this->v_data_array) || (int)$this->v_data_array['products_id'] === 0) {
            return;
        }
        
        $data = $this->trackingService->encodedProductByProductId(new IdType((int)$this->v_data_array['products_id']),
                                                                  new IdType($_SESSION['languages_id']),
                                                                  new IdType($_SESSION['customers_status']['customers_status_id']));
        
        $this->v_output_buffer[] = MainFactory::create('GoogleAnalyticsProductDetailSnippet', $data)->get_html();
        
        if (!$isCartTrackingEnabled) {
            return;
        }
        
        $this->v_output_buffer[] = MainFactory::create('GoogleAnalyticsProductDetailAddToCartSnippet')->get_html();
    }
    
    
    /**
     * Add the snippets for the category
     */
    private function addCategorySnippets()
    {
        $this->v_output_buffer[] = MainFactory::create('GoogleAnalyticsCategoryAddToCartSnippet')->get_html();
    }
    
    
    /**
     * Add the snippets for the cart change action
     */
    public function addCartSnippets()
    {
        if (!$this->configReadService->trackingEnabled(GoogleAnalyticsTracking::shoppingCart())) {
            return;
        }
        
        $this->v_output_buffer[] = MainFactory::create('GoogleAnalyticsShoppingCartChangeSnippet')->get_html();
        
        if (!$this->configReadService->trackingEnabled(GoogleAnalyticsTracking::checkout())) {
            return;
        }
        
        $this->v_output_buffer[] = MainFactory::create('GoogleAnalyticsGoToCheckoutSnippet')->get_html();
    }
}