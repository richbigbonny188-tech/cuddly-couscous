<?php
/* --------------------------------------------------------------
   dependent.inc.php 2020-12-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/** @var DatabaseModel $this */

/**
 * SET MISSING CONFIGURATION TYPES
 */

$keyTypeRelation = [
    'configuration/GZIP_LEVEL'                                           => 'number',
    'configuration/PRICE_PRECISION'                                      => 'number',
    'configuration/PRODUCT_LIST_FILTER'                                  => '1-0-switcher',
    'configuration/SESSION_TIMEOUT'                                      => 'number',
    'configuration/SQL_LOG_MAX_FILESIZE'                                 => 'number',
    'gm_configuration/ATTACH_CONDITIONS_OF_USE_IN_ORDER_CONFIRMATION'    => '1-0-switcher',
    'gm_configuration/ATTACH_PRIVACY_NOTICE_IN_ORDER_CONFIRMATION'       => '1-0-switcher',
    'gm_configuration/ATTACH_WITHDRAWAL_FORM_IN_ORDER_CONFIRMATION'      => '1-0-switcher',
    'gm_configuration/ATTACH_WITHDRAWAL_INFO_IN_ORDER_CONFIRMATION'      => '1-0-switcher',
    'gm_configuration/CAT_MENU_LEFT'                                     => 'switcher',
    'gm_configuration/CAT_MENU_TOP'                                      => 'switcher',
    'gm_configuration/SUPPRESS_INDEX_IN_URL'                             => 'switcher',
    'gm_configuration/CATEGORY_ACCORDION_EFFECT'                         => 'switcher',
    'gm_configuration/CATEGORY_DISPLAY_SHOW_ALL_LINK'                    => 'switcher',
    'gm_configuration/CATEGORY_UNFOLD_LEVEL'                             => 'number',
    'gm_configuration/CATEGORY_TOP_SHOW_LEVEL'                           => 'number',
    'gm_configuration/DISPLAY_TAX'                                       => '1-0-switcher',
    'gm_configuration/ENABLE_RATING'                                     => 'switcher',
    'gm_configuration/GM_ANALYTICS_CODE_USE'                             => '1-0-switcher',
    'gm_configuration/GM_CALLBACK_SERVICE_VVCODE'                        => 'switcher',
    'gm_configuration/GM_CHECK_CONDITIONS'                               => '1-0-switcher',
    'gm_configuration/GM_CHECK_PRIVACY_ACCOUNT_ADDRESS_BOOK'             => '1-0-switcher',
    'gm_configuration/GM_CHECK_PRIVACY_ACCOUNT_CONTACT'                  => '1-0-switcher',
    'gm_configuration/GM_CHECK_PRIVACY_ACCOUNT_NEWSLETTER'               => '1-0-switcher',
    'gm_configuration/GM_CHECK_PRIVACY_CALLBACK'                         => '1-0-switcher',
    'gm_configuration/GM_CHECK_PRIVACY_CHECKOUT_PAYMENT'                 => '1-0-switcher',
    'gm_configuration/GM_CHECK_PRIVACY_CHECKOUT_SHIPPING'                => '1-0-switcher',
    'gm_configuration/GM_CHECK_PRIVACY_CONTACT'                          => '1-0-switcher',
    'gm_configuration/GM_CHECK_PRIVACY_FOUND_CHEAPER'                    => '1-0-switcher',
    'gm_configuration/GM_CHECK_PRIVACY_REVIEWS'                          => '1-0-switcher',
    'gm_configuration/GM_CHECK_PRIVACY_TELL_A_FRIEND'                    => '1-0-switcher',
    'gm_configuration/GM_CHECK_WITHDRAWAL'                               => '1-0-switcher',
    'gm_configuration/GM_COOKIE_STATUS'                                  => 'switcher',
    'gm_configuration/GM_CONFIRM_IP'                                     => '1-0-switcher',
    'gm_configuration/GM_CONTACT_VVCODE'                                 => 'switcher',
    'gm_configuration/GM_FORGOT_PASSWORD_VVCODE'                         => 'switcher',
    'gm_configuration/GM_LOG_IP'                                         => '1-0-switcher',
    'gm_configuration/GM_LOGIN_TIMELINE'                                 => 'number',
    'gm_configuration/GM_LOGIN_TIMEOUT'                                  => 'number',
    'gm_configuration/GM_LOGIN_TRYOUT'                                   => 'number',
    'gm_configuration/GM_NEW_PRODUCTS_STARTPAGE'                         => 'number',
    'gm_configuration/GM_NEWSLETTER_VVCODE'                              => 'switcher',
    'gm_configuration/GM_ORDER_STATUS_CANCEL_ID'                         => 'number',
    'gm_configuration/GM_PASSWORD_REENCRYPT'                             => 'switcher',
    'gm_configuration/GM_PRICE_OFFER_VVCODE'                             => 'switcher',
    'gm_configuration/GM_REVIEWS_VVCODE'                                 => 'switcher',
    'gm_configuration/GM_SEARCH_TIMELINE'                                => 'number',
    'gm_configuration/GM_SEARCH_TIMEOUT'                                 => 'number',
    'gm_configuration/GM_SEARCH_TRYOUT'                                  => 'number',
    'gm_configuration/GM_SEO_BOOST_CATEGORIES'                           => 'switcher',
    'gm_configuration/GM_SEO_BOOST_CONTENT'                              => 'switcher',
    'gm_configuration/GM_SEO_BOOST_PRODUCTS'                             => 'switcher',
    'gm_configuration/GM_SEO_BOOST_SHORT_URLS'                           => 'switcher',
    'gm_configuration/GM_SHOW_CONDITIONS'                                => '1-0-switcher',
    'gm_configuration/GM_SHOW_CONDITIONS_CONFIRMATION'                   => '1-0-switcher',
    'gm_configuration/GM_SHOW_PRIVACY_CONFIRMATION'                      => '1-0-switcher',
    'gm_configuration/GM_SHOW_PRIVACY_REGISTRATION'                      => '1-0-switcher',
    'gm_configuration/GM_SHOW_PRIVACY_WITHDRAWAL_WEB_FORM'               => '1-0-switcher',
    'gm_configuration/GM_SHOW_PRIVACY_GV_SEND'                           => '1-0-switcher',
    'gm_configuration/GM_SHOW_WISHLIST'                                  => '1-0-switcher',
    'gm_configuration/GM_SHOW_WITHDRAWAL'                                => '1-0-switcher',
    'gm_configuration/GM_SHOW_WITHDRAWAL_CONFIRMATION'                   => '1-0-switcher',
    'gm_configuration/GM_SPECIALS_STARTPAGE'                             => 'number',
    'gm_configuration/GM_TELL_A_FRIEND'                                  => 'switcher',
    'gm_configuration/GM_TELL_A_FRIEND_VVCODE'                           => 'switcher',
    'gm_configuration/MAIN_SHOW_ATTRIBUTES'                              => 'switcher',
    'gm_configuration/MAIN_SHOW_GRADUATED_PRICES'                        => 'switcher',
    'gm_configuration/MAIN_SHOW_QTY'                                     => 'switcher',
    'gm_configuration/MAIN_SHOW_QTY_INFO'                                => 'switcher',
    'gm_configuration/MAIN_VIEW_MODE_TILED'                              => 'switcher',
    'gm_configuration/SHOW_ACCOUNT_WITHDRAWAL_LINK'                      => '1-0-switcher',
    'gm_configuration/SHOW_ADDITIONAL_FIELDS_PRODUCT_DETAILS'            => 'switcher',
    'gm_configuration/SHOW_FACEBOOK'                                     => 'switcher',
    'gm_configuration/SHOW_WHATSAPP'                                     => 'switcher',
    'gm_configuration/SHOW_MANUFACTURER_IMAGE_LISTING'                   => 'switcher',
    'gm_configuration/SHOW_MANUFACTURER_IMAGE_PRODUCT_DETAILS'           => 'switcher',
    'gm_configuration/SHOW_OLD_DISCOUNT_PRICE'                           => '1-0-switcher',
    'gm_configuration/SHOW_OLD_GROUP_PRICE'                              => '1-0-switcher',
    'gm_configuration/SHOW_OLD_SPECIAL_PRICE'                            => '1-0-switcher',
    'gm_configuration/SHOW_PINTEREST'                                    => 'switcher',
    'gm_configuration/SHOW_PRODUCTS_COUNT'                               => 'switcher',
    'gm_configuration/SHOW_PRODUCT_RIBBONS'                              => 'switcher',
    'gm_configuration/SHOW_RATING_IN_GRID_AND_LISTING'                   => 'switcher',
    'gm_configuration/SHOW_SUBCATEGORIES'                                => 'switcher',
    'gm_configuration/SHOW_TOP_COUNTRY_SELECTION'                        => 'switcher',
    'gm_configuration/SHOW_TOP_CURRENCY_SELECTION'                       => 'switcher',
    'gm_configuration/SHOW_TWITTER'                                      => 'switcher',
    'gm_configuration/SHOW_ZOOM'                                         => 'switcher',
    'gm_configuration/TRUNCATE_PRODUCTS_HISTORY'                         => 'number',
    'gm_configuration/TRUNCATE_PRODUCTS_NAME'                            => 'number',
    'gm_configuration/USE_SEO_BOOST_LANGUAGE_CODE'                       => 'switcher',
    'gm_configuration/WITHDRAWAL_PDF_ACTIVE'                             => '1-0-switcher',
    'gm_configuration/WITHDRAWAL_WEBFORM_ACTIVE'                         => '1-0-switcher',
    'gm_configuration/ALWAYS_SHOW_CONTINUE_SHOPPING_BUTTON'              => '1-0-switcher',
    'gm_configuration/USE_UPCOMING_PRODUCT_SWIPER_ON_INDEX'              => 'switcher',
    'gm_configuration/USE_TOP_PRODUCT_SWIPER_ON_INDEX'                   => 'switcher',
    'gm_configuration/USE_SPECIAL_PRODUCT_SWIPER_ON_INDEX'               => 'switcher',
    'gm_configuration/USE_NEW_PRODUCT_SWIPER_ON_INDEX'                   => 'switcher',
    'gm_configuration/PRIVACY_CHECKBOX_REGISTRATION'                     => '1-0-switcher',
    'gm_configuration/PRIVACY_CHECKBOX_CALLBACK'                         => '1-0-switcher',
    'gm_configuration/PRIVACY_CHECKBOX_CONTACT'                          => '1-0-switcher',
    'gm_configuration/PRIVACY_CHECKBOX_ASK_PRODUCT_QUESTION'             => '1-0-switcher',
    'gm_configuration/PRIVACY_CHECKBOX_FOUND_CHEAPER'                    => '1-0-switcher',
    'gm_configuration/PRIVACY_CHECKBOX_REVIEWS'                          => '1-0-switcher',
    'gm_configuration/PRIVACY_CHECKBOX_ACCOUNT_EDIT'                     => '1-0-switcher',
    'gm_configuration/PRIVACY_CHECKBOX_ADDRESS_BOOK'                     => '1-0-switcher',
    'gm_configuration/PRIVACY_CHECKBOX_NEWSLETTER'                       => '1-0-switcher',
    'gm_configuration/PRIVACY_CHECKBOX_CHECKOUT_SHIPPING'                => '1-0-switcher',
    'gm_configuration/PRIVACY_CHECKBOX_CHECKOUT_PAYMENT'                 => '1-0-switcher',
    'gm_configuration/PRIVACY_CHECKBOX_WITHDRAWAL_WEB_FORM'              => '1-0-switcher',
    'gm_configuration/PRIVACY_CHECKBOX_GV_SEND'                          => '1-0-switcher',
    'gm_configuration/DATA_TRANSFER_TO_TRANSPORT_COMPANIES_STATUS'       => '1-0-switcher',
    'gm_configuration/DATA_TRANSFER_TO_TRANSPORT_COMPANIES_REQUIRED'     => '1-0-switcher',
    'gm_configuration/USE_SMALLER_IMAGES_FOR_PRODUCTS'                   => 'switcher',
    'gm_configuration/GRADUATED_ASSIGN'                                  => '1-0-switcher',
    'gm_configuration/GM_RECOMMENDED_PRODUCTS_STARTPAGE'                 => 'number',
    'gm_configuration/SHOW_ABANDONMENT_OF_WITHDRAWL_DOWNLOAD'            => '1-0-switcher',
    'gm_configuration/SHOW_ABANDONMENT_OF_WITHDRAWL_SERVICE'             => '1-0-switcher',
    'gm_configuration/CHECK_ABANDONMENT_OF_WITHDRAWL_DOWNLOAD'           => '1-0-switcher',
    'gm_configuration/CHECK_ABANDONMENT_OF_WITHDRAWL_SERVICE'            => '1-0-switcher',
    'gm_configuration/GM_CREATE_ACCOUNT_VVCODE'                          => 'switcher',
    'gm_configuration/LOG_IP_CALLBACK'                                   => '1-0-switcher',
    'gm_configuration/LOG_IP_CONTACT'                                    => '1-0-switcher',
    'gm_configuration/LOG_IP_TELL_A_FRIEND'                              => '1-0-switcher',
    'gm_configuration/LOG_IP_FOUND_CHEAPER'                              => '1-0-switcher',
    'gm_configuration/LOG_IP_REVIEWS'                                    => '1-0-switcher',
    'gm_configuration/LOG_IP_ACCOUNT_CONTACT'                            => '1-0-switcher',
    'gm_configuration/LOG_IP_ACCOUNT_ADDRESS_BOOK'                       => '1-0-switcher',
    'gm_configuration/LOG_IP_ACCOUNT_NEWSLETTER'                         => '1-0-switcher',
    'gm_configuration/LOG_IP_WITHDRAWAL_WEB_FORM'                        => '1-0-switcher',
    'gm_configuration/LOG_IP_GV_SEND'                                    => '1-0-switcher',
    'gm_configuration/LOG_IP_SHIPPING'                                   => '1-0-switcher',
    'gm_configuration/LOG_IP_ORDER_SHIPPING_ADDRESS'                     => '1-0-switcher',
    'gm_configuration/LOG_IP_ORDER_PAYMENT_ADDRESS'                      => '1-0-switcher',
    'gm_configuration/SHOW_PRODUCTS_MODEL_IN_SHOPPING_CART_AND_WISHLIST' => 'switcher',
    'gm_configuration/SHOW_PRODUCTS_MODEL_IN_PRODUCT_DETAILS'            => 'switcher',
    'gm_configuration/SHOW_PRODUCTS_MODEL_IN_PRODUCT_LISTS'              => 'switcher',
    'gm_configuration/SHOW_RATING_AS_TAB'                                => 'switcher',
    'gm_configuration/CATEGORY_UNFOLD_DEFAULT_LEVEL'                     => 'number',
    'gm_configuration/GALLERY_LIGHTBOX'                                  => 'switcher',
    'gm_configuration/ENABLE_JS_HYPHENATION'                             => 'switcher',
    'gm_configuration/ENABLE_LIVE_SEARCH'                                => 'switcher',
    'gm_configuration/PRODUCT_REVIEW_NAME'                               => 'product-review-mode',
    'gm_configuration/GM_CAPTCHA_TYPE'                                   => 'captcha-type',
    'gm_configuration/GM_PASSWORD_ENCRYPTION_TYPE'                       => 'password-encryption-type',
];

foreach ($keyTypeRelation as $key => $type) {
    /** @var mysqli_result $check */
    $check = $this->query('SELECT * FROM `gx_configurations` WHERE `key` = "' . $key . '";', true);
    if ($check->num_rows === 1) {
        $query     = 'UPDATE `gx_configurations` SET `type` = "' . $type . '" WHERE `key` = "' . $key . '";';
        $t_success &= $this->query($query, true);
    }
}

/**
 * ADD CONFIGURATIONS IF MISSING
 */

$missingConfigs = [
    'gm_configuration/ADMIN_FEED_ACCEPTED_SHOP_INFORMATION_DATA_PROCESSING'  => [
        'value' => 'false',
        'type'  => 'switcher',
    ],
    'gm_configuration/DISPLAY_0_PROCENT_TAX'                                 => [
        'value' => '0',
        'type'  => '1-0-switcher',
    ],
    'gm_configuration/MANUAL_ORDER_PAYMENT'                                  => [
        'value' => '',
        'type'  => 'default-payment-method',
    ],
    'gm_configuration/GM_LOG_IP_LOGIN'                                       => [
        'value' => '0',
        'type'  => '1-0-switcher',
    ],
    'configuration/SESSION_CHECK_SSL_SESSION_ID'                             => [
        'value' => 'false',
        'type'  => 'switcher',
    ],
    'gm_configuration/GM_SHOW_CAT'                                           => [
        'value' => 'none',
        'type'  => 'dropdown',
    ],
    'gm_configuration/DOWNLOAD_DELAY_FOR_ABANDONMENT_OF_WITHDRAWL_RIGHT'     => [
        'value' => '0',
        'type'  => 'seconds',
    ],
    'gm_configuration/DOWNLOAD_DELAY_WITHOUT_ABANDONMENT_OF_WITHDRAWL_RIGHT' => [
        'value' => '0',
        'type'  => 'seconds',
    ],
];

foreach ($missingConfigs as $key => $config) {
    /** @var mysqli_result $check */
    $check = $this->query('SELECT * FROM `gx_configurations` WHERE `key` = "' . $key . '";', true);
    if ($check->num_rows === 0) {
        $query     = 'INSERT INTO `gx_configurations` (`key`, `value`, `default`, `type`) VALUES ("' . $key . '", "'
                     . $config['value'] . '", "' . $config['value'] . '", "' . $config['type'] . '");';
        $t_success &= $this->query($query, true);
    }
}

/**
 * MIGRATING OLD "DATA_TRANSFER_TO_TRANSPORT_COMPANIES_SETTINGS" VALUE
 */

/** @var mysqli_result $check */
$check = $this->query('SELECT * FROM `gx_configurations`
WHERE `key` = "gm_configuration/DATA_TRANSFER_TO_TRANSPORT_COMPANIES_SETTINGS" AND `value` LIKE "a:%";',
                      true);
if ($check->num_rows > 0) {
    $newValue             = '';
    $dataTransferSettings = unserialize($check->fetch_assoc()['value']);
    foreach ($dataTransferSettings as $method => $value) {
        if ($value = '1') {
            $newValue .= $method . ',';
        }
    }
    
    $query     = 'UPDATE `gx_configurations` SET `value` = "' . rtrim($newValue, ',') . '"
                  WHERE  `key` = "gm_configuration/DATA_TRANSFER_TO_TRANSPORT_COMPANIES_SETTINGS";';
    $t_success &= $this->query($query, true);
}

/***********************************************************
 * Adding new Admin Access group for unknown modules/items
 * and set permissions for this new group.
 ***********************************************************/

/** @var mysqli_result $check */
$check = $this->query('
    SELECT `admin_access_group_id`
    FROM `admin_access_group_items`
    WHERE `identifier` = "unknown-admin-access-item";
', true);
if ($check->num_rows === 0) {
    //
    // Add new group
    //
    $t_success &= $this->query('
        INSERT INTO `admin_access_groups` (`protected`)
        VALUES (1);
    ',true);
    $groupId   = $this->get_insert_id();
    
    $t_success &= $this->query('
        INSERT INTO `admin_access_group_descriptions` (`admin_access_group_id`, `language_id`, `name`, `description`)
        VALUES ('.$groupId.', 1, "Unknown modules", "This permission combines all unknown modules. It is recommended to grant this permission.");
    ', true);
    
    $t_success &= $this->query('
        INSERT INTO `admin_access_group_descriptions` (`admin_access_group_id`, `language_id`, `name`, `description`)
        VALUES ('.$groupId.', 2, "Unbekannte Module", "Diese Berechtigung fasst alle unbekannten Module zusammen. Es wird empfohlen, dass diese Berechtigung immer erlaubt wird.");
    ', true);
    
    $t_success &= $this->query('
        INSERT INTO `admin_access_group_items` (`admin_access_group_id`, `type`, `identifier`)
        VALUES ('.$groupId.', "CONTROLLER", "unknown-admin-access-item");
    ', true);
    $t_success &= $this->query('
        INSERT INTO `admin_access_group_items` (`admin_access_group_id`, `type`, `identifier`)
        VALUES ('.$groupId.', "PAGE", "unknown-admin-access-item");
    ', true);
    $t_success &= $this->query('
        INSERT INTO `admin_access_group_items` (`admin_access_group_id`, `type`, `identifier`)
        VALUES ('.$groupId.', "ROUTE", "unknown-admin-access-item");
    ', true);
    $t_success &= $this->query('
        INSERT INTO `admin_access_group_items` (`admin_access_group_id`, `type`, `identifier`)
        VALUES ('.$groupId.', "AJAX_HANDLER", "unknown-admin-access-item");
    ', true);
    
    
    //
    // Add group permissions for each role
    //
    /** @var mysqli_result $roles */
    $roles = $this->query('SELECT * FROM `admin_access_roles`;', true);
    while ($role = $roles->fetch_assoc()) {
        $t_success &= $this->query('
            INSERT INTO `admin_access_permissions` (`admin_access_role_id`, `admin_access_group_id`, `reading_granted`, `writing_granted`, `deleting_granted`)
            VALUES ('.$role['admin_access_role_id'].', '.$groupId.', '.$role['reading_unknown_group_granted'].', '.$role['writing_unknown_group_granted'].', '.$role['deleting_unknown_group_granted'].');
        ', true);
    }
}

/***********************************************************
 * Adding route for new configuration page to Admin Access
 ***********************************************************/

$check = $this->getAdminAccessGroupIdByIdentifier('ROUTE', '/admin/configurations');
if($check === false){
    $configGroup = $this->getAdminAccessGroupIdByName('Shop Einstellungen', 2);
    $configGroup = ($configGroup === false) ? 47 : $configGroup;
    $this->addAdminAccessGroupItem($configGroup, 'ROUTE', '/admin/configurations');
    $this->addAdminAccessGroupItem($configGroup, 'ROUTE', '/admin/api/configurations');
}

/*************************************************
 *
 * Adding new configuration for Google API Key
 *
 *************************************************/

$checkIfGapiKeyExists = $this->query('SELECT * FROM `gx_configurations` WHERE `key` = "gm_configuration/GOOGLE_API_KEY"', true);
if ($checkIfGapiKeyExists->num_rows < 1) {
    $query = 'INSERT INTO `gx_configurations` (`key`, `value`, `default`, `sort_order`, `legacy_group_id`) VALUES ("gm_configuration/GOOGLE_API_KEY", "", "", NULL, NULL)';
    $t_success &= $this->query($query, true);
}

$checkIfFooterColumn1Exists = $this->query('SELECT * FROM `content_manager_aliases` WHERE `content_alias`="Footer-column-1"',
                                           true);

if ($checkIfFooterColumn1Exists->num_rows < 1) {
    // cleaning up content_manager_aliases table
    $t_success &= $this->query('DELETE FROM `content_manager_aliases` WHERE `content_group` NOT IN (SELECT `content_group` from `content_manager`)', true);
    $nextContentGroupIdQuery  = 'SELECT MAX(`content_group`) + 1 as "next_cm_id" FROM `content_manager`';
    $nextContentGroupIdResult = $this->query($nextContentGroupIdQuery, true);
    $nextContentGroupId       = (int)$nextContentGroupIdResult->fetch_assoc()['next_cm_id'];
    
    $query = "INSERT INTO `content_manager` (`categories_id`, `parent_id`, `group_ids`, `languages_id`, `content_name`, `content_title`, `content_heading`, `content_text`, `sort_order`, `file_flag`, `content_file`, `download_file`, `content_status`, `content_group`, `content_delete`, `gm_link`, `gm_link_target`, `gm_priority`, `gm_changefreq`, `gm_sitemap_entry`, `gm_robots_entry`, `gm_url_keywords`, `protected`, `content_position`, `content_type`,`contents_meta_title`,`contents_meta_description`,`contents_meta_keywords`) VALUES
(0, 0, '', 1, 'Footer column 1', 'Footer column 1', 'Footer column 1', '', 0, 4, '', '', 1, '%s', 0, '', '', '0.5', 'weekly', 0, 0, '', '1', 'elements_footer', 'content','','',''),
(0, 0, '', 2, 'Footer Spalte 1', 'Footer Spalte 1', 'Footer Spalte 1', '', 0, 4, '', '', 1, '%s', 0, '', '', '0.5', 'weekly', 0, 0, '', '1', 'elements_footer', 'content','','','');";
    
    $t_success &= $this->query(sprintf($query, $nextContentGroupId, $nextContentGroupId), true);
    
    $createAliasQuery = 'INSERT INTO `content_manager_aliases` (`content_group`, `content_alias`) VALUES ('
                        . $nextContentGroupId
                        . ', "Footer-column-1"), (4321005, "Footer-column-2"), (4321006, "Footer-column-3"), (4321007, "Footer-column-4");';
    
    $t_success &= $this->query($createAliasQuery, true);
}

/***********************************************
 * add GLBrain export scheme
 */
$check = $this->query('SELECT * FROM `export_schemes` WHERE `scheme_name` = "[Gambio] GLMall" AND `created_by` = "gambio";', true);
if ($check->num_rows < 1) {
    $schemeId = $this->query("
		INSERT INTO `export_schemes`
			(`type_id`, `scheme_name`, `filename`, `field_separator`, `field_quotes`, `date_created`, `date_modified`,
			`date_last_export`, `created_by`, `customers_status_id`, `currencies_id`, `languages_id`, `campaign_id`, `shipping_free_minimum`,
			`quantity_minimum`, `export_attributes`, `export_properties`, `export_features`, `cronjob_allowed`, `cronjob_days`,
			`cronjob_hour`, `cronjob_interval`, `export_all_new_ones`, `export_property_image`, `amount_additional_image_files`)
		VALUES
            (2, '[Gambio] GLMall', 'glmall.csv', ',', '\"', '2020-09-08 11:58:54', '2020-09-11 09:02:36', '1000-01-01 00:00:00',
             'gambio', 1, 1, 2, '0', '0.0000', '0.0000', 0, 1, 0, 0, 'Mon|Tue|Wed|Thu|Fri|Sat|Sun', '4', '0', 1, 0, 10);
		");
    
    if($schemeId > 0)
    {
        $this->query("
			INSERT INTO `export_scheme_fields` (`scheme_id`, `field_name`, `field_content`, `field_content_default`, `created_by`, `sort_order`, `status`)
			VALUES
                (".$schemeId.", 'product_id', '{p_id}', '', 'gambio', 1, 1),
                (".$schemeId.", 'title', '{products_name}', '', 'gambio', 2, 1),
                (".$schemeId.", 'description', '{p_description}', '', 'gambio', 3, 1),
                (".$schemeId.", 'price', '{p_price_point}', '', 'gambio', 4, 1),
                (".$schemeId.", 'currency', '{p_currency}', '', 'gambio', 5, 1),
                (".$schemeId.", 'update_date', '{products_last_modified}', '', 'gambio', 6, 1),
                (".$schemeId.", 'external_link', '{p_link}', '', 'gambio', 7, 1),
                (".$schemeId.", 'main_picture', '{p_popup_image}', '', 'gambio', 8, 1),
                (".$schemeId.", 'gallery_images', '{p_popup_images}', '', 'gambio', 9, 1),
                (".$schemeId.", 'category', '{c_path}', '', 'gambio', 10, 1);
		");
    }
}


/*************************************************************
 * Update Admin Access Groups regarding new configuration page
 *************************************************************/

$productsGroupId = $this->getAdminAccessGroupIdByIdentifier('PAGE', 'categories.php'); # 11
$contentGroupIdOld = $this->getAdminAccessGroupIdByName('Layout / Design', 1); # 19
$contentGroupIdNew = $this->getAdminAccessGroupIdByName('Content', 1); # 19
$logoManagerGroupId = $this->getAdminAccessGroupIdByIdentifier('PAGE', 'gm_logo.php'); # 21
$imageProcessingGroupId = $this->getAdminAccessGroupIdByIdentifier('CONTROLLER', 'ImageProcessing'); # 26
$toolboxGroupId = $this->getAdminAccessGroupIdByName('Toolbox', 1); # 27
$changeTextsGroupId = $this->getAdminAccessGroupIdByIdentifier('PAGE', 'gm_lang_edit.php'); # 31
$importExportGroupId = $this->getAdminAccessGroupIdByName('Import / Export', 1); # 43
$productExportGroupId = $this->getAdminAccessGroupIdByIdentifier('PAGE', 'csv.php'); # 44
$customerExportGroupId = $this->getAdminAccessGroupIdByIdentifier('PAGE', 'gm_module_export.php'); # 45
$configGroupId = $this->getAdminAccessGroupIdByIdentifier('ROUTE', '/admin/configurations'); # 47
$generalSettingsGroupId = $this->getAdminAccessGroupIdByIdentifier('PAGE', 'gm_miscellaneous.php'); # 48
$lawsGroupId = $this->getAdminAccessGroupIdByIdentifier('PAGE', 'gm_laws.php'); # 49
$securityGroupId = $this->getAdminAccessGroupIdByIdentifier('PAGE', 'gm_security.php'); # 55
$redirectionRulesGroupId = $this->getAdminAccessGroupIdByIdentifier('ROUTE', '/admin/redirect-rules'); # 66

# MOVE "SECURITY CONFIG" GROUP ITEMS INTO "MAIN CONFIG" GROUP AND DELETE THE OLD GROUP
if($configGroupId !== $securityGroupId){
    $query = 'UPDATE `admin_access_group_items` SET `admin_access_group_id` = "'.$configGroupId.'" WHERE `admin_access_group_id` = "'.$securityGroupId.'";';
    $t_success &= $this->query($query, true);
    $this->deleteAdminAccessGroupById($securityGroupId);
}

# MOVE "LAW CONFIG" GROUP ITEMS INTO "MAIN CONFIG" GROUP AND DELETE THE OLD GROUP
if($configGroupId !== $lawsGroupId){
    $query = 'UPDATE `admin_access_group_items` SET `admin_access_group_id` = "'.$configGroupId.'" WHERE `admin_access_group_id` = "'.$lawsGroupId.'";';
    $t_success &= $this->query($query, true);
    $this->deleteAdminAccessGroupById($lawsGroupId);
}

# MOVE "GENERAL SETTINGS CONFIG" GROUP ITEMS INTO "MAIN CONFIG" GROUP AND DELETE THE OLD GROUP
if($configGroupId !== $generalSettingsGroupId){
    $query = 'UPDATE `admin_access_group_items` SET `admin_access_group_id` = "'.$configGroupId.'" WHERE `admin_access_group_id` = "'.$generalSettingsGroupId.'";';
    $t_success &= $this->query($query, true);
    $this->deleteAdminAccessGroupById($generalSettingsGroupId);
}

# MOVE "PRODUCT EXPORT" GROUP ITEMS INTO "IMPORT / EXPORT GROUP" AND DELETE THE OLD GROUP
if($importExportGroupId !== $productExportGroupId){
    $query = 'UPDATE `admin_access_group_items` SET `admin_access_group_id` = "'.$importExportGroupId.'" WHERE `admin_access_group_id` = "'.$productExportGroupId.'";';
    $t_success &= $this->query($query, true);
    $this->deleteAdminAccessGroupById($productExportGroupId);
}

# MOVE "CUSTOMER EXPORT" GROUP ITEMS INTO "IMPORT / EXPORT" GROUP AND DELETE THE OLD GROUP
if($importExportGroupId !== $customerExportGroupId){
    $query = 'UPDATE `admin_access_group_items` SET `admin_access_group_id` = "'.$importExportGroupId.'" WHERE `admin_access_group_id` = "'.$customerExportGroupId.'";';
    $t_success &= $this->query($query, true);
    $this->deleteAdminAccessGroupById($customerExportGroupId);
}

# CREATE NEW GROUP FOR "REDIRECTION RULES" MODULE
if($redirectionRulesGroupId === false){
    $names = [
        1 => 'Redirection rules',
        2=> 'Weiterleitungsregeln',
    ];
    $descriptions = [
        1 => 'Allows the management of redirection rules.',
        2=> 'Erlaubt die Verwaltung von Weiterleitungsregeln.',
    ];
    
    $createGroupId = $this->addAdminAccessGroup($names, $descriptions, 1, $toolboxGroupId);
    $t_success &= $createGroupId > 0;
    
    if($createGroupId > 0){
        $this->addAdminAccessGroupItem($createGroupId, 'ROUTE', '/admin/redirect-rules');
        $this->addAdminAccessGroupItem($createGroupId, 'ROUTE', '/admin/redirect-rules/get-rules');
        $this->addAdminAccessGroupItem($createGroupId, 'ROUTE', '/admin/redirect-rules/add-rule');
        $this->addAdminAccessGroupItem($createGroupId, 'ROUTE', '/admin/redirect-rules/delete-rule');
        $this->addAdminAccessGroupItem($createGroupId, 'ROUTE', '/admin/redirect-rules/enable-rule');
        $this->addAdminAccessGroupItem($createGroupId, 'ROUTE', '/admin/redirect-rules/disable-rule');
        $this->addAdminAccessGroupItem($createGroupId, 'ROUTE', '/admin/redirect-rules/update-rule');
        $this->addAdminAccessGroupItem($createGroupId, 'ROUTE', '/admin/redirect-rules/get-configuration');
        $this->addAdminAccessGroupItem($createGroupId, 'ROUTE', '/admin/redirect-rules/save-configuration');
    }
}

# SET "IMAGE PROCESSING" AS SUB GROUP OF "CONFIGURATION"
if($imageProcessingGroupId !== false){
    $query = 'UPDATE `admin_access_groups` SET `parent_id` = "'.$configGroupId.'" WHERE `admin_access_group_id` = "'.$imageProcessingGroupId.'";';
    $t_success &= $this->query($query, true);
}

# SET "LOGO MANAGER" AS SUB GROUP OF "CONFIGURATION"
if($logoManagerGroupId !== false){
    $query = 'UPDATE `admin_access_groups` SET `parent_id` = "'.$configGroupId.'" WHERE `admin_access_group_id` = "'.$logoManagerGroupId.'";';
    $t_success &= $this->query($query, true);
}

# SET "IMPORT / EXPORT" AS SUB GROUP OF "PRODUCTS"
if($importExportGroupId !== false){
    $query = 'UPDATE `admin_access_groups` SET `parent_id` = "'.$productsGroupId.'" WHERE `admin_access_group_id` = "'.$importExportGroupId.'";';
    $t_success &= $this->query($query, true);
}

# SET "CHANGE TEXT" AS SUB GROUP OF "CONTENT"
if($changeTextsGroupId !== false){
    $query = 'UPDATE `admin_access_groups` SET `parent_id` = "'.(($contentGroupIdNew > 0)? $contentGroupIdNew: $contentGroupIdOld).'" WHERE `admin_access_group_id` = "'.$changeTextsGroupId.'";';
    $t_success &= $this->query($query, true);
}

# UPDATE NAME AND DESCRIPTION OF "CONTENT" GROUP
if($contentGroupIdOld !== $contentGroupIdNew && $contentGroupIdOld !== false){
    $query = 'UPDATE `admin_access_group_descriptions` SET `name` = "Content", `description` = "Allows the general usage of layout and design options and content management." WHERE `admin_access_group_id` = "'.$contentGroupIdOld.'" AND `language_id` = 1;';
    $t_success &= $this->query($query, true);
    $query = 'UPDATE `admin_access_group_descriptions` SET `name` = "Inhalte", `description` = "Erlaubt die generelle Nutzung von Darstellungsoptionen und Verwaltung von Inhalten." WHERE `admin_access_group_id` = "'.$contentGroupIdOld.'" AND `language_id` = 2;';
    $t_success &= $this->query($query, true);
}

/*************************
 * ADD MISSING GROUP ITEMS
 *************************/

# Add GROUP ITEM FOR API V3 MAIN ROUTE
$apiMainGroup = $this->getAdminAccessGroupIdByIdentifier('CONTROLLER', 'HttpApiV2'); # 1
if($apiMainGroup > 0) {
    $this->addAdminAccessGroupItem($apiMainGroup, 'ROUTE', '/api.php/v3');
}

# Add GROUP ITEM FOR API V3 MAIN ROUTE
$adminUiGroup = $this->getAdminAccessGroupIdByIdentifier('PAGE', 'admin.php'); # 2
if($adminUiGroup > 0) {
    $this->addAdminAccessGroupItem($adminUiGroup, 'ROUTE', '/admin');
}

# Add GROUP ITEM FOR TRACKING CODES API V3 ROUTE
$parcelServicesGroup = $this->getAdminAccessGroupIdByIdentifier('ROUTE', '/api.php/v3/parcel-services'); # 51
if($parcelServicesGroup > 0) {
    $this->addAdminAccessGroupItem($parcelServicesGroup, 'ROUTE', '/api.php/v3/tracking-codes');
}

$additionalPagesFlag = $this->query("SELECT * FROM `cm_file_flags` WHERE `file_flag_name`='additional_pages'", true);

if ($additionalPagesFlag->num_rows === 0) {
    $newId     = $this->query("SELECT `file_flag` FROM `cm_file_flags` ORDER BY `file_flag` DESC LIMIT 1", true);
    $newId     = (int)$newId->fetch_assoc()['file_flag'] + 1;
    $query     = "INSERT INTO `cm_file_flags` (`file_flag`, `file_flag_name`) VALUES ({$newId}, 'additional_pages')";
    $t_success &= $this->query($query, true);
}

$additionalPagesFlagId = $this->query("SELECT `file_flag` FROM `cm_file_flags` WHERE `file_flag_name`='additional_pages' LIMIT 1",
    true);

if ($additionalPagesFlagId->num_rows <= 1) {
    $additionalPagesFlagId = (int)$additionalPagesFlagId->fetch_assoc()['file_flag'];
    
    $checkIfWrongAdditionalPageContentExists = $this->query("SELECT * FROM `cm_file_flags` WHERE `file_flag_name` = 'additional_pages' AND `file_flag` != {$additionalPagesFlagId}",
        true);
    
    if ($checkIfWrongAdditionalPageContentExists->num_rows < 1) {
        $t_success &= $this->query("UPDATE `content_manager` SET `file_flag` = {$additionalPagesFlagId} WHERE `content_position` = 'pages_additional' AND `file_flag` != {$additionalPagesFlagId}",
            true);
    }
}

// Adds GoogleMaps purpose if it does not exist

$googleMapsCookiePurpose = $this->query("SELECT * FROM cookie_consent_panel_purposes WHERE purpose_alias = 'gambio/googleMaps'");
if (!$googleMapsCookiePurpose) {
    $shopLanguages = $this->getLanguages();
    
    if ($shopLanguages) {
        $lastPurposeId = $this->query("SELECT purpose_id FROM cookie_consent_panel_purposes ORDER BY purpose_id DESC LIMIT 1", true);
        $lastPurposeId = (int)$lastPurposeId->fetch_assoc()['purpose_id'] + 1;
        
        $values = '';
        foreach ($shopLanguages as $language) {
            $values .= "({$lastPurposeId}, {$language['languages_id']}, 2, '', 'Google Maps', 'gambio/googleMaps', 0, 0),";
        }
        
        if ($values) {
            $values = rtrim($values, ',');
            $insertQuery = "INSERT INTO `cookie_consent_panel_purposes`
                                (purpose_id, language_id, category_id, purpose_description, purpose_name, purpose_alias, purpose_status, purpose_deletable)
                                VALUES
                                {$values};
                            ";
            $t_success &= $this->query($insertQuery, true);
        }
        
    }
}