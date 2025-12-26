<?php
/* --------------------------------------------------------------
   function.gm_footer.php 2021-01-08
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

require_once DIR_FS_INC . 'xtc_get_tax_class_id.inc.php';

function smarty_function_product_ribbons($params, &$smarty)
{
    static $results;
    $key = $params['product_id'];
    
    if (isset($results[$key])) {
        $smarty->assign($params['out'], $results[$key]);
        
        return;
    }
    
    $arrResult = [];
    
    $arrResult['manufacturer'] = getManufacturersData($params);
    $arrResult['ribbons']      = [];
    
    $coo_text_mgr = MainFactory::create_object('LanguageTextManager',
                                               ['general', $_SESSION['languages_id']],
                                               true);
    $sectionArray = $coo_text_mgr->get_section_array();
    
    if (count($arrTemp = getDateAvailable($params)) > 0) {
        $arrResult['ribbons'][] = [
            'class' => $arrTemp['class'],
            'text'  => $sectionArray['RIBBON_UPCOMING']
        ];
    }
    
    if (count($arrTemp = getNew($params)) > 0) {
        $arrResult['ribbons'][] = [
            'class' => $arrTemp['class'],
            'text'  => $sectionArray['RIBBON_NEW'],
        ];
    }
    
    if (count($arrTemp = getRecommendation($params)) > 0) {
        $arrResult['ribbons'][] = [
            'class' => $arrTemp['class'],
            'text'  => $sectionArray['RIBBON_TOP']
        ];
    }
    
    if (count($arrTemp = getSoldOut($params)) > 0) {
        $arrResult['ribbons'][] = [
            'class' => $arrTemp['class'],
            'text'  => $sectionArray['RIBBON_SOLD_OUT']
        ];
    }
    
    if (count($arrTemp = getSpecials($params)) > 0) {
        $arrResult['ribbons'][] = [
            'class' => $arrTemp['class'],
            'text'  => $arrTemp['text'] // $sectionArray['RIBBON_SPECIAL']
        ];
    }
    
    $results[$key] = $arrResult;
    $smarty->assign($params['out'], $arrResult);
}

function getSoldOut($p_params)
{
    $arrTemp = [];
    
    $configurationStockCheckSql        = 'SELECT `value` as configuration_value FROM gx_configurations
                                           WHERE `key` = "configuration/STOCK_CHECK"';
    $configurationStockCheckResult     = xtc_db_query($configurationStockCheckSql);
    $configurationStockCheckResultBool = xtc_db_fetch_array($configurationStockCheckResult)['configuration_value'];
    
    $configurationAttributesStockCheckSql        = 'SELECT `value` as configuration_value FROM gx_configurations
                                           WHERE `key` = "configuration/ATTRIBUTE_STOCK_CHECK"';
    $configurationAttributesStockCheckResult     = xtc_db_query($configurationAttributesStockCheckSql);
    $configurationAttributesStockCheckResultBool = xtc_db_fetch_array($configurationAttributesStockCheckResult)['configuration_value'];
    
    $configurationAllowStockCheckout       = 'SELECT `value` as configuration_value FROM gx_configurations
                                               WHERE `key` = "configuration/STOCK_ALLOW_CHECKOUT"';
    $configurationAllowStockCheckoutResult = xtc_db_query($configurationAllowStockCheckout);
    $configurationAllowStockCheckoutBool   = xtc_db_fetch_array($configurationAllowStockCheckoutResult)['configuration_value'];
    
    $productsUsePropertiesCombisQty       = 'SELECT 
                                                    use_properties_combis_quantity, 
                                                    products_model 
												FROM products
                                                WHERE products_id = ' . (int)$p_params['product_id'];
    $productsUsePropertiesCombisQtyResult = xtc_db_query($productsUsePropertiesCombisQty);
    $productsArray                        = xtc_db_fetch_array($productsUsePropertiesCombisQtyResult);
    $productsUsePropertiesCombisQtyType   = $productsArray['use_properties_combis_quantity'];
    
    $isGift = strpos($productsArray['products_model'], 'GIFT_') === 0;
    
    if ($isGift || ($p_params['showProductRibbons'] !== 'true' || $configurationStockCheckResultBool === 'false')
        || $configurationAllowStockCheckoutBool === 'true'
        || $productsUsePropertiesCombisQtyType === '3') {
        return $arrTemp;
    }
    
    $hasPropertiesQuery  = 'SELECT COUNT(*) AS cnt 
									FROM products_properties_combis
                                    WHERE 
                                        products_id = ' . (int)$p_params['product_id'];
    $hasPropertiesResult = xtc_db_query($hasPropertiesQuery);
    $hasProperties       = xtc_db_fetch_array($hasPropertiesResult)['cnt'] !== '0';
    
    if (!$hasProperties || $productsUsePropertiesCombisQtyType === '1' || $productsUsePropertiesCombisQtyType === '0') {
        $productsStockSql    = 'SELECT COUNT(*) AS cnt 
									   FROM products
                                       WHERE 
                                            products_id = ' . (int)$p_params['product_id'] . ' AND 
                                            products_quantity <= 0 AND 
                                            gm_price_status = 0';
        $productsStockResult = xtc_db_query($productsStockSql);
        
        if (xtc_db_fetch_array($productsStockResult)['cnt'] !== '0') {
            $arrTemp['class'] = 'sold-out';
            $arrTemp['text']  = 'PRODUCT_RIBBON_SOLD_OUT';
            
            return $arrTemp;
        }
    }
    
    if ($hasProperties && $productsUsePropertiesCombisQtyType === '2') {
        $combinationSql    = 'SELECT COUNT(*) AS cnt 
								FROM products_properties_combis
	                            WHERE 
	                                products_id = ' . (int)$p_params['product_id'] . ' AND 
	                                combi_quantity > 0';
        $combinationResult = xtc_db_query($combinationSql);
        
        if (xtc_db_fetch_array($combinationResult)['cnt'] === '0') {
            $arrTemp['class'] = 'sold-out';
            $arrTemp['text']  = 'PRODUCT_RIBBON_SOLD_OUT';
            
            return $arrTemp;
        }
    }
    if ($configurationAttributesStockCheckResultBool !== 'false') {
        $hasAttributesQuery  = 'SELECT COUNT(*) AS cnt
							FROM products_attributes
							WHERE 
							    products_id = ' . (int)$p_params['product_id'];
        $hasAttributesResult = xtc_db_query($hasAttributesQuery);
        
        if (xtc_db_fetch_array($hasAttributesResult)['cnt'] !== '0') {
            $attributesSql    = 'SELECT COUNT(*) AS cnt
								FROM products_attributes
								WHERE 
								    products_id = ' . (int)$p_params['product_id'] . ' AND 
									attributes_stock > 0';
            $attributesResult = xtc_db_query($attributesSql);

            if (xtc_db_fetch_array($attributesResult)['cnt'] === '0') {
                $configurationDownloadsStockCheckSql        = 'SELECT `value` as `configuration_value` 
                                                                FROM `gx_configurations`
                                                                WHERE `key` = "configuration/DOWNLOAD_STOCK_CHECK"';
                $configurationDownloadsStockCheckResult     = xtc_db_query($configurationDownloadsStockCheckSql);
                $configurationDownloadsStockCheckResultBool = xtc_db_fetch_array($configurationDownloadsStockCheckResult)['configuration_value'];

                $downloadsSql    = 'SELECT COUNT(*) AS cnt 
                                    FROM `products_attributes_download` 
                                    WHERE 
                                        `products_attributes_id` IN (
                                             SELECT `products_attributes_id` 
                                             FROM `products_attributes` 
                                             WHERE `products_id` = ' . (int)$p_params['product_id'] . '
                                         )';
                $downloadsResult = xtc_db_query($downloadsSql);
                $downloadsCount  = (int)xtc_db_fetch_array($downloadsResult)['cnt'];

                if (($downloadsCount > 0 && $configurationDownloadsStockCheckResultBool === 'true')
                    || $downloadsCount === 0) {
                    $arrTemp['class'] = 'sold-out';
                    $arrTemp['text']  = 'PRODUCT_RIBBON_SOLD_OUT';
                }
            }
        }
    }
    
    return $arrTemp;
}

function getRecommendation($p_params)
{
    $arrTemp = [];
    
    if ($p_params['showProductRibbons'] !== 'true') {
        return $arrTemp;
    }
    
    $strSql = "select * from products 
		where products_id = " . (int)$p_params['product_id'] . " 
		and products_startpage = 1";
    $result = xtc_db_query($strSql);
    while ($item = xtc_db_fetch_array($result)) {
        $arrTemp['class'] = 'recommendation';
        $arrTemp['text']  = 'PRODUCT_RIBBON_RECOMMENDATION';
    };
    
    return $arrTemp;
}

function getNew($p_params)
{
    $arrTemp = [];
    
    if ($p_params['showProductRibbons'] !== 'true') {
        return $arrTemp;
    }
    
    $strNow = date('Y-m-d', mktime(1, 1, 1, date('m'), date('d') - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date('Y')));
    $strSql = "select * from products 
		where products_id = " . (int)$p_params['product_id'] . " 
		and products_date_added > '" . $strNow . "'";
    $result = xtc_db_query($strSql);
    while ($item = xtc_db_fetch_array($result)) {
        $arrTemp['class'] = 'new';
        $arrTemp['text']  = 'PRODUCT_RIBBON_NEW';
    };
    
    return $arrTemp;
}

function getDateAvailable($p_params)
{
    $arrTemp = [];
    
    if ($p_params['showProductRibbons'] !== 'true') {
        return $arrTemp;
    }
    
    $strSql = "select * from products 
		where products_id = " . (int)$p_params['product_id'] . " 
		and products_date_available is not null 
		and products_date_available > NOW()";
    $result = xtc_db_query($strSql);
    while ($item = xtc_db_fetch_array($result)) {
        $arrTemp['class'] = 'available';
        $arrTemp['text']  = sprintf(PRODUCT_RIBBON_AVAILABLE, date("d.m.Y", mktime($item['products_date_available'])));
    };
    
    return $arrTemp;
}

function getSpecials($p_params)
{
    $arrTemp = [];
    
    if ($p_params['showProductRibbons'] !== 'true') {
        return $arrTemp;
    }
    
    $xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);

    $specialPrice = $xtPrice->xtcCheckSpecial($p_params['product_id']);
    if($specialPrice !== false)
    {
        $specialPrice = $specialPrice + $xtPrice->get_attributes_combi_price($p_params['product_id'],xtc_get_tax_class_id($p_params['product_id']));
        $normalPrice  = $xtPrice->getPprice($p_params['product_id']) + $xtPrice->get_attributes_combi_price($p_params['product_id'],xtc_get_tax_class_id($p_params['product_id']));

        if ($specialPrice < $normalPrice && $specialPrice > 0) {
            $arrTemp['class'] = 'special';
            $arrTemp['text']  = ceil(round((1 - ($specialPrice / $normalPrice)) * -100, 1)) . '%';
        }
    }
    
    return $arrTemp;
}

function getManufacturersData($p_params)
{
    $manufacturersDataArray = [];
    
    if ($p_params['showManufacturerImages'] !== 'true') {
        return $manufacturersDataArray;
    }
    
    $productsDataArray = getManufacturersId($p_params['product_id']);
    $manufacturersId   = $productsDataArray['manufacturers_id'];
    
    if ($manufacturersId > 0) {
        $query = '
			SELECT m.manufacturers_id, m.manufacturers_name, m.manufacturers_image, i.manufacturers_url
			FROM manufacturers AS m
			LEFT JOIN manufacturers_info AS i
			    ON (i.manufacturers_id = m.manufacturers_id AND i.languages_id = ' . (int)$_SESSION['languages_id'] . ')  
			WHERE m.manufacturers_id = ' . (int)$manufacturersId . ' 
				AND m.manufacturers_image != "" 
				AND m.manufacturers_image IS NOT NULL 
			GROUP BY m.manufacturers_id';
        
        $result = xtc_db_query($query);
        
        while ($item = xtc_db_fetch_array($result)) {
            $manufacturersDataArray[] = [
                'ID'        => $item['manufacturers_id'],
                'NAME'      => $item['manufacturers_name'],
                'IMAGE'     => 'images/' . $item['manufacturers_image'],
                'IMAGE_ALT' => $item['manufacturers_name'],
                'URL'       => $item['manufacturers_url']
            ];
        }
    }
    
    return $manufacturersDataArray;
}

function getManufacturersId($p_productId)
{
    $arrTemp = [];
    $strSql  = "select * from products where products_id = " . (int)$p_productId;
    $result  = xtc_db_query($strSql);
    while ($objProduct = xtc_db_fetch_array($result)) {
        $arrTemp = [
            'manufacturers_id' => $objProduct['manufacturers_id'],
            'date_available'   => $objProduct['products_date_available']
        ];
    }
    
    return $arrTemp;
}
