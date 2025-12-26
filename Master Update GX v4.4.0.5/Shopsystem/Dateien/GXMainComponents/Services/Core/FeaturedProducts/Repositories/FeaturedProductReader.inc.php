<?php
/* --------------------------------------------------------------
  FeaturedProductReader.php 2021-06-09
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2021 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

/**
 * Class FeaturedProductReader
 */
class FeaturedProductReader implements FeaturedProductReaderInterface
{
    /**
     * CodeIgniter QueryBuilder
     *
     * @var CI_DB_query_builder
     */
    protected $queryBuilder;

    /**
     * Whether customer group check is active
     *
     * @var bool
     */
    protected $isCustomerGroupCheckActive;


    /**
     * FeaturedProductReader constructor.
     *
     * @param CI_DB_query_builder $queryBuilder
     * @param BoolType            $isCustomerGroupCheckActive
     */
    public function __construct(CI_DB_query_builder $queryBuilder, BoolType $isCustomerGroupCheckActive)
    {
        $this->queryBuilder               = $queryBuilder;
        $this->isCustomerGroupCheckActive = $isCustomerGroupCheckActive->asBool();
    }


    /**
     * Get offers
     *
     * @param FeaturedProductSettings $settings
     *
     * @return FeaturedProductCollection
     * @throws FeaturedProductNotFoundException
     */
    public function getOfferedProducts(FeaturedProductSettings $settings)
    {
        $featuredProducts = [];

        $query = $this->queryBuilder->select('*')
            ->from('products')
            ->join('specials',
                   'products.products_id = specials.products_id')
            ->join('products_description', 'products.products_id = products_description.products_id')
            ->join('languages',
                   'products_description.language_id = languages.languages_id')
            ->join('shipping_status',
                   'shipping_status.shipping_status_id = products.products_shippingtime AND shipping_status.language_id = products_description.language_id','left outer')
            ->where([
                        'specials.status'          => '1',
                        'languages.code'           => $settings->languageCode(),
                        'products.products_status' => '1'
                    ])
            ->limit($settings->getLimit());

        if ($this->isCustomerGroupCheckActive) {
            $query->where("group_permission_" . $settings->getCustomerGroupId(), '1');
        }

        if (!$settings->isFsk18Purchasable()) {
            $query->where('products_fsk18', '0');
        }

        if ($settings->getRandomOrder()) {
            $query->order_by('RAND()');
        }

        $result = $query->get()->result_array();

        foreach ($result as $item) {
            $productId          = new IntType($item['products_id']);
            $name               = new StringType($item['products_name']);
            $vpeId              = new IntType($item['products_vpe']);
            $image              = new StringType($item['products_image']);
            $imageAltText       = new StringType((string)$item['gm_alt_text']);
            $shortDescription   = new StringType($item['products_short_description']);
            $metaDescription    = new StringType($item['products_meta_description']);
            $shippingStatusName = new StringType((string)$item['shipping_status_name']);
            $price              = new DecimalType($item['products_price']);
            $taxClassId         = new IntType($item['products_tax_class_id']);
            $quantity           = new DecimalType($item['gm_min_order']);

            $featuredProducts[] = FeaturedProductFactory::create($settings,
                                                                 $productId,
                                                                 $name,
                                                                 $vpeId,
                                                                 $image,
                                                                 $imageAltText,
                                                                 $shortDescription,
                                                                 $metaDescription,
                                                                 $shippingStatusName,
                                                                 $price,
                                                                 $taxClassId,
                                                                 $quantity);
        }

        return MainFactory::create(FeaturedProductCollection::class, $featuredProducts);
    }


    /**
     * Get top products
     *
     * @param FeaturedProductSettings $settings
     *
     * @return FeaturedProductCollection
     * @throws FeaturedProductNotFoundException
     */
    public function getTopProducts(FeaturedProductSettings $settings): FeaturedProductCollection
    {
        $topProducts = [];

        $where = [
            'products_status'    => '1',
            'products_startpage' => '1',
            'languages.code'     => $settings->languageCode(),
        ];

        if ($this->isCustomerGroupCheckActive) {

            $where['group_permission_' . $settings->getCustomerGroupId()] = '1';
        }

        if (!$settings->isFsk18Purchasable()) {

            $where['products_fsk18'] = '0';
        }

        $query = $this->queryBuilder->select()
            ->from('products')
            ->where($where)
            ->order_by('products_startpage_sort',
                       'ASC')
            ->limit($settings->getLimit())
            ->join('products_description',
                   'products.products_id = products_description.products_id')
            ->join('shipping_status',
                   'shipping_status.shipping_status_id = products.products_shippingtime AND shipping_status.language_id = products_description.language_id','left outer')
            ->join('languages',
                   'products_description.language_id = languages.languages_id');

        if ($settings->getRandomOrder()) {
            $query->order_by('RAND()');
        }

        $result = $query->get()->result_array();

        if (count($result) > 0) {


            foreach ($result as $item) {
                $productId          = new IntType($item['products_id']);
                $name               = new StringType($item['products_name']);
                $vpeId              = new IntType($item['products_vpe']);
                $image              = new StringType($item['products_image']);
                $imageAltText       = new StringType((string)$item['gm_alt_text']);
                $shortDescription   = new StringType($item['products_short_description']);
                $metaDescription    = new StringType($item['products_meta_description']);
                $shippingStatusName = new StringType((string)$item['shipping_status_name']);
                $price              = new DecimalType($item['products_price']);
                $taxClassId         = new IntType($item['products_tax_class_id']);
                $quantity           = new DecimalType($item['gm_min_order']);

                $topProducts[] = FeaturedProductFactory::create($settings,
                                                                $productId,
                                                                $name,
                                                                $vpeId,
                                                                $image,
                                                                $imageAltText,
                                                                $shortDescription,
                                                                $metaDescription,
                                                                $shippingStatusName,
                                                                $price,
                                                                $taxClassId,
                                                                $quantity);
            }
        }

        return MainFactory::create(FeaturedProductCollection::class, $topProducts);
    }


    /**
     * Get upcoming products
     *
     * @param FeaturedProductSettings $settings
     * @param DateTime                $dateExpected
     *
     * @return FeaturedProductCollection
     */
    public function getUpcomingProducts(FeaturedProductSettings $settings)
    {
        $upcomingProducts = [];

        $query = $this->queryBuilder->select('*')
            ->from('products')
            ->join('products_description',
                   'products.products_id = products_description.products_id')
            ->join('languages',
                   'products_description.language_id = languages.languages_id')
            ->join('shipping_status',
                   'shipping_status.shipping_status_id = products.products_shippingtime AND shipping_status.language_id = products_description.language_id','left outer')
            ->where([
                        'languages.code'           => $settings->languageCode(),
                        'products.products_status' => '1'
                    ])
            ->where('to_days(products_date_available) >= to_days(now())')
            ->limit($settings->getLimit());

        if ($this->isCustomerGroupCheckActive) {
            $query->where("group_permission_" . $settings->getCustomerGroupId(), '1');
        }

        if (!$settings->isFsk18Purchasable()) {
            $query->where('products_fsk18', '0');
        }

        if ($settings->getRandomOrder()) {
            $query->order_by('RAND()');
        } elseif (EXPECTED_PRODUCTS_FIELD === 'date_expected') {
            $query->order_by('products_date_available', EXPECTED_PRODUCTS_SORT);
        } else {
            $query->order_by(EXPECTED_PRODUCTS_FIELD, EXPECTED_PRODUCTS_SORT);
        }

        $result = $query->get()->result_array();

        foreach ($result as $item) {
            $productId          = new IntType($item['products_id']);
            $name               = new StringType($item['products_name']);
            $vpeId              = new IntType($item['products_vpe']);
            $image              = new StringType($item['products_image']);
            $imageAltText       = new StringType((string)$item['gm_alt_text']);
            $shortDescription   = new StringType($item['products_short_description']);
            $metaDescription    = new StringType($item['products_meta_description']);
            $shippingStatusName = new StringType((string)$item['shipping_status_name']);
            $price              = new DecimalType($item['products_price']);
            $taxClassId         = new IntType($item['products_tax_class_id']);
            $quantity           = new DecimalType($item['gm_min_order']);

            $upcomingProducts[] = FeaturedProductFactory::create($settings,
                                                                 $productId,
                                                                 $name,
                                                                 $vpeId,
                                                                 $image,
                                                                 $imageAltText,
                                                                 $shortDescription,
                                                                 $metaDescription,
                                                                 $shippingStatusName,
                                                                 $price,
                                                                 $taxClassId,
                                                                 $quantity);
        }

        return MainFactory::create(FeaturedProductCollection::class, $upcomingProducts);
    }


    /**
     * Get new products
     *
     * @param FeaturedProductSettings $settings
     *
     * @return FeaturedProductCollection
     */
    public function getNewProducts(FeaturedProductSettings $settings)
    {
        $newProducts = [];

        $where = [
            'products_status' => '1',
            'languages.code'  => $settings->languageCode(),
        ];

        if ($this->isCustomerGroupCheckActive) {

            $where['group_permission_' . $settings->getCustomerGroupId()] = '1';
        }

        if (!$settings->isFsk18Purchasable()) {

            $where['products_fsk18'] = '0';
        }

        $query = $this->queryBuilder->select()
            ->from('products')
            ->where($where)
            ->limit($settings->getLimit())
            ->join('products_description',
                   'products.products_id = products_description.products_id')
            ->join('shipping_status',
                   'shipping_status.shipping_status_id = products.products_shippingtime AND shipping_status.language_id = products_description.language_id','left outer')
            ->join('languages', 'products_description.language_id = languages.languages_id');

        if ($settings->getRandomOrder()) {
            $query = $query->order_by('RAND()');
        }

        if (MAX_DISPLAY_NEW_PRODUCTS_DAYS != '0') {
            $t_date_new_products = date('Y-m-d',
                                        mktime(1,
                                               1,
                                               1,
                                               date('m'),
                                               date('d') - (int)MAX_DISPLAY_NEW_PRODUCTS_DAYS,
                                               date('Y')));
            $query->where('products_date_added > "' . $t_date_new_products . '" ');
        }

        $result = $query->get()->result_array();

        if (count($result) > 0) {

            foreach ($result as $item) {
                $productId          = new IntType($item['products_id']);
                $name               = new StringType($item['products_name']);
                $vpeId              = new IntType($item['products_vpe']);
                $image              = new StringType($item['products_image']);
                $imageAltText       = new StringType((string)$item['gm_alt_text']);
                $shortDescription   = new StringType($item['products_short_description']);
                $metaDescription    = new StringType($item['products_meta_description']);
                $shippingStatusName = new StringType((string)$item['shipping_status_name']);
                $price              = new DecimalType($item['products_price']);
                $taxClassId         = new IntType($item['products_tax_class_id']);
                $quantity           = new DecimalType($item['gm_min_order']);

                $newProducts[] = FeaturedProductFactory::create($settings,
                                                                $productId,
                                                                $name,
                                                                $vpeId,
                                                                $image,
                                                                $imageAltText,
                                                                $shortDescription,
                                                                $metaDescription,
                                                                $shippingStatusName,
                                                                $price,
                                                                $taxClassId,
                                                                $quantity);
            }
        }

        return MainFactory::create(FeaturedProductCollection::class, $newProducts);
    }


    /**
     * get products by category id.
     *
     * @param FeaturedProductSettings $settings
     *
     * @param IntType                 $categoryId
     *
     * @return FeaturedProductCollection
     */
    public function getProductsByCategoryId(FeaturedProductSettings $settings, IntType $categoryId)
    {
        $newProducts = [];

        $where = [
            'products_status'                      => '1',
            'languages.code'                       => $settings->languageCode(),
            'products_to_categories.categories_id' => $categoryId->asInt()
        ];

        if ($this->isCustomerGroupCheckActive) {

            $where['group_permission_' . $settings->getCustomerGroupId()] = '1';
        }

        if (!$settings->isFsk18Purchasable()) {

            $where['products_fsk18'] = '0';
        }

        $query = $this->queryBuilder->select()
            ->from('products')
            ->where($where)
            ->limit($settings->getLimit())
            ->join('products_description',
                   'products.products_id = products_description.products_id')
            ->join('shipping_status',
                   'shipping_status.shipping_status_id = products.products_shippingtime AND shipping_status.language_id = products_description.language_id','left outer')
            ->join('languages',
                   'products_description.language_id = languages.languages_id')
            ->join('products_to_categories',
                   'products_to_categories.products_id = products.products_id');

        if ($settings->getRandomOrder()) {
            $query->order_by('RAND()');
        }

        $result = $query->get()->result_array();

        if (count($result) > 0) {

            foreach ($result as $item) {
                $productId          = new IntType($item['products_id']);
                $name               = new StringType($item['products_name']);
                $vpeId              = new IntType($item['products_vpe']);
                $image              = new StringType($item['products_image']);
                $imageAltText       = new StringType((string)$item['gm_alt_text']);
                $shortDescription   = new StringType($item['products_short_description']);
                $metaDescription    = new StringType($item['products_meta_description']);
                $shippingStatusName = new StringType((string)$item['shipping_status_name']);
                $price              = new DecimalType($item['products_price']);
                $taxClassId         = new IntType($item['products_tax_class_id']);
                $quantity           = new DecimalType($item['gm_min_order']);

                $newProducts[] = FeaturedProductFactory::create($settings,
                                                                $productId,
                                                                $name,
                                                                $vpeId,
                                                                $image,
                                                                $imageAltText,
                                                                $shortDescription,
                                                                $metaDescription,
                                                                $shippingStatusName,
                                                                $price,
                                                                $taxClassId,
                                                                $quantity);
            }
        }

        return MainFactory::create(FeaturedProductCollection::class, $newProducts);
    }
}
