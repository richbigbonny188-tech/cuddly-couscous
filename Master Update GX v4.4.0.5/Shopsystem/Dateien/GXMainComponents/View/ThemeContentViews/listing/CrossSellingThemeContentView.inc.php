<?php
/* --------------------------------------------------------------
   CrossSellingThemeContentView.inc.php 2021-03-04
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------

   (c) 2005 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: cross_selling.php 1243 2005-09-25 09:33:02Z mz $)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(also_purchased_products.php,v 1.21 2003/02/12); www.oscommerce.com
   (c) 2003	 nextcommerce (also_purchased_products.php,v 1.9 2003/08/17); www.nextcommerce.org
   ---------------------------------------------------------------------------------------*/

/**
 * Class CrossSellingThemeContentView
 */
class CrossSellingThemeContentView extends ThemeContentView
{
    protected $coo_product;
    protected $type = 'cross_selling';
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->set_flat_assigns(true);
    }
    
    
    public function prepare_data()
    {
        $this->build_html      = false;
        $t_uninitialized_array = $this->get_uninitialized_variables([
                                                                        'coo_product',
                                                                        'type'
                                                                    ]);
        
        if (empty($t_uninitialized_array)) {
            $t_data_array = $this->get_data();
            $this->add_data($t_data_array);
        } else {
            trigger_error("Variable(s) " . implode(', ',
                                                   $t_uninitialized_array) . " do(es) not exist in class "
                          . get_class($this) . " or is/are null",
                          E_USER_ERROR);
        }
    }
    
    
    /**
     * @return array
     */
    protected function get_data()
    {
        switch ($this->type) {
            case 'cross_selling':
                $this->set_content_template('product_info_cross_selling.html');
                
                return $this->coo_product->getCrossSells();
            
            case 'reverse_cross_selling':
                $this->set_content_template('product_info_reverse_cross_selling.html');
                
                return $this->coo_product->getReverseCrossSells();
            
            default:
                return [];
        }
    }
    
    
    protected function add_data($p_data_array)
    {
        if (is_array($p_data_array) && count($p_data_array) > 0) {
            $this->build_html = true;
            
            if ($this->type === 'cross_selling') {
                
                $crossSellProducts = [];
                foreach ($p_data_array as $data) {
                    $crossSellProducts = $data['PRODUCTS'];
                }
                
                $this->set_content_data('module_content', $this->generateCrossSellListing($crossSellProducts));
            } else {
                $this->set_content_data('module_content', $this->generateCrossSellListing($p_data_array));
            }
        }
    }
    
    
    /**
     * @param product $product
     */
    public function set_coo_product(product $product)
    {
        $this->coo_product = $product;
    }
    
    
    /**
     * @return product
     */
    public function get_coo_product()
    {
        return $this->coo_product;
    }
    
    
    /**
     * @param string $p_type
     */
    public function set_type($p_type)
    {
        $this->type = (string)$p_type;
    }
    
    
    /**
     * @return string
     */
    public function get_type()
    {
        return $this->type;
    }
    
    
    protected function generateCrossSellListing($products)
    {
        $showRating = false;
        if (gm_get_conf('ENABLE_RATING') === 'true' && gm_get_conf('SHOW_RATING_IN_GRID_AND_LISTING') === 'true') {
            $showRating = true;
        }
        
        $showManufacturerImages = gm_get_conf('SHOW_MANUFACTURER_IMAGE_LISTING');
        $showProductRibbons     = gm_get_conf('SHOW_PRODUCT_RIBBONS');
        
        $fullscreenPage = $GLOBALS['coo_template_control']->findSettingValueByName('gx-product-info-full-width');
        
        $swiperData = [
            'products'               => $products,
            'id'                     => $this->type,
            'truncate'               => gm_get_conf('TRUNCATE_PRODUCTS_NAME'),
            'showRating'             => $showRating,
            'fullscreenPage'         => $fullscreenPage,
            'showManufacturerImages' => $showManufacturerImages,
            'showProductRibbons'     => $showProductRibbons,
            "swiperOptions"          => [
                "slidesPerView" => 5,
                "autoplay" => false,
                'usePreviewBullets' => true,
                'centeredSlides'    => false,
                'breakpoints' => [
                    480 => [
                        'usePreviewBullets' => true,
                        'slidesPerView'     => 1,
                        'centeredSlides'    => true
                    ],
                    768 => [
                        'usePreviewBullets' => true,
                        'slidesPerView'     => 2,
                        'centeredSlides'    => false
                    ],
                    992 => [
                        'usePreviewBullets' => true,
                        'slidesPerView'     => 3,
                        'centeredSlides'    => false
                    ],
                    1200 => [
                        'usePreviewBullets' => true,
                        'slidesPerView'     => 5,
                        'centeredSlides'    => false
                    ],
                    10000 => [
                        'usePreviewBullets' => true,
                        'slidesPerView'     => 5, // default slides per view
                        'centeredSlides'    => false
                    ]
                ]
            ]
        ];
        
        $swiperHtml = MainFactory::create_object('ProductsSwiperThemeContentView', [$swiperData]);
        
        return $swiperHtml->get_html();
    }
}
