<?php
/* --------------------------------------------------------------
    CheckStatusController.inc.php 2021-02-09
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
 */

use Gambio\Core\Event\EventDispatcher;
use Gambio\ProductImageList\Interfaces\ProductImageListReadServiceInterface;
use Gambio\ProductImageList\ReadService\Dtos\CombiModelAndProductsIdDto;
use Gambio\ProductImageList\ReadService\Exceptions\CombinationDoesNotHaveAListException;
use Gambio\ProductImageList\ReadService\Interfaces\CombiModelAndProductsIdDtoInterface;
use Gambio\Shop\Language\ValueObjects\LanguageId;
use Gambio\Shop\Price\Product\Database\ValueObjects\CustomersStatusShowPrice;
use Gambio\Shop\Product\ValueObjects\ProductId;
use Gambio\Shop\ProductModifiers\Database\GroupRepositoryFactory;
use Gambio\Shop\ProductModifiers\Groups\Collections\GroupCollectionInterface;
use Gambio\Shop\ProductModifiers\Helpers\ModifierTranslatorHelper;
use Gambio\Shop\ProductModifiers\Modifiers\Collections\ModifierIdentifierCollection;
use Gambio\Shop\ProductModifiers\Modifiers\Events\OnModifierIdCreateEvent;
use Gambio\Shop\SellingUnit\Images\Entities\Interfaces\SellingUnitImageCollectionInterface;
use Gambio\Shop\SellingUnit\Unit\SellingUnitInterface;
use Gambio\Shop\SellingUnit\Unit\Services\Interfaces\SellingUnitReadServiceInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\Interfaces\QuantityInterface;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\OrderInfoQuantity;
use Gambio\Shop\SellingUnit\Unit\ValueObjects\SellingUnitId;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class CheckStatusController
 *
 * @extends    HttpViewController
 * @category   System
 * @package    HttpViewControllers
 */
class CheckStatusController extends HttpViewController
{
    /**
     * @var ProductImageListReadServiceInterface
     */
    protected $readService;
    
    /**
     * @var SellingUnitInterface
     */
    protected $sellingUnit;
    protected $sellingUnitId;
    protected $modifierGroups;
    /**
     * @var GroupRepositoryFactory
     */
    protected $groupRepositoryFactory;
    
    /**
     * @var xtcPrice_ORIGIN
     */
    protected $xtcPrice;
    
    
    /**
     * @return SellingUnitInterface
     * @throws Throwable
     */
    public function sellingUnit(): SellingUnitInterface
    {
        if ($this->sellingUnit === null) {
            /**
             * @var SellingUnitReadServiceInterface $service
             */
            $service           = LegacyDependencyContainer::getInstance()->get(SellingUnitReadServiceInterface::class);
            $this->sellingUnit = $service->getSellingUnitBy($this->sellingUnitId(),
                                                            null,
                                                            null,
                                                            $this->requestedQuantity());
        }
        
        return $this->sellingUnit;
    }
    
    
    /**
     * @return QuantityInterface
     */
    public function requestedQuantity(): QuantityInterface
    {
        $products_qty = (float)str_replace(',', '.', $this->_getQueryParameter('products_qty'));
        
        return new OrderInfoQuantity($products_qty);
    }
    
    
    /**
     * @return SellingUnitId
     * @throws Throwable
     */
    public function sellingUnitId(): SellingUnitId
    {
        
        if ($this->sellingUnitId === null) {
            $productId  = new ProductId((int)$this->_getQueryParameter('products_id'));
            $languageId = new LanguageId((int)$_SESSION['languages_id']);
            /**
             * @var EventDispatcher $dispatcher
             */
            $dispatcher     = LegacyDependencyContainer::getInstance()->get(EventDispatcherInterface::class);
            $modifiers      = new ModifierIdentifierCollection();
            $queryModifiers = $this->_getQueryParameter('modifiers') ? : [];
            
            foreach ($queryModifiers as $type => $modifier) {
                foreach ($modifier as $id => $item) {
                    $event = $dispatcher->dispatch(new OnModifierIdCreateEvent($type, $item));
                    if ($event->modifierId()) {
                        $modifiers[] = $event->modifierId();
                    }
                }
            }
            $this->sellingUnitId = new SellingUnitId($modifiers, $productId, $languageId);
        }
        
        return $this->sellingUnitId;
    }
    
    
    /**
     * @return HttpControllerResponse
     * @todo use GET and POST REST-API like
     *
     * @todo get rid of old AjaxHandler
     */
    public function actionDefault()
    {
        $this->_initialize_from_modifiers();
        
        $ajaxHandler = !is_null($this->_getQueryParameter('id')) ? MainFactory::create('AttributesAjaxHandler') : false;
        
        /** @var ProductReadService $productReadService */
        $productReadService = StaticGXCoreLoader::getService('ProductRead');
        
        try {
            $product = $productReadService->getProductById(new IdType((int)$this->_getQueryParameter('products_id')));
        } catch (UnexpectedValueException $e) {
            return MainFactory::create('JsonHttpControllerResponse', ['success' => false, 'message' => $e->getMessage(), 'code' => $e->getCode()]);
        }
        $errorMessage = $this->_getQuantityChecker($product);
        
        if(STOCK_CHECK == 'true' && CHECK_STOCK_BEFORE_SHOPPING_CART === 'true') {
            if (!$errorMessage) {
                $result       = $this->_getStockChecker($product);
                $errorMessage = $result['canCheckout'] ? null : $result['message'];
            }
    
            if (!$errorMessage && $ajaxHandler) {
                $result       = $this->_getAttributeStockChecker($ajaxHandler);
                $errorMessage = $result['canCheckout'] ? null : $result['message'];
            }
        }

        $selectionTemplate = $this->getSelectionTemplate();
        $result            = $this->_getPropertiesResponseArray($selectionTemplate,
                                                                $errorMessage,
                                                                $product);
        
        if (StaticGXCoreLoader::getThemeControl()->isThemeSystemActive()) {
            $galleries         = $this->_get_product_galleries_html($product, $this->sellingUnit()->images());
            $result['content'] = array_merge($result['content'], $galleries);
        }
        
        if ($ajaxHandler) {
            $ajaxHandler = MainFactory::create('AttributesAjaxHandler');
            
            $_POST['properties_values_ids'] = $this->_getQueryParameter('properties_values_ids');
            $_POST['products_id']           = (int)$this->_getQueryParameter('products_id');
            
            $getArray = [
                'action'       => 'calculate_price',
                'products_qty' => $this->_getQueryParameter('products_qty'),
                'products_id'  => $this->_getQueryParameter('products_id'),
                'id'           => $this->_getQueryParameter('id')
            ];
            
            $ajaxHandler->set_data('GET', $getArray);
            $ajaxHandler->set_data('POST', $this->_getQueryParametersCollection()->getArray());
            $ajaxHandler->proceed();
            $result['content']['price']['value'] = $ajaxHandler->get_response();
        }
        
        if ($selectionTemplate instanceof stdClass && $selectionTemplate->status !== 'no_combi_selected') {
            $dto = $this->getCombiModelAndProductsId($selectionTemplate, $product);
            
            try {
                $imageList = $this->readService()->getImageListByCombiModelAndProductsId($dto);
            } catch (CombinationDoesNotHaveAListException $exception) {
                $imageList = null;
                unset($exception);
            }
            
            if ($imageList !== null) {
                
                $result['content']['imageList'] = $imageList;
            }
        }
    
        $vpe = '';
        if ($this->sellingUnit()->vpe()) {
            $vpe = $this->buildVpeHtml();
        }
        if (StaticGXCoreLoader::getThemeControl()->isThemeSystemActive()) {
            // SellingUnit calculates the weight of attributes & properties in combination correctly
            $result['content']['weight']['value'] = $this->getSellingUnitWeightValue();
            // SellingUnit calculates the model of attributes & properties in combination correctly
            $result['content']['model']['value'] = (string)$this->sellingUnit()->model()->value();
            // SellingUnit calculates the price of attributes & properties in combination correctly
            $result['content']['price']['value'] = $this->sellingUnit()->price()->formattedPrice()->value() . $vpe;
        }
        return MainFactory::create('JsonHttpControllerResponse', $result);
    }
    
    
    /**
     * @param stdClass               $selectionTemplate
     * @param StoredProductInterface $product
     *
     * @return CombiModelAndProductsIdDtoInterface
     */
    protected function getCombiModelAndProductsId(
        stdClass $selectionTemplate,
        StoredProductInterface $product
    ): CombiModelAndProductsIdDtoInterface {
        $productModel = $product->getProductModel();
        $pattern      = '#^' . preg_quote($productModel, '#') . '-#';
        $combiModel   = preg_replace($pattern, '', $selectionTemplate->model);
        
        return new CombiModelAndProductsIdDto($combiModel, $product->getProductId());
    }
    
    
    /**
     * @return HttpControllerResponse
     * @throws Throwable
     * @todo get rid of old AjaxHandler
     * @todo use GET and POST REST-API like
     *
     */
    public function actionAttributes()
    {
        $this->_initialize_from_modifiers();
        
        $ajaxHandler = MainFactory::create('AttributesAjaxHandler');
        
        $weight = $this->_getAttributesWeight($ajaxHandler);
        $price  = $this->_getAttributesPrice($ajaxHandler);
        $images = [
            'html'            => $this->_getAttributesImagesHtml($ajaxHandler),
            'attributes_data' => $this->_getAttributesImagesData()
        ];
        
        /** @var ProductReadService $productReadService */
        $productReadService = StaticGXCoreLoader::getService('ProductRead');
        
        try {
            $product = $productReadService->getProductById(new IdType((int)$this->_getQueryParameter('products_id')));
        } catch (UnexpectedValueException $e) {
            return MainFactory::create('JsonHttpControllerResponse', ['success' => false, 'message' => $e->getMessage(), 'code' => $e->getCode()]);
        }
        $quantityChecker = $this->_getQuantityChecker($product);
        $stockChecker    = $this->_getStockChecker($product);
        
        if (!isset($stockChecker['message'])) {
            
            $stockChecker = $this->_getAttributeStockChecker($ajaxHandler);
        }
        
        $result = $this->_getAttributesResponseArray($weight, $price, $images, $quantityChecker, $stockChecker);
        if (StaticGXCoreLoader::getThemeControl()->isThemeSystemActive()) {
            $galleries         = $this->_get_product_galleries_html($product, $this->sellingUnit()->images());
            $result['content'] = array_merge($result['content'], $galleries);
            //reads model from sellingUnit
            $result['content']['model'] = [
                'value' => (string)$this->sellingUnit()->model()->value(),
                'type' => 'html',
                'selector' => 'modelNumberText',
            ];

            $result['content']['weight'] = [
                'value' => $this->getSellingUnitWeightValue(),
                'type' => 'text',
                'selector' => 'weight',
            ];
    
            $vpe = '';
            if ($this->sellingUnit()->vpe()) {
                $vpe = $this->buildVpeHtml();
            }
            
            $result['content']['price'] = [
                'selector' => 'price',
                'type'     => 'html',
                'value'    => $this->sellingUnit()->price()->formattedPrice()->value() . $vpe
            ];
        }
        
        return MainFactory::create('JsonHttpControllerResponse', $result);
    }
    
    
    /**
     * @param mixed                   $selectionTemplate
     * @param mixed                   $combiStatus
     * @param string                  $p_quantityChecker
     * @param \StoredProductInterface $product
     *
     * @return array
     */
    protected function _getPropertiesResponseArray(
        $selectionTemplate,
        $p_quantityChecker,
        StoredProductInterface $product
    ) {
        $languageTextManager = MainFactory::create('LanguageTextManager', 'product_info');
        
        $discount = $this->_getDiscount();
        
        $result = [
            'success'     => ($selectionTemplate->combi_status->STATUS_CODE === 1
                              || $selectionTemplate->combi_status->STATUS_CODE === 2)
                             && ($selectionTemplate->status === 'stock_allowed'
                                 || $selectionTemplate->status === 'valid_quantity')
                             && empty($p_quantityChecker),
            'status_code' => $selectionTemplate->combi_status->STATUS_CODE,
            'content'     => [
                'price'                  => [
                    'selector' => 'price',
                    'type'     => 'html',
                    'value'    => $this->_formatPrice($selectionTemplate->price, $product)
                ],
                'qty'                    => [
                    'selector' => 'quantity',
                    'type'     => 'text',
                    'value'    => ($selectionTemplate->quantity > 0
                                   || $selectionTemplate->quantity === '-') ? $selectionTemplate->quantity : 0
                ],
                'shipping'               => [
                    'selector' => 'shippingTime',
                    'type'     => 'text',
                    'value'    => $selectionTemplate->shipping_status_name
                ],
                'shippingIcon'           => [
                    'selector' => 'shippingTimeImage',
                    'type'     => 'attribute',
                    'key'      => 'src',
                    'value'    => 'images/icons/status/' . $selectionTemplate->shipping_status_image
                ],
                'shippingIconAlt'        => [
                    'selector' => 'shippingTimeImage',
                    'type'     => 'attribute',
                    'key'      => 'alt',
                    'value'    => ($selectionTemplate->shipping_status_name) ? $selectionTemplate->shipping_status_name : $languageTextManager->get_text('unknown_shippingtime')
                ],
                'abroadShippingInfo' => [
                    'selector' => 'abroadShippingInfo',
                    'type'     => 'hide',
                    'value'    => !$selectionTemplate->abroad_info_link_active ? 'true' : 'false'
                ],
                'weight'                 => [
                    'selector' => 'weight',
                    'type'     => 'text',
                    'value'    => $selectionTemplate->weight
                ],
                'model'                  => [
                    'selector' => 'modelNumberText',
                    'type'     => 'html',
                    'value'    => $selectionTemplate->model
                ],
                'hideModel'              => [
                    'selector' => 'modelNumber',
                    'type'     => 'hide',
                    'value'    => (trim($selectionTemplate->model) === '') ? 'true' : 'false'
                ],
                'message'                => [
                    'selector' => 'messageCart',
                    'type'     => 'html',
                    'value'    => (!empty($selectionTemplate->message)) ? $selectionTemplate->message : $p_quantityChecker
                ],
                'messageNoCombiSelected' => [
                    'selector' => 'messageCart',
                    'type'     => 'html',
                    'value'    => $selectionTemplate->combi_status->STATUS_CODE
                                  === -1 ? $selectionTemplate->combi_status->STATUS_TEXT : ''
                ],
                'filter'                 => [
                    'selector' => StaticGXCoreLoader::getThemeControl()
                        ->isThemeSystemActive() ? 'modifiersForm' : 'propertiesForm',
                    'type'     => 'replace',
                    'value'    => $selectionTemplate->html
                ],
                'ribbon'                 => [
                    'selector' => 'ribbonSpecial',
                    'type'     => 'html',
                    'value'    => $discount
                ]
            ]
        ];
        
        if (!empty($selectionTemplate->message)) {
            $result['content']['help'] = [
                'selector' => 'messageHelp',
                'type'     => 'replace',
                'value'    => ''
            ];
        }
        
        if (isset($selectionTemplate->dont_use_properties_combis_shipping_time)
            && $selectionTemplate->dont_use_properties_combis_shipping_time === true) {
            
            if (isset($result['content']['shipping'])) {
                
                unset($result['content']['shipping']);
            }
            
            if (isset($result['content']['shippingIcon'])) {
                
                unset($result['content']['shippingIcon']);
            }
            
            if (isset($result['content']['shippingIconAlt'])) {
                
                unset($result['content']['shippingIconAlt']);
            }
        }
        
        return $result;
    }
    
    
    /**
     * @param string $p_weight
     * @param string $p_price
     * @param string $p_images
     * @param string $p_quantityChecker
     * @param string $stockChecker
     *
     * @return array
     */
    protected function _getAttributesResponseArray($p_weight, $p_price, $p_images, $p_quantityChecker, $stockChecker)
    {
        $discount = $this->_getDiscount();
        
        $messageCart = $p_quantityChecker;
        $messageCart .= !empty($messageCart) ? '<br />' : '';
        $messageCart .= $stockChecker['message'];
        
        $result = [
            'success'     => empty($p_quantityChecker) && $stockChecker['canCheckout'],
            'status_code' => 1,
            'attrImages'  => $p_images['attributes_data'],
            'content'     => [
                'weight'  => [
                    'selector' => 'weight',
                    'type'     => 'text',
                    'value'    => $p_weight
                ],
                'price'   => [
                    'selector' => 'price',
                    'type'     => 'html',
                    'value'    => $p_price
                ],
                'images'  => [
                    'selector' => 'attributeImages',
                    'type'     => 'html',
                    'value'    => $p_images['html']
                ],
                'message' => [
                    'selector' => 'messageCart',
                    'type'     => 'html',
                    'value'    => $messageCart
                ],
                'ribbon'  => [
                    'selector' => 'ribbonSpecial',
                    'type'     => 'html',
                    'value'    => $discount
                ]
            ]
        ];
        
        if (!empty($p_quantityChecker)) {
            $result['content']['help'] = [
                'selector' => 'messageHelp',
                'type'     => 'replace',
                'value'    => ''
            ];
        }
        
        return $result;
    }
    
    
    /**
     * @return string
     */
    protected function _getDiscount()
    {
        require_once DIR_FS_INC . 'xtc_get_tax_class_id.inc.php';
        
        $combiPrice = 0;
        $discount   = '';
        $xtcPrice   = $this->getXtcPrice();
        
        if (!is_null($this->_getQueryParameter('properties_values_ids'))) {
            $propertiesControl = MainFactory::create_object('PropertiesControl');
            $combiId           = $propertiesControl->get_combis_id_by_value_ids_array(xtc_get_prid($this->_getQueryParameter('products_id')),
                                                                                      $this->_getQueryParameter('properties_values_ids'));
            $combiPrice        = $xtcPrice->get_properties_combi_price($combiId);
        }
        
        $specialPrice = $xtcPrice->xtcCheckSpecial($this->_getQueryParameter('products_id')) + $combiPrice;
        $normalPrice  = $xtcPrice->getPprice($this->_getQueryParameter('products_id')) + $combiPrice;
        
        if (is_array($this->_getQueryParameter('id'))) {
            foreach ($this->_getQueryParameter('id') as $optionId => $valueId) {
                $optionPrice  = $xtcPrice->xtcGetOptionPrice($this->_getQueryParameter('products_id'),
                                                             $optionId,
                                                             $valueId);
                $specialPrice += $optionPrice['price'];
                $normalPrice  += $optionPrice['price'];
            }
        }
        
        $isSpecial = false;
        
        if ($specialPrice < $normalPrice && $specialPrice > 0) {
            $discount  = ceil(round((1 - ($specialPrice / $normalPrice)) * -100, 1));
            $isSpecial = true;
        }
        
        if ($isSpecial) {
            $discount = '<div class="ribbon-special"><span>' . $discount . '%</span></div>';
            
            return $discount;
        }
        
        return $discount;
    }
    
    
    /**
     * @param AttributesAjaxHandler $ajaxHandler
     *
     * @return mixed
     */
    protected function _getAttributesWeight(AttributesAjaxHandler $ajaxHandler)
    {
        $getArray = [
            'action'       => 'calculate_weight',
            'products_qty' => $this->_getQueryParameter('products_qty'),
            'products_id'  => $this->_getQueryParameter('products_id')
        ];
        
        if (!is_null($this->_getQueryParameter('id'))) {
            $getArray['id'] = $this->_getQueryParameter('id');
        }
        
        $ajaxHandler->set_data('GET', $getArray);
        $ajaxHandler->set_data('POST', $this->_getQueryParametersCollection()->getArray());
        $ajaxHandler->proceed();
        
        $weight = $ajaxHandler->get_response();
        
        $ajaxHandler->v_output_buffer = null;
        
        return $weight;
    }
    
    
    /**
     * @param AttributesAjaxHandler $ajaxHandler
     *
     * @return mixed
     */
    protected function _getAttributesPrice(AttributesAjaxHandler $ajaxHandler)
    {
        $productQty = $this->_getQueryParameter('products_qty');
        
        $getArray = [
            'action'       => 'calculate_price',
            'products_qty' => !empty($productQty) ? str_replace(',', '.', (string)$productQty) : '1',
            'products_id'  => $this->_getQueryParameter('products_id')
        ];
        
        if (!is_null($this->_getQueryParameter('id'))) {
            $getArray['id'] = $this->_getQueryParameter('id');
        }
        
        if (is_null($this->_getPostData('properties_values_ids'))) {
            $propertiesControl = MainFactory::create_object('PropertiesControl');
            if ((int)$propertiesControl->count_properties_to_product((int)$this->_getQueryParameter('products_id'))
                > 0) {
                $_POST['properties_values_ids'] = [];
                $_POST['products_id']           = (int)$this->_getQueryParameter('products_id');
            }
        }
        
        if (empty($ajaxHandler->v_data_array['POST']['products_qty'])) {
            $ajaxHandler->v_data_array['POST']['products_qty'] = '1';
        }
        
        $ajaxHandler->set_data('GET', $getArray);
        $ajaxHandler->proceed();
        $price = $ajaxHandler->get_response();
        
        $ajaxHandler->v_output_buffer = null;
        
        return $price;
    }
    
    
    /**
     * @param AttributesAjaxHandler $ajaxHandler
     *
     * @return mixed
     */
    protected function _getAttributesImagesHtml(AttributesAjaxHandler $ajaxHandler)
    {
        $getArray = [
            'action' => 'attribute_images'
        ];
        
        if (!is_null($this->_getQueryParameter('id'))) {
            $getArray['id'] = $this->_getQueryParameter('id');
        }
        
        $ajaxHandler->set_data('GET', $getArray);
        $ajaxHandler->proceed();
        
        return $ajaxHandler->get_response();
    }
    
    
    /**
     * @return array
     */
    protected function _getAttributesImagesData()
    {
        $optionsIds = '';
        $valuesIds  = '';
        
        if (is_array($this->_getQueryParameter('id'))) {
            foreach ($this->_getQueryParameter('id') as $optionId => $valueId) {
                $optionsIds .= 'id[' . (int)$optionId . '],';
                $valuesIds  .= (int)$valueId . ',';
            }
        } elseif (!is_null($this->_getQueryParameter('options_ids'))
                  && !is_null($this->_getQueryParameter('values_ids'))) {
            $optionsIds = $this->_getQueryParameter('options_ids');
            $valuesIds  = $this->_getQueryParameter('values_ids');
        }
        
        $attributes      = [];
        $optionsIdsArray = explode(',', substr($optionsIds, 0, -1));
        $valuesIdsArray  = explode(',', substr($valuesIds, 0, -1));
        $db              = StaticGXCoreLoader::getDatabaseQueryBuilder();
        
        foreach ($optionsIdsArray as $key => $value) {
            $from             = strpos($value, '[');
            $productOptionsId = (int)substr($value, $from + 1, -1);
            
            $result = $db->select([
                                      'po.products_options_name',
                                      'pov.products_options_values_name',
                                      'pov.gm_filename'
                                  ])
                ->from(['products_options AS po', 'products_options_values AS pov'])
                ->where([
                            'po.products_options_id' => $productOptionsId,
                            'po.language_id' => $_SESSION['languages_id'],
                            'pov.language_id' => $_SESSION['languages_id'],
                            'pov.products_options_values_id' => (int)$valuesIdsArray[$key]
                        ])
                ->limit(1)
                ->get();
            if ($result->num_rows() === 1) {
                $result = $result->row_array();
                if (!empty($result['gm_filename'])) {
                    $attributes[] = [
                        'src'   => DIR_WS_CATALOG . DIR_WS_IMAGES . 'product_images/attribute_images/'
                                   . $result['gm_filename'],
                        'title' => $result['products_options_name'] . ': ' . $result['products_options_values_name']
                    ];
                }
            }
        }
        
        return $attributes;
    }
    
    
    /**
     * @param $product_id
     * @param $properties
     *
     * @return int
     */
    protected function _get_cart_quantity($product_id, $properties)
    {
        $result = 0;
        if (array_key_exists('cart', $_SESSION)) {
            $cartProductId = $product_id;
            if (is_array($properties) && count($properties)) {
                $propertiesControl = MainFactory::create_object('PropertiesControl');
                $combiId           = $propertiesControl->get_combis_id_by_value_ids_array(xtc_get_prid($product_id),
                                                                                          $properties);
                $cartProductId     = "{$product_id}x{$combiId}";
            }
            
            $cart   = $_SESSION['cart'];
            $result = $cart->get_quantity($cartProductId);
        }
        
        return $result;
    }
    
    
    /**
     * @param StoredProduct $product
     *
     * @return string
     */
    protected function _getQuantityChecker(StoredProduct $product)
    {
        $quantityChecker = '';
        if ($product->getSettings()->getPriceStatus() === 0
            && $_SESSION['customers_status']['customers_status_show_price'] == '1'
            && (!$product->isFsk18() || $_SESSION['customers_status']['customers_fsk18_purchasable'])) {
            
            $product_id            = $this->_getQueryParameter('products_id');
            $productQty            = $this->_getQueryParameter('products_qty');
            $products_qty          = !empty($productQty) ? (double)str_replace(',', '.', (string)$productQty) : 1;
            $properties_values_ids = $this->_getQueryParameter('properties_values_ids');
            
            $cart_qty = $this->_get_cart_quantity($product_id, $properties_values_ids);
            
            $ajaxHandler = MainFactory::create('OrderAjaxHandler');
            $ajaxHandler->set_data('GET',
                                   [
                                       'action' => 'quantity_checker',
                                       'qty'    => $products_qty + $cart_qty,
                                       'id'     => $product_id
                                   ]);
            $ajaxHandler->proceed();
            $quantityChecker = $ajaxHandler->get_response();
        }
        
        return $quantityChecker;
    }
    
    
    /**
     * @param AttributesAjaxHandler $ajaxHandler
     *
     * @return array
     */
    protected function _getAttributeStockChecker(AttributesAjaxHandler $ajaxHandler)
    {
        $productId = (int)$this->_getQueryParameter('products_id');
        
        if (CHECK_STOCK_BEFORE_SHOPPING_CART === 'true' && ATTRIBUTE_STOCK_CHECK === 'true' && STOCK_CHECK === 'true'
            && xtc_has_product_attributes($productId)) {
            
            $productQty = $this->_getQueryParameter('products_qty');
            
            $getArray = [
                'action'       => 'attribute_stock',
                'products_qty' => !empty($productQty) ? str_replace(',', '.', (string)$productQty) : '1',
                'products_id'  => $this->_getQueryParameter('products_id')
            ];
            
            if (!is_null($this->_getQueryParameter('id'))) {
                $getArray['id'] = $this->_getQueryParameter('id');
            }
            
            if (is_null($this->_getPostData('properties_values_ids'))) {
                $propertiesControl = MainFactory::create_object('PropertiesControl');
                if ((int)$propertiesControl->count_properties_to_product((int)$this->_getQueryParameter('products_id'))
                    > 0) {
                    $_POST['properties_values_ids'] = [];
                    $_POST['products_id']           = (int)$this->_getQueryParameter('products_id');
                }
            }
            
            if (empty($ajaxHandler->v_data_array['POST']['products_qty'])) {
                $ajaxHandler->v_data_array['POST']['products_qty'] = '1';
            }
            
            $ajaxHandler->set_data('GET', $getArray);
            $ajaxHandler->set_data('POST', $getArray);
            $ajaxHandler->proceed();
            $product_in_stock = $ajaxHandler->get_response();
            
            $ajaxHandler->v_output_buffer = null;
            
            return $product_in_stock;
        }
        
        return [
            'canCheckout' => true,
        ];
    }
    
    
    /**
     * @param StoredProductInterface $product
     *
     * @return array
     */
    protected function _getStockChecker(StoredProductInterface $product)
    {
        $stockChecker = [
            'message'     => '',
            'canCheckout' => true,
        ];

        $propertiesControl = MainFactory::create_object('PropertiesControl');
        $numberOfProperties = $propertiesControl->count_properties_to_product($product->getProductId());
        $useProductStock = true;
        if ($product->getSettings()->getPropertiesCombisQuantityCheckMode() == 2 ||
            $product->getSettings()->getPropertiesCombisQuantityCheckMode() == 3 ||
            ($product->getSettings()->getPropertiesCombisQuantityCheckMode() === 0 && ATTRIBUTE_STOCK_CHECK == 'true' && $numberOfProperties > 0)){
            $useProductStock = false;
        }

        # Check stock only if option ist action and controller is called on product details page or in listing
        # for a product without attributes and properties
        if (CHECK_STOCK_BEFORE_SHOPPING_CART === 'true' && STOCK_CHECK === 'true' && $useProductStock
            && (!xtc_has_product_attributes((int)$this->_getQueryParameter('products_id'))
                || (bool)$this->queryParametersArray['isProductInfo'])) {
            
            if ($product->getSettings()->getPriceStatus() === 0) {
                $ajaxHandler = MainFactory::create('OrderAjaxHandler');
                
                $getArray = [
                    'action' => 'stock_checker',
                    'qty'    => $this->_getQueryParameter('products_qty'),
                    'id'     => $this->_getQueryParameter('products_id')
                ];
                
                $ajaxHandler->set_data('GET', $getArray);
                $ajaxHandler->proceed();
                $stockChecker = $ajaxHandler->get_response();
                
                # Clear stock message if checkout is possible and this controller is not called from the product details page
                if ($stockChecker['canCheckout'] && !(bool)$this->queryParametersArray['isProductInfo']) {
                    $stockChecker['message'] = '';
                }
            }
        }
        
        return $stockChecker;
    }
    
    
    /**
     * @param                         $price
     * @param \StoredProductInterface $product
     *
     * @return string
     */
    protected function _formatPrice($price, StoredProductInterface $product)
    {
        if (trim(strip_tags($price)) === GM_SHOW_PRICE_ON_REQUEST) {
            $seoBoost     = MainFactory::create_object('GMSEOBoost', [], true);
            $sefParameter = $messageBody = '';
            
            $query  = "SELECT
							content_id,
							content_title
						FROM " . TABLE_CONTENT_MANAGER . "
						WHERE
							languages_id = '" . (int)$_SESSION['languages_id'] . "' AND
							content_group = '7'";
            $result = xtc_db_query($query);
            if (xtc_db_num_rows($result)) {
                $row                 = xtc_db_fetch_array($result);
                $contactContentId    = $row['content_id'];
                $contactContentTitle = $row['content_title'];
                
                if (defined('SEARCH_ENGINE_FRIENDLY_URLS') && SEARCH_ENGINE_FRIENDLY_URLS === 'false') {
                    $sefParameter = '&content=' . xtc_cleanName($contactContentTitle);
                }
            }
            
            $subject           = GM_SHOW_PRICE_ON_REQUEST . ': '
                                 . $product->getName(new LanguageCode(new StringType($_SESSION['language_code'])));
            $propertySelection = [];
            
            $propertyValueIds = (array)$this->_getQueryParameter('properties_values_ids');
            
            foreach ($propertyValueIds as $propertyValueId) {
                $propertyValueId = (int)$propertyValueId;
                
                $sql    = 'SELECT
							`properties_name`, 
							`values_name` 
						FROM `products_properties_index` 
						WHERE 
							`products_id` = ' . (int)$this->_getQueryParameter('products_id') . ' AND
							`language_id` = ' . (int)$_SESSION['languages_id'] . ' AND
							`properties_values_id` = ' . $propertyValueId;
                $result = xtc_db_query($sql);
                if (xtc_db_num_rows($result)) {
                    $row                 = xtc_db_fetch_array($result);
                    $propertySelection[] = $row['properties_name'] . ': ' . $row['values_name'];
                }
            }
            
            if (count($propertySelection)) {
                /** @var LanguageTextManager $languageTextManager */
                $languageTextManager = MainFactory::create(LanguageTextManager::class, 'properties');
                $messageBody         = $languageTextManager->get_text('properties') . ':' . PHP_EOL . PHP_EOL;
                $messageBody         .= implode(PHP_EOL, $propertySelection) . PHP_EOL;
            }
            
            $messageBody = $messageBody === '' ? '' : '&message_body=' . rawurlencode($messageBody);
            
            if ($seoBoost->boost_content) {
                $contactUrl = xtc_href_link($seoBoost->get_boosted_content_url($contactContentId,
                                                                               $_SESSION['languages_id']) . '?subject='
                                            . rawurlencode($subject) . $messageBody);
            } else {
                $contactUrl = xtc_href_link(FILENAME_CONTENT,
                                            'coID=7&subject=' . rawurlencode($subject) . $sefParameter . $messageBody);
            }
            
            $price = '<a href="' . $contactUrl . '" class="btn btn-lg btn-price-on-request price-on-request">'
                     . GM_SHOW_PRICE_ON_REQUEST . '</a>';
        }
        
        return $price;
    }
    
    
    /**
     * @return ProductImageListReadServiceInterface
     */
    protected function readService(): ProductImageListReadServiceInterface
    {
        if ($this->readService === null) {
            
            $this->readService = StaticGXCoreLoader::getService('ProductImageListRead');
        }
        
        return $this->readService;
    }
    
    
    /**
     * Parses the new modifiers data structure to the current (old) way
     */
    protected function _initialize_from_modifiers()
    {
        if (StaticGXCoreLoader::getThemeControl()->isThemeSystemActive()) {
            ModifierTranslatorHelper::translateGlobals();
            if(empty($this->queryParametersArray['id'])){
                if($id = $_POST['id'] ?? $_GET['id'] ?? null) {
                    $this->queryParametersArray['id'] = $id;
                }
            } elseif (empty($this->queryParametersArray['modifiers'])) {
                $this->queryParametersArray['modifiers'] = $_POST['modifiers'] ?? $_GET['modifiers'] ?? null;
            }
            
            $properties_values_ids = $_POST['properties_values_ids'] ?? $_GET['properties_values_ids'] ?? null;
            if($properties_values_ids)
                $this->queryParametersArray['properties_values_ids'] = $properties_values_ids;
            }
    }
    
    
    /**
     * @param                                     $product
     * @param SellingUnitImageCollectionInterface $images
     *
     * @return mixed
     */
    protected function _get_product_galleries_html($product, SellingUnitImageCollectionInterface $images)
    {
        /* @var ProductImagesThemeContentView $view */
        $view = MainFactory::create_object('ProductImagesContentView');
        
        $filepath   = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getProductOptionsTemplatePath();
        $c_template = $view->get_default_template($filepath, 'product_info_', 'gallery_main.html');
        $view->set_content_template($c_template);
        $view->setImages($images);
        $view->setProductId((int)$this->_getQueryParameter('products_id'));
        $view->setProductName($product->getName(new LanguageCode(new StringType($_SESSION['language_code']))));
        
        $imageGallery = $view->get_html();
        
        $c_template = $view->get_default_template($filepath, 'product_info_', 'gallery_modal.html');
        $view->set_content_template($c_template);
        $imageModal = $view->get_html();
        
        return [
            'imageGallery'   => $imageGallery,
            'imageModal'     => $imageModal,
            'replaceGallery' => $this->replaceGallery($view->get_gallery_hash()),
        ];
    }
    
    
    /**
     * @param string $hash
     *
     * @return bool
     */
    protected function replaceGallery(string $hash)
    {
        $queryHash = $this->_getQueryParameter('galleryHash');
        
        return ($queryHash !== $hash);
    }
    
    
    /**
     * @return string
     */
    protected function parsePropertyValuesAsString()
    {
        $t_properties_values = $this->_getQueryParameter('properties_values_ids');
        foreach ($t_properties_values as $key => $property_value_id) {
            $propertiesValuesArray[] = $key . ':' . $property_value_id;
        }
        
        return implode('&', $propertiesValuesArray);
    }
    
    
    /**
     * @return object
     * @throws Throwable
     */
    protected function getSelectionTemplate()
    {
        
        $coo_properties_control = MainFactory::create_object('PropertiesControl');
        $propertiesValues       = $this->parsePropertyValuesAsString();
        $t_output_array         = array_merge($coo_properties_control->get_selection_data($this->_getQueryParameter('products_id'),
                                                                                          $_SESSION['languages_id'],
                                                                                          $this->_getQueryParameter('products_qty'),
                                                                                          $propertiesValues,
                                                                                          $_SESSION['currency'],
                                                                                          $_SESSION['customers_status']['customers_status_id'],
                                                                                          false,
                                                                                          StaticGXCoreLoader::getThemeControl()
                                                                                              ->isThemeSystemActive()
                                                                                          === false),
                                              ['properties_values' => $propertiesValues]);
        if (CHECK_STOCK_BEFORE_SHOPPING_CART !== 'true'
            && in_array($t_output_array['status'], ['invalid_quantity', 'stock_allowed'])) {
            $textManager = MainFactory::create('LanguageTextManager',
                                               'properties_dropdown',
                                               $_SESSION['languages_id']);
            $t_output_array['status']                    = 'valid_quantity';
            $t_output_array['message']                   = '';
            $t_output_array['combi_status']->STATUS_CODE = 1;
            $t_output_array['combi_status']->STATUS_TEXT = $textManager->get_text('available');
        }
        if (StaticGXCoreLoader::getThemeControl()->isThemeSystemActive()) {
            $t_output_array['html'] = $this->getSelectionHtmlTheme();
        }
        
        return (object)$t_output_array;
    }
    
    
    /**
     * @return string|string[]
     * @throws Throwable
     */
    protected function getSelectionHtmlTheme()
    {
        // CREATE ProductAttributesThemeContentView OBJECT
        $showPriceStatus = LegacyDependencyContainer::getInstance()->get(CustomersStatusShowPrice::class);
        $view            = $this->createModifierGroupsThemeContentView($this->sellingUnit(), $showPriceStatus);
        
        // SET TEMPLATE
        $filepath   = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getProductOptionsTemplatePath();
        $c_template = $view->get_default_template($filepath, 'product_modifiers_template_', 'group');
        $view->set_content_template($c_template);
        $view->set_modifiers_groups($this->modifierGroups());
        $view->set_selected_modifier_ids($this->sellingUnitId()->modifiers());
        
        return $view->get_html();
    }
    
    
    /**
     * @param SellingUnitInterface     $sellingUnit
     * @param CustomersStatusShowPrice $showPriceStatus
     *
     * @return ModifierGroupsThemeContentView
     */
    protected function createModifierGroupsThemeContentView(
        SellingUnitInterface $sellingUnit,
        CustomersStatusShowPrice $showPriceStatus
    ): ModifierGroupsThemeContentView {
        return MainFactory::create('ModifierGroupsThemeContentView', $sellingUnit, $showPriceStatus);
    }
    
    
    /**
     * @return GroupRepositoryFactory
     */
    protected function groupRepositoryFactory(): GroupRepositoryFactory
    {
        if ($this->groupRepositoryFactory === null) {
            $this->groupRepositoryFactory = new GroupRepositoryFactory();
        }
        
        return $this->groupRepositoryFactory;
    }
    
    
    /**
     * @return GroupCollectionInterface
     * @throws Throwable
     */
    protected function modifierGroups(): GroupCollectionInterface
    {
        if ($this->modifierGroups === null) {
            $repository           = $this->groupRepositoryFactory()->createRepository();
            $this->modifierGroups = $repository->getGroupsBySellingUnit($this->sellingUnitId(),
                                                                        new LanguageId($_SESSION['languages_id']));
        }
        
        return $this->modifierGroups;
    }
    
    /**
     * @return xtcPrice_ORIGIN
     */
    private function getXtcPrice()
    {
        if ($this->xtcPrice === null) {
            $this->xtcPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);
        }
        
        return $this->xtcPrice;
    }
    
    
    /**
     * @return string
     * @throws Throwable
     */
    protected function buildVpeHtml()
    {
        $coo_xtc_price = $this->getXtcPrice();
    
        $price    = $this->sellingUnit->price()->pricePlain()->value();
        $vpeValue = $this->sellingUnit->vpe()->value();
        $vpeName  = $this->sellingUnit->vpe()->name();
    
        $calculatedVpe = $price * (1 / $vpeValue);
        $formattedVpeValue  = $coo_xtc_price->xtcFormat($calculatedVpe, true);
        
        $vpeText = $formattedVpeValue . TXT_PER . $vpeName;
        
        return "<br /><span class='gm_products_vpe products-vpe'>{$vpeText}</span>";
    }
    
    
    /**
     * @return float|string
     * @throws Throwable
     */
    protected function getSellingUnitWeightValue()
    {
        $weight = '';
        if ($this->sellingUnit()->weight()) {
            $weight = $this->sellingUnit()->weight()->value() ? : '-';
        }
    
        return $weight;
    }
}
