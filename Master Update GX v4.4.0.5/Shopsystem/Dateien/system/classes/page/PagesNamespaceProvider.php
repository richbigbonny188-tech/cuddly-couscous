<?php
/*--------------------------------------------------------------------------------------------------
    PagesNamespaceProvider.php 2020-07-21
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/

class PagesNamespaceProvider
{
    /**
     * @var string[]
     */
    protected $pageNamespaces;
    /**
     * @var CI_DB_query_builder
     */
    protected $queryBuilder;

    public function __construct()
    {
        $this->queryBuilder   = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $baseUrl              = (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG;
        $this->pageNamespaces = [
            'CALLBACK_SERVICE'            => xtc_href_link("shop_content.php", "coID=14"),
            'MANUFACTURERS'               => "",
            'PRODUCT_INFO'                => "",
            'PRODUCT_INFO_WITH_MODIFIERS' => "",
            'CAT'                         => "",
            'SEARCH'                      => xtc_href_link("advanced_search_result.php"),
            'PRICE_OFFER'                 => xtc_href_link("gm_price_offer.php"),
            'CART'                        => xtc_href_link("shopping_cart.php"),
            'CONTENT'                     => xtc_href_link("shop_content.php", "coID=2"),
            'WISH_LIST'                   => xtc_href_link("wish_list.php"),
            'ADDRESS_BOOK_PROCESS'        => xtc_href_link("address_book_process.php"),
            'GV_SEND'                     => xtc_href_link("gv_send.php"),
            'CHECKOUT'                    => xtc_href_link("checkout_shipping.php"),
            'ACCOUNT_HISTORY'             => xtc_href_link("account_history.php"),
            'ACCOUNT'                     => xtc_href_link("account.php"),
            'INDEX'                       => xtc_href_link("index.php"),
            'WITHDRAWAL'                  => xtc_href_link("withdrawal.php"),
            'LOGOFF'                      => xtc_href_link("logoff.php")
        ];

        $this->updateProductInfoNamespaces();
        $this->updateCategoryNamespaces();
        $this->updateManufacturesNamespaces();
    }

    public function getPagesNamespaces(): array
    {
        return $this->pageNamespaces;
    }

    protected function updateProductInfoNamespaces()
    {
        $language_id             = (int)$_SESSION['languages_id'];
        $sqlProductWithModifiers = "
        select products_description.products_id,
               products_description.products_name
        from products_description
        where products_description.language_id = {$language_id}
          and products_description.products_id in (
            select min(products_id)
            from (
                     select min(products.products_id) products_id
                     from products_properties_combis
                              inner join products on products.products_id = products_properties_combis.products_id
                     where products.products_status = 1
                     union
                     select min(products_attributes.products_id)
                     from products_attributes
                              inner join products on products.products_id = products_attributes.products_id
                     where products.products_status = 1
                 ) ids
        )";
        $products                = $this->queryBuilder->query($sqlProductWithModifiers)->result_array();
        $productWithModifiers = [];
        $sqlProductWithModifiers = '';
        if (count($products)) {
            $productWithModifiers = $products[0];
            $sqlProductWithModifiers = "and products.products_id <> {$productWithModifiers['products_id']}";
            $this->pageNamespaces['PRODUCT_INFO_WITH_MODIFIERS'] = xtc_href_link(
                'product_info.php',
                xtc_product_link(
                    $productWithModifiers['products_id'],
                    $productWithModifiers['products_name']
                )
            );
        } else {
            unset($this->pageNamespaces['PRODUCT_INFO_WITH_MODIFIERS']);
        }

        $sqlProduct = " select products_description.products_id,
                               products_description.products_name
                        from products_description
                        where products_description.language_id = {$language_id}
                          and products_description.products_id in (
                                select min(products.products_id) products_id 
                                from products
                                where products.products_status = 1 $sqlProductWithModifiers
                        )";
        $products   = $this->queryBuilder->query($sqlProduct)->result_array();
        if (count($products)) {
            $this->pageNamespaces['PRODUCT_INFO'] = xtc_href_link(
                'product_info.php',
                xtc_product_link(
                    $products[0]['products_id'],
                    $products[0]['products_name']
                )
            );
        } elseif (count($productWithModifiers))
        {
            $this->pageNamespaces['PRODUCT_INFO'] = xtc_href_link(
                'product_info.php',
                xtc_product_link(
                    $productWithModifiers['products_id'],
                    $productWithModifiers['products_name']
                )
            );
        }
    }

    protected function updateCategoryNamespaces()
    {
        $sql        = "select min(categories_id) categories_id  
                from categories where categories_status = 1";
        $categories = $this->queryBuilder->query($sql)->result_array();
        if (count($categories)) {
            $this->pageNamespaces['CAT'] = xtc_href_link(
                'index.php',
                xtc_category_link(
                    (int)$categories[0]['categories_id']
                )
            );
        }
    }

    protected function updateManufacturesNamespaces()
    {
        $sql        = "select manufacturers_id, manufacturers_name  
                       from manufacturers 
                       order by manufacturers_id
                       limit 1";
        $manufacturers = $this->queryBuilder->query($sql)->result_array();
        if (count($manufacturers)) {
            $this->pageNamespaces['MANUFACTURERS'] = xtc_href_link(
                FILENAME_DEFAULT,
                xtc_manufacturer_link(
                    (int)$manufacturers[0]['manufacturers_id'],
                    $manufacturers[0]['manufacturers_name']
                )
            );
        }
    }


}