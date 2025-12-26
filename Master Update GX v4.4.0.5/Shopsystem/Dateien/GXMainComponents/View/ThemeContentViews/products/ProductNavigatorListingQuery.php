<?php
/*--------------------------------------------------------------
   ProductNavigatorListingQuery.php 2020-09-03
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

/**
 * Class ProductNavigatorListingQuery
 */
class ProductNavigatorListingQuery
{
    /**
     * @var int
     */
    protected $categoryId;
    
    /**
     * @var int
     */
    protected $languagesId;
    
    
    /**
     * ProductNavigatorListingQuery constructor.
     *
     * @param int $categoryId
     * @param int $languagesId
     */
    public function __construct(int $categoryId, int $languagesId)
    {
        $this->categoryId  = $categoryId;
        $this->languagesId = $languagesId;
    }
    
    
    /**
     * @return string
     */
    public function __toString()
    {
        return "
                                        SELECT DISTINCT p.products_fsk18,
                                                        p.products_sort,
											            p.products_shippingtime,
											            p.products_model,
											            p.products_ean,
											            pd.products_name,
											            m.manufacturers_name,
											            p.products_quantity,
											            p.products_image,
											            p.products_image_w,
											            p.products_image_h,
											            p.products_weight,
											            p.gm_show_weight,
											            pd.products_short_description,
											            pd.products_description,
											            pd.gm_alt_text,
											            pd.products_meta_description,
											            p.products_id,
											            p.manufacturers_id,
											            p.products_price,
											            p.products_vpe,
											            p.products_vpe_status,
											            p.products_vpe_value,
											            p.products_discount_allowed,
											            p.products_tax_class_id
											
										FROM
											products p
											LEFT JOIN products_description AS pd ON (pd.products_id = p.products_id)
											LEFT JOIN products_to_categories AS ptc ON (ptc.products_id = p.products_id)
											LEFT JOIN manufacturers AS m ON (m.manufacturers_id = p.manufacturers_id)
											LEFT JOIN specials AS s ON (s.products_id = p.products_id)
											
										WHERE
											p.products_status = 1 AND
											pd.language_id = '" . $this->languagesId . "' AND
											ptc.categories_id = '" . $this->categoryId . "'
											
											
											
											 ORDER BY p.products_sort ASC, p.products_id ASC , p.products_sort ASC
                                        ";
    }
    
    
    /**
     * @return int
     */
    public function categoryId(): int
    {
        return $this->categoryId;
    }
}