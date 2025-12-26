<?php
/*--------------------------------------------------------------------------------------------------
    ProductWidgetOutputCommand.php 2021-05-05
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

/**
 * Class ProductWidgetOutputCommand
 */
class ProductWidgetOutputCommand
{
    /**
     * @var ProductReadService
     */
    protected $productService;
    
    /**
     * @var ProductsSwiperThemeContentView
     */
    protected $contentView;
    
    /**
     * @var IdType
     */
    protected $productId;

    /**
     * @var ProductWidgetCommandConfiguration
     */
    protected $commandConfiguration;


    /**
     * @return mixed|string
     */
    public function execute()
    {
        // Do not use the MainFactory in order to avoid class overload issues
        $product     = new product($this->productId->asInt());
        $productData = $product->buildDataArray($product->data);
    
        if (is_array($productData['PRODUCTS_SHIPPING_RANGE']) && count($productData['PRODUCTS_SHIPPING_RANGE']) >= 2
            && current($productData['PRODUCTS_SHIPPING_RANGE'])['name']
               !== end($productData['PRODUCTS_SHIPPING_RANGE'])['name']) {
            
            reset($productData['PRODUCTS_SHIPPING_RANGE']);
            $productData['PRODUCTS_SHIPPING_NAME'] = $this->createShippingTimeFromRange($productData['PRODUCTS_SHIPPING_RANGE']);
        }
        
        $data = [
            "id"                     => $this->commandConfiguration->elementId(),
            "hoverable"              => $this->commandConfiguration->hoverable(),
            "template"               => "product_listing_swiper.html",
            "truncate"               => $this->commandConfiguration->truncate(),
            "showRating"             => $this->commandConfiguration->showRating(),
            "fullscreenPage"         => $this->commandConfiguration->fullscreenPage(),
            "showManufacturerImages" => $this->commandConfiguration->showManufacturerImages(),
            "showProductRibbons"     => $this->commandConfiguration->showProductRibbons(),
            "products"               => [$productData]
        ];
    
        if (StaticGXCoreLoader::getThemeControl()->isThemeSystemActive() === false) {
            
            $data ['template'] = 'snippets/product_listing/product_listing_swiper.html';
        }
        
        //  If theme is active MainFactory will create a ProductsSwiperThemeContentView class
        $this->contentView = MainFactory::create_object(ProductsSwiperContentView::class, [$data]);
        $result            = $this->contentView->get_html();
        $script            = <<<HTML
const imageElement = this;
const containerElement = imageElement.parentNode;

const hoverElement = containerElement.querySelector('[data-gambio-widget=\'product_hover\']');
const swiperElement = hoverElement.querySelector('[data-gambio-widget=\'swiper\']');

if ('{$this->commandConfiguration->elementClassName()}'.length) {
    swiperElement.classList.add('{$this->commandConfiguration->elementClassName()}');
}

const initialize = () => {
    window.gambio.widgets.init($(swiperElement));
    window.gambio.widgets.init($(hoverElement));
    imageElement.remove();
}

if (window.gambio) {
    initialize();
    return;
}

window.addEventListener('JSENGINE_INIT_FINISHED', initialize);
HTML;
        /*
         * This is a workaround for executing dynamically inserted scripts within DOM nodes such as <div> etc.
         *
         * Usually you would just insert a clean <script> tag containing all the code.
         * But due to browser-side security restrictions the inserted tag
         * would internally end up as text node in the DOM (eg. "<" would be converted to "&lt").
         *
         * In future versions, we should create an external script with handlers for such things
         * and avoid inserting scripts dynamically into the DOM.
         *
         * @todo
         */
        $result .= <<<HTML
<img src="images/pixel_trans.gif" style="opacity: 0;" alt="Transparent pixel" onload="$script" />
HTML;
        $style = '<style type="text/css">#'. $data['id'] .'.swiper-container .product-container { width: 100%; }</style>';
        $result .= $style;

        return $result;
    }


    /**
     * ProductWidgetOutputCommand constructor.
     *
     * @param IdType $productId
     * @param ProductWidgetCommandConfiguration $commandConfiguration
     * @param ProductReadService $productService
     */
    public function __construct(
        IdType $productId,
        ProductWidgetCommandConfiguration $commandConfiguration,
        ProductReadService $productService = null
    ) {
        $this->productId            = $productId;
        $this->commandConfiguration = $commandConfiguration;
        $this->productService       = $productService ?? StaticGXCoreLoader::getService('ProductRead');
    }
    
    
    /**
     * @param array $range
     *
     * @return string
     */
    protected function createShippingTimeFromRange(array $range): string
    {
        /** @var LanguageTextManager $languageTextManager */
        $languageTextManager = MainFactory::create(LanguageTextManager::class, 'product_listing');
        $textFrom            = $languageTextManager->get_text('text_from');
        $textTo              = $languageTextManager->get_text('text_to');
    
        return $textFrom . ' ' . current($range)['name'] . ' ' . $textTo . ' ' . end($range)['name'];
    }
}
