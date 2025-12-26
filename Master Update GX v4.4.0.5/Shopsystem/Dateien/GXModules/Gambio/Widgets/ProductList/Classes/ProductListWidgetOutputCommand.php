<?php
/*--------------------------------------------------------------------------------------------------
    ProductListWidgetOutputCommand.php 2021-06-09
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2021 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

/**
 * Class ProductListWidgetOutputCommand
 */
class ProductListWidgetOutputCommand
{
    /**
     * @var ProductReadService
     */
    protected $productReadService;

    /**
     * @var VPEReadService
     */
    protected $vpeReadService;

    /**
     * @var ProductsShippingStatusSource
     */
    protected $productsShippingStatusSource;

    /**
     * @var xtcPrice_ORIGIN|mixed
     */
    protected $xtcPrice;

    /**
     * @var FeaturedProductReaderInterface
     */
    protected $featuredProductReadService;

    /**
     * @var array
     */
    protected $products;

    /**
     * @var IntType
     */
    protected $customerGroupId;

    /**
     * @var BoolType
     */
    protected $isFsk18Purchasable;

    /**
     * @var ProductListWidgetCommandConfiguration
     */
    protected $commandConfiguration;

    /**
     * @var GMSEOBoost_ORIGIN
     */
    protected $gmSeoBoost;

    const SLIDES_PER_PREVIEW = [
        1  => 12,
        2  => 6,
        3  => 4,
        4  => 3,
        6  => 2,
        12 => 1
    ];


    /**
     * Create ProductListWidgetOutputCommand instance
     *
     * @param ProductListWidgetCommandConfiguration $commandConfiguration
     * @param array                                 $products
     * @param ProductReadService|null               $productReadService           Product read service
     * @param VPEReadService|null                   $vpeReadService               VPE read service
     * @param ProductsShippingStatusSource|null     $productsShippingStatusSource Product shipping status source
     * @param xtcPrice_ORIGIN|null                  $xtcPrice                     Pricing format class
     * @param FeaturedProductReaderInterface|null   $featuredProductReadService
     * @param GMSEOBoost_ORIGIN|null     $gmSeoBoost
     */
    public function __construct(
        ProductListWidgetCommandConfiguration $commandConfiguration,
        array $products,
        ProductReadService $productReadService = null,
        VPEReadService $vpeReadService = null,
        ProductsShippingStatusSource $productsShippingStatusSource = null,
        PriceDataInterface $xtcPrice = null,
        FeaturedProductReaderInterface $featuredProductReadService = null,
        UrlKeywordsRepairerInterface $gmSeoBoost = null
    )
    {
        $this->commandConfiguration = $commandConfiguration;
        $this->customerGroupId = new IntType($_SESSION['customers_status']['customers_status_id'] ?? 0);
        $this->isFsk18Purchasable = new BoolType($_SESSION['customers_status']['customers_fsk18_display'] === '1');

        $this->products = $products;
        $this->productReadService = $productReadService ?? StaticGXCoreLoader::getService('ProductRead');
        $this->vpeReadService = $vpeReadService ?? StaticGXCoreLoader::getService('VPERead');
        $this->featuredProductReadService = $featuredProductReadService ??
            StaticGXCoreLoader::getService('FeaturedProductRead');
        $this->productsShippingStatusSource = $productsShippingStatusSource ??
            MainFactory::create(ProductsShippingStatusSource::class);

        $this->xtcPrice = $xtcPrice ?? MainFactory::create('xtcPrice',
                $_SESSION['currency'],
                $_SESSION['customers_status']['customers_status_id']);
        
        $this->gmSeoBoost = $gmSeoBoost ?? MainFactory::create('GMSEOBoost');
    }


    /**
     * Get the HTML output
     *
     * @return string HTML fragment
     */
    public function execute()
    {
        $template = ($this->commandConfiguration->presentation() === 'list') ?
            "product_listing.html" :
            "product_listing_swiper.html";

        $products = $this->productsByListType($this->commandConfiguration->listType());

        $productData = [];

        foreach ($products as $product) {
            if ($product instanceof ProductListItem) {
                $productData[] = $this->dataFromProductListItem($product);
                continue;
            }

            if ($product instanceof StoredProduct) {
                $productData[] = $this->dataFromStoredProduct($product);
                continue;
            }

            if ($product instanceof FeaturedProduct) {
                $productData[] = $this->dataFromFeaturedProduct($product, $this->commandConfiguration->languageCode());
            }
        }

        if (count($productData)) {
            return ProductListContentViewGenerator::generate(
                $this->commandConfiguration,
                $productData,
                $template,
                $this->getSwiperBreakpoints($productData)
            );
        }

        return '';
    }


    /**
     * Return an array of product IDs based on the selected list type and provided language
     *
     * @param string $listType List type
     *
     * @return array
     */
    protected function productsByListType(string $listType): array
    {

        $featuredProductSettings = MainFactory::create(
            FeaturedProductSettings::class,
            new IntType($this->commandConfiguration->maxProducts()),
            new BoolType($this->commandConfiguration->random()),
            $this->customerGroupId,
            $this->isFsk18Purchasable,
            $this->commandConfiguration->languageCode()
        );

        $products = [];

        if ($listType === 'own-list') {
            return $this->getOwnListProducts();
        }

        switch ($listType) {
            case 'category':
                $categoryId = $this->commandConfiguration->categoryId();
                if ($categoryId) {
                    try {
                        $products = $this->featuredProductReadService
                            ->getProductsByCategoryId(
                                $featuredProductSettings,
                                new IntType($categoryId)
                            )
                            ->getArray();
                    } catch (Exception $e) {
                        //hide exceptions in order to keep stability
                    }
                }
                break;
            case 'deals':
                $products = $this->featuredProductReadService
                    ->getOfferedProducts($featuredProductSettings)
                    ->getArray();
                break;
            case 'recommended':
                $products = $this->featuredProductReadService
                    ->getTopProducts($featuredProductSettings)
                    ->getArray();
                break;
            case 'coming-soon':
                $products = $this->featuredProductReadService
                    ->getUpcomingProducts($featuredProductSettings)
                    ->getArray();
                break;
            case 'new':
                $products = $this->featuredProductReadService
                    ->getNewProducts($featuredProductSettings)
                    ->getArray();
                break;
        }

        return $products;
    }


    /**
     * Create data array from ProductListItem object
     *
     * @param ProductListItem $product Product
     *
     * @return array
     */
    protected function dataFromProductListItem(ProductListItem $product): array
    {
        $productShippingName = $this->productsShippingStatusSource
            ->get_shipping_status_name(
                $product->getShippingTimeId(),
                $this->commandConfiguration->languageId()->asInt()
            );

        $productShortDescription = $product->getProductObject()
            ->getShortDescription($this->commandConfiguration->languageCode());

        $data = [
            'showManufacturerImages'     => $this->commandConfiguration->showManufacturerImages(),
            'showProductRibbons'         => $this->commandConfiguration->showProductRibbons(),
            'PRODUCTS_SHIPPING_NAME'     => $this->getShippingStatusText($productShippingName, $product->getProductId()),
            'PRODUCTS_SHORT_DESCRIPTION' => $productShortDescription,
            'PRODUCTS_META_DESCRIPTION'  => $product->getName(),
            'PRODUCTS_NAME'              => $product->getName(),
            'PRODUCTS_IMAGE'             => $this->getImageCorrectPath($product->getImage()),
            'PRODUCTS_IMAGE_ALT'         => $product->getImageAltText(),
            'PRODUCTS_ID'                => $product->getProductId(),
            'PRODUCTS_VPE'               => '',
            'PRODUCTS_LINK'              => $this->getProductLink($product->getProductId(), $product->getName()),
            'PRODUCTS_PRICE'             => $this->getProductPrice($product)
        ];

        if ($product->isVpeActive()) {
            $data['PRODUCTS_VPE'] = $this->getProductsVpeValue($product->getProductId());
        }

        return $data;
    }


    /**
     * Create data array from StoredProduct object
     *
     * @param StoredProduct $product Product
     *
     * @return array
     */
    protected function dataFromStoredProduct(StoredProduct $product): array
    {
        $productsShippingName = $this->productsShippingStatusSource
            ->get_shipping_status_name(
                $product->getShippingTimeId(),
                $this->commandConfiguration->languageId()->asInt()
            );

        $data = [
            'showManufacturerImages'     => $this->commandConfiguration->showManufacturerImages(),
            'showProductRibbons'         => $this->commandConfiguration->showProductRibbons(),
            'PRODUCTS_SHIPPING_NAME'     => $this->getShippingStatusText($productsShippingName, $product->getProductId()),#$productsShippingName,
            //'PRODUCTS_SHIPPING_NAME'     => $productsShippingName,
            'PRODUCTS_SHORT_DESCRIPTION' => $product->getShortDescription($this->commandConfiguration->languageCode()),
            'PRODUCTS_META_DESCRIPTION'  => $product->getName($this->commandConfiguration->languageCode()),
            'PRODUCTS_NAME'              => $product->getName($this->commandConfiguration->languageCode()),
            'PRODUCTS_IMAGE'             => $this->getImageCorrectPath($product->getPrimaryImage()->getFilename()),
            'PRODUCTS_IMAGE_ALT'         => $product->getPrimaryImage()->getAltText($this->commandConfiguration->languageCode()),
            'PRODUCTS_ID'                => $product->getProductId(),
            'PRODUCTS_VPE'               => '',
            'PRODUCTS_LINK'              => $this->getProductLink($product->getProductId(), 
                                                                  $product->getName($this->commandConfiguration->languageCode())),
            'PRODUCTS_PRICE'             => $this->getProductPrice($product)
        ];

        if ($product->isVpeActive()) {

            $data['PRODUCTS_VPE'] = $this->getProductsVpeValue($product->getProductId());
        }

        return $data;
    }


    /**
     * @param int $productsId
     *
     * @return string
     * @deprecated With variant products this method will be unnecessary und the 2 product widgets need a refactoring
     */
    public function getProductsVpeValue(int $productsId): string
    {
        $productObj = MainFactory::create('product', $productsId);
        $productData = $productObj->buildDataArray($productObj->data);

        return $productData['PRODUCTS_VPE'] ?? '';
    }

    /**
     * Create data array from FeaturedProduct object
     *
     * @param FeaturedProduct $product Product
     * @param LanguageCode $languageCode Language
     *
     * @return array
     */
    protected function dataFromFeaturedProduct(FeaturedProduct $product, LanguageCode $languageCode): array
    {
        $data = [
            'showManufacturerImages'     => $this->commandConfiguration->showManufacturerImages(),
            'showProductRibbons'         => $this->commandConfiguration->showProductRibbons(),
            'PRODUCTS_SHIPPING_NAME'     => $this->getShippingStatusText($product->getShippingStatusName(), $product->getProductsId()),
            'PRODUCTS_SHORT_DESCRIPTION' => $product->getShortDescription(),
            'PRODUCTS_META_DESCRIPTION'  => $product->getMetaDescription(),
            'PRODUCTS_NAME'              => $product->getName(),
            'PRODUCTS_IMAGE'             => $this->getImageCorrectPath($product->getImage()),
            'PRODUCTS_IMAGE_ALT'         => $product->getImageAltText(),
            'PRODUCTS_ID'                => $product->getProductsId(),
            'PRODUCTS_VPE'               => $product->getVpeID(),
            'PRODUCTS_LINK'              => $this->getProductLink($product->getProductsId(), $product->getName()),
            'PRODUCTS_PRICE'             => $this->getProductPrice($product)
        ];

        if ($product->getVpeID() > 0) {
            $data['PRODUCTS_VPE'] = $this->getProductsVpeValue($product->getProductsId());
        }

        return $data;
    }


    /**
     * @return array
     */
    protected function getOwnListProducts(): array
    {
        $productIds = $this->products;

        if ($this->commandConfiguration->random() === true) {
            shuffle($productIds);
        }

        if (count($productIds) > $this->commandConfiguration->maxProducts()) {
            $productIds = array_slice($productIds, 0, $this->commandConfiguration->maxProducts());
        }

        $products = [];

        foreach ($productIds as $id) {
            try {
                $product = $this->productReadService->getProductById(MainFactory::create(IdType::class, $id));
                if ($product->isActive()) {
                    $products[] = $product;
                }
            } catch (Exception $e) {
                //hide error messages in order to keep stability
            }
        }

        return $products;
    }


    /**
     * @param $image
     *
     * @return string
     */
    protected function getImageCorrectPath($image): string
    {
        return (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG
            . "/images/product_images/thumbnail_images/{$image}";
    }

    /**
     * @return array
     */
    protected function getSwiperBreakpoints($products): array
    {
        return [
            480 => [
                'usePreviewBullets' => true,
                'slidesPerView'     => min($this->commandConfiguration->itemsPerRowXs(), count($products)),
                'centeredSlides'    => true
            ],
            768 => [
                'usePreviewBullets' => true,
                'slidesPerView'     => min($this->commandConfiguration->itemsPerRowSm(), count($products)),
                'centeredSlides'    => false
            ],
            992 => [
                'usePreviewBullets' => true,
                'slidesPerView'     => min($this->commandConfiguration->itemsPerRowMd(), count($products)),
                'centeredSlides'    => false
            ],
            1200 => [
                'usePreviewBullets' => true,
                'slidesPerView'     => min($this->commandConfiguration->itemsPerRowLg(), count($products)),
                'centeredSlides'    => false
            ],
            10000 => [
                'usePreviewBullets' => true,
                'slidesPerView'     => min($this->commandConfiguration->itemsPerRowLg(), count($products)), // default slides per view
                'centeredSlides'    => false
            ],
        ];
    }

    /**
     * @param $product
     * @return string
     */
    protected function getProductPrice($product): string
    {
        $productId = method_exists(get_class($product), 'getProductId') ?
            $product->getProductId() :
            $product->getProductsId();

        $propertiesControl = MainFactory::create_object('PropertiesControl');
        $productCombination = $propertiesControl->get_cheapest_combi(
            $productId,
            $this->commandConfiguration->languageId()->asInt()
        );

        $price = $this->xtcPrice->xtcGetPrice(
            $productId,
            true,
            $product instanceof StoredProduct ? $product->getMinimumQuantity() : $product->getQuantity(),
            $product->getTaxClassId(),
            $product->getPrice(),
            1,
            0,
            true,
            true,
            $productCombination['products_properties_combis_id'],
            true
        );

        return $price['formated'] ?? 0;
    }
    
    
    /**
     * @param string $currentStatus
     * @param int    $productId
     *
     * @return string
     */
    protected function getShippingStatusText(string $currentStatus, int $productId): string
    {
        // Do not use the MainFactory in order to avoid class overload issues
        $product = new product($productId);
    
        $productData = $product->buildDataArray($product->data);
    
        if (is_array($productData['PRODUCTS_SHIPPING_RANGE']) && count($productData['PRODUCTS_SHIPPING_RANGE']) >= 2
            && current($productData['PRODUCTS_SHIPPING_RANGE'])['name']
               !== end($productData['PRODUCTS_SHIPPING_RANGE'])['name']) {
            
            reset($productData['PRODUCTS_SHIPPING_RANGE']);
            return $this->createShippingTimeFromRange($productData['PRODUCTS_SHIPPING_RANGE']);
        }
        if($productData['PRODUCTS_SHIPPING_NAME'] !== $currentStatus)
        {
            return '';
        }
        return $currentStatus;
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


    /**
     * @param int    $productId
     * @param string $productName
     *
     * @return string
     */
    protected function getProductLink(int $productId, string $productName): string
    {
        if ($this->gmSeoBoost->boost_products) {
            return xtc_href_link($this->gmSeoBoost->get_boosted_product_url($productId,
                                                                            $productName,
                                                                            $this->commandConfiguration->languageId()
                                                                                ->asInt()));
        }

        return xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($productId, $productName));
    }
}
