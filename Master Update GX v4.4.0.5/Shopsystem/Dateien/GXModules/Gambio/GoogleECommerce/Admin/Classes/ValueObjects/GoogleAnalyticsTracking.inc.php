<?php
/* --------------------------------------------------------------
   GoogleAnalyticsTracking.php 2018-04-13
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2018 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class GoogleAnalyticsTracking
 *
 * @package GoogleAnalytics
 */
class GoogleAnalyticsTracking implements GoogleAnalyticsTrackingInterface
{
    /**
     * Valid google analytic tracking types. Use on of the constants below for instantiation.
     */
    const BOX_IMPRESSION     = 'box-impression';
    const LISTING_IMPRESSION = 'listing-impression';
    const PRODUCT_CLICK      = 'product-click';
    const PRODUCT_DETAILS    = 'product-details';
    const SHOPPING_CART      = 'shopping-cart';
    const CHECKOUT           = 'checkout';
    
    /**
     * @var string
     */
    protected $type;
    
    /**
     * @var array
     */
    protected static $validTypes = [
        'box-impression',
        'listing-impression',
        'product-click',
        'product-details',
        'shopping-cart',
        'checkout'
    ];
    
    
    /**
     * GoogleAnalyticsTracking constructor.
     * Protected to enforce usage of named constructor.
     *
     * @param string $type Tracking type, use one of the constants.
     *
     * @throws InvalidTrackingException If invalid type was provided.
     */
    protected function __construct($type)
    {
        if (!in_array($type, static::$validTypes)) {
            throw new InvalidTrackingException('Invalid tracking type "' . $type . '" provided. Valid types are "'
                                               . implode('"', static::$validTypes) . '".');
        }
        $this->type = $type . '-tracking';
    }
    
    
    /**
     * Named constructor of GoogleAnalyticsTracking.
     *
     * @param string $type Tracking type, use one of the constants.
     *
     * @return static|GoogleAnalyticsTracking New instance.
     */
    public static function create($type)
    {
        return new static($type);
    }
    
    
    /**
     * Creates a box impression tracking instance.
     *
     * @return static|GoogleAnalyticsTracking New box impression instance.
     */
    public static function boxImpression()
    {
        return new static(static::BOX_IMPRESSION);
    }
    
    
    /**
     * Creates a list impression tracking instance.
     *
     * @return static|GoogleAnalyticsTracking New list impression instance.
     */
    public static function listImpression()
    {
        return new static(static::LISTING_IMPRESSION);
    }
    
    
    /**
     * Creates a product click tracking instance.
     *
     * @return static|GoogleAnalyticsTracking New product click instance.
     */
    public static function productClicks()
    {
        return new static(static::PRODUCT_CLICK);
    }
    
    
    /**
     * Creates a box product details instance.
     *
     * @return static|GoogleAnalyticsTracking New product details instance.
     */
    public static function productDetails()
    {
        return new static(static::PRODUCT_DETAILS);
    }
    
    
    /**
     * Creates a shopping cart tracking instance.
     *
     * @return static|GoogleAnalyticsTracking New box impression instance.
     */
    public static function shoppingCart()
    {
        return new static(static::SHOPPING_CART);
    }
    
    
    /**
     * Creates a shopping cart tracking instance.
     *
     * @return static|GoogleAnalyticsTracking New box impression instance.
     */
    public static function checkout()
    {
        return new static(static::CHECKOUT);
    }
    
    
    /**
     * Returns the string representation of the tracking type.
     *
     * @return string
     */
    public function trackingType()
    {
        return $this->type;
    }
}