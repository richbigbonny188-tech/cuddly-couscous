<?php
/*--------------------------------------------------------------------------------------------------
    ProductListContentViewGenerator.php 2019-09-05
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2019 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

/**
 * Class representing the content view for the product list widget
 */
class ProductListContentViewGenerator
{
    /**
     * Generate the HTML output of the product slider
     *
     * @param ProductListWidgetCommandConfiguration $commandConfiguration
     *
     * @param array $products Product array
     * @param string $template Template file
     * @param array $swiperBreakpoints
     * @return string
     */
    public static function generate(
        ProductListWidgetCommandConfiguration $commandConfiguration,
        array $products,
        string $template,
        array $swiperBreakpoints = []
    ) {
        $data = [
            "id"                     => $commandConfiguration->elementId(),
            "hoverable"              => $commandConfiguration->hoverable(),
            "template"               => $template,
            "truncate"               => $commandConfiguration->truncate(),
            "showRating"             => $commandConfiguration->showRating(),
            "fullscreenPage"         => $commandConfiguration->fullscreenPage(),
            "showManufacturerImages" => $commandConfiguration->showManufacturerImages(),
            "showProductRibbons"     => $commandConfiguration->showProductRibbons(),
            "products"               => $products,
            "swiperOptions"          => [
                "slidesPerView" => max(1, min($commandConfiguration->itemsPerRowLg(), count($products) - 1)),
                "autoplay" => false,
                'usePreviewBullets' => true,
                'centeredSlides'    => false,
                'breakpoints' => $swiperBreakpoints
            ]
        ];
    
        if (StaticGXCoreLoader::getThemeControl()->isThemeSystemActive() === false) {
        
            $data ['template'] = 'snippets/product_listing/' . $template;
        }
    
        //  If theme is active MainFactory will create a ProductsSwiperThemeContentView class
        $contentView = MainFactory::create_object(ProductsSwiperContentView::class, [$data]);
        $result      = $contentView->get_html();
        
        $script = <<<HTML
const imageElement = this;
const containerElement = imageElement.parentNode;

const hoverElement = containerElement.querySelector('[data-gambio-widget=\'product_hover\']');
const swiperElement = hoverElement.querySelector('[data-gambio-widget=\'swiper\']');

if ('{$commandConfiguration->elementClassName()}'.length) {
    swiperElement.classList.add('{$commandConfiguration->elementClassName()}');
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
        
        return $result;
    }
}