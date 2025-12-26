<?php
/* --------------------------------------------------------------
   xtc_has_product_attributes.inc.php 2018-07-26 gm
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2018 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------

   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_has_product_attributes.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: xtc_has_product_attributes.inc.php 1009 2005-07-11 16:19:29Z mz $)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// Check if product has attributes
  function xtc_has_product_attributes($products_id) {
    $attributes_query = "select count(*) as count from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$products_id . "'";
    $attributes_query  = xtc_db_query($attributes_query);
    $attributes = xtc_db_fetch_array($attributes_query,true);

    if ($attributes['count'] > 0) 
      return true;
	
	
    $properties_query = "select count(*) as count from products_properties_combis where products_id = '" . (int)$products_id . "'";
    $properties_query  = xtc_db_query($properties_query);
    $properties = xtc_db_fetch_array($properties_query,true);

    if ($properties['count'] > 0) 
      return true;
    
    $customizer_query = "select count(*) as count from gm_gprint_surfaces_groups_to_products where products_id = '". (int)$products_id ."'";
    $customizer_query = xtc_db_query($customizer_query);
    $customizer = xtc_db_fetch_array($customizer_query,true);
    
    if ($customizer['count'] > 0)
      return true;
	
	# no attributes or properties found
	return false;
  }
 ?>