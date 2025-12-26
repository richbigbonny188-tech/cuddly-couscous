<?php
/* --------------------------------------------------------------
   AlsoPurchasedThemeContentView.inc.php 2021-03-31
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------


   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(also_purchased_products.php,v 1.21 2003/02/12); www.oscommerce.com
   (c) 2003	 nextcommerce (also_purchased_products.php,v 1.9 2003/08/17); www.nextcommerce.org
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: also_purchased_products.php 1243 2005-09-25 09:33:02Z mz $)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/**
 * Class AlsoPurchasedThemeContentView
 */
class AlsoPurchasedThemeContentView extends ThemeContentView
{
    protected $coo_product;
    protected $data_array;
    
    
    function __construct()
    {
        parent::__construct();
        
        $this->set_content_template('product_info_related_products.html');
        $this->set_flat_assigns(true);
    }
    
    
    function prepare_data()
    {
        $t_uninitialized_array = $this->get_uninitialized_variables(['coo_product']);
        
        if (empty($t_uninitialized_array)) {
            $this->get_data();
            if (count($this->data_array) >= MIN_DISPLAY_ALSO_PURCHASED && count($this->data_array) > 0) {
                $this->add_data();
            } else {
                $this->build_html = false;
            }
        } else {
            trigger_error("Variable(s) " . implode(', ',
                                                   $t_uninitialized_array) . " do(es) not exist in class "
                          . get_class($this) . " or is/are null",
                          E_USER_ERROR);
        }
    }
    
    
    protected function get_data()
    {
        $this->data_array = $this->coo_product->getAlsoPurchased();
    }
    
    
    protected function add_data()
    {
        $this->content_array['module_content'] = $this->generateAlsoPurchasedListing();
    }
    
    
    /**
     * @param array $p_data_array
     */
    public function set_data_array(array $p_data_array)
    {
        $this->data_array = $p_data_array;
    }
    
    
    /**
     * @return array
     */
    public function get_data_array()
    {
        return $this->data_array;
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
     * @return mixed|string
     */
    protected function generateAlsoPurchasedListing()
    {
        if (!empty($this->data_array)) {
            $showRating = false;
            if (gm_get_conf('ENABLE_RATING') === 'true' && gm_get_conf('SHOW_RATING_IN_GRID_AND_LISTING') === 'true') {
                $showRating = true;
            }
            
            $fullscreenPage = $GLOBALS['coo_template_control']->findSettingValueByName('gx-product-info-full-width');
            
            $swiperData = [
                'products'       => $this->data_array,
                'id'             => 'also_purchased',
                'truncate'       => gm_get_conf('TRUNCATE_PRODUCTS_NAME'),
                'showRating'     => $showRating,
                'fullscreenPage' => $fullscreenPage,
                "swiperOptions" => [
                    "slidesPerView" => 5,
                    "autoplay" => false,
                    'usePreviewBullets' => true,
                    'centeredSlides' => false,
                    'breakpoints' => [
                        480 => [
                            'usePreviewBullets' => true,
                            'slidesPerView' => 1,
                            'centeredSlides' => true
                        ],
                        768 => [
                            'usePreviewBullets' => true,
                            'slidesPerView' => 2,
                            'centeredSlides' => false
                        ],
                        992 => [
                            'usePreviewBullets' => true,
                            'slidesPerView' => 3,
                            'centeredSlides' => false
                        ],
                        1200 => [
                            'usePreviewBullets' => true,
                            'slidesPerView' => 5,
                            'centeredSlides' => false
                        ],
                        10000 => [
                            'usePreviewBullets' => true,
                            'slidesPerView' => 5, // default slides per view
                            'centeredSlides' => false
                        ]
                    ]
                ]
            ];
            
            $swiperHtml = MainFactory::create_object('ProductsSwiperThemeContentView', [$swiperData]);
            
            return $swiperHtml->get_html();
        }
        
        return false;
    }
}
