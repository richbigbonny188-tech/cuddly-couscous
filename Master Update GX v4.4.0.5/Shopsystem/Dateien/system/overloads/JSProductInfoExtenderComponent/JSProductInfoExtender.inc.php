<?php
/* --------------------------------------------------------------
   JSProductInfoExtender.inc.php 2018-11-14
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2017 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

class JSProductInfoExtender extends JSProductInfoExtender_parent
{
    function proceed()
    {
        parent::proceed();

        include_once(get_usermod(DIR_FS_CATALOG . 'gm/javascript/jquery/ui/jquery-ui.js'));
        include_once(get_usermod(DIR_FS_CATALOG . 'gm/javascript/GMOrderQuantityChecker.js'));
        include_once(get_usermod(DIR_FS_CATALOG . 'gm/javascript/GMAttributesCalculator.js'));
        include_once(get_usermod(DIR_FS_CATALOG . 'gm/javascript/GMAttributeImages.js'));
        include_once(get_usermod(DIR_FS_CATALOG . 'gm/javascript/gm_product_details.js'));

        include_once(get_usermod(DIR_FS_CATALOG . 'gm/properties/javascript/Properties/CombiStatusCheck.js'));
        include_once(get_usermod(DIR_FS_CATALOG . 'gm/properties/javascript/SelectionFormListener/DropdownsListener.js'));

        $coo_product = MainFactory::create_object('product', array($this->v_data_array['GET']['products_id']));
        if (isset($coo_product->data['gm_show_price_offer']) && $coo_product->data['gm_show_price_offer'] > 0) {
            include_once(get_usermod(DIR_FS_CATALOG . 'gm/javascript/price_offer.js.php'));
        }
    }
}
