<?php
/* --------------------------------------------------------------
 ConfigurationMigrationMapping.php 2019-12-12
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2019 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Configuration\Migration;

class ConfigurationMigrationMapping
{
    public const TYPE_MAP = [
        'switcher'               => [
            // gId = 1 (my shop)
            'USE_DEFAULT_LANGUAGE_CURRENCY',
            'DISPLAY_CART',
            'SHOW_COUNTS',
            'ALLOW_ADD_TO_CART',
            'PRICE_IS_BRUTTO',
            'APPEND_PROPERTIES_MODEL',
            'UNFOLD_FAVS_BOX',
            'SEARCH_IN_ATTR',
            'SEARCH_IN_DESC',

            // gId 4 (image options)
            'CONFIG_CALCULATE_IMAGE_SIZE',

            // gId 5 (customer options)
            'ACCOUNT_FAX',
            'ACCOUNT_GENDER',
            'ACCOUNT_TELEPHONE',
            'ACCOUNT_DOB',
            'ACCOUNT_COMPANY',
            'ACCOUNT_SUBURB',
            'ACCOUNT_STATE',
            'ACCOUNT_ADDITIONAL_INFO',
            'ACCOUNT_SPLIT_STREET_INFORMATION',
            'ACCOUNT_B2B_STATUS',
            'DELETE_GUEST_ACCOUNT',
            'ACCOUNT_DEFAULT_B2B_STATUS',
            'ACCOUNT_NAMES_OPTIONAL',
            'GENDER_MANDATORY',

            // gId => 7 (shipping options)
            'SHOW_SHIPPING',
            'SHOW_CART_SHIPPING_COSTS',
            'SHOW_CART_SHIPPING_WEIGHT',

            // gId => 9 (stocking options)
            'GM_SET_OUT_OF_STOCK_ATTRIBUTES',
            'GM_SET_OUT_OF_STOCK_ATTRIBUTES_SHOW',
            'GM_SET_OUT_OF_STOCK_PRODUCTS',
            'STOCK_CHECK',
            'ATTRIBUTE_STOCK_CHECK',
            'DOWNLOAD_STOCK_CHECK',
            'STOCK_LIMITED',
            'STOCK_ALLOW_CHECKOUT',
            'CHECK_STOCK_BEFORE_SHOPPING_CART',

            // gId => 10 (logging options)
            'LOGGING_ENABLED',
            'STORE_PAGE_PARSE_TIME',
            'DISPLAY_PAGE_PARSE_TIME',
            'LOG_SQL_FRONTEND',
            'LOG_SQL_BACKEND',
            'STORE_DB_TRANSACTIONS',
            'ERROR_REPORT_HIDE_E_ERROR',
            'ERROR_REPORT_HIDE_E_WARNING',
            'ERROR_REPORT_HIDE_E_PARSE',
            'ERROR_REPORT_HIDE_E_NOTICE',
            'ERROR_REPORT_HIDE_E_CORE_ERROR',
            'ERROR_REPORT_HIDE_E_CORE_WARNING',
            'ERROR_REPORT_HIDE_E_COMPILE_ERROR',
            'ERROR_REPORT_HIDE_E_COMPILE_WARNING',
            'ERROR_REPORT_HIDE_E_USER_ERROR',
            'ERROR_REPORT_HIDE_E_USER_WARNING',
            'ERROR_REPORT_HIDE_E_USER_NOTICE',
            'ERROR_REPORT_HIDE_E_ALL',
            'ERROR_REPORT_HIDE_E_STRICT',
            'ERROR_REPORT_HIDE_E_RECOVERABLE_ERROR',
            'ERROR_REPORT_HIDE_E_DEPRECATED',

            // gId => 12 (email options)
            'SMTP_AUTH',
            'EMAIL_USE_HTML',
            'ENTRY_EMAIL_ADDRESS_CHECK',
            'SEND_EMAILS',

            // gId = 13 (download options)
            'DOWNLOAD_BY_REDIRECT',
            'DOWNLOAD_ENABLED',

            // gId => 14 (performance options)
            'GZIP_COMPRESSION',
            'HTML_COMPRESSION',
            'CSS_INLINE_OUTPUT',
            'PREFER_GZHANDLER',
            'USE_BUSTFILES',

            // gId => 15 (session options)
            'SESSION_FORCE_COOKIE_USE',
            'SESSION_CHECK_SSL_SESSION_ID',
            'SESSION_CHECK_USER_AGENT',
            'SESSION_CHECK_IP_ADDRESS',
            'SESSION_RECREATE',
            'CHECK_CLIENT_AGENT',

            // gId => 17 (additional modules)
            'ACTIVATE_GIFT_SYSTEM',
            'ACTIVATE_NAVIGATOR',
            'ACTIVATE_PAGE_TOKEN',
            'ACTIVATE_REVERSE_CROSS_SELLING',
            'GROUP_CHECK',
            'QUICKLINK_ACTIVATED',
            'SECURITY_CODE_LENGTH',
            'SHOW_UNTRANSLATED_MENUITEMS',
            'USE_WYSIWYG',

            // gId => 18 (ust id nr options)
            'ACCOUNT_COMPANY_VAT_CHECK',
            'ACCOUNT_COMPANY_VAT_GROUP',
            'ACCOUNT_COMPANY_VAT_LIVE_CHECK',
            'ACCOUNT_VAT_BLOCK_ERROR',
            'MOVE_ONLY_IF_NO_GUEST',
            'CALCULATE_TAX_BASED_ON_VAT_ID',

            // gId 19 (legacy google conversions)
            'GOOGLE_CONVERSION',

            // gId => 21 (afterbuy, deprecated)
            'AFTERBUY_ACTIVATED',

            // ot_gv module
            'MODULE_ORDER_TOTAL_GV_CREDIT_TAX',
            'MODULE_ORDER_TOTAL_GV_INC_SHIPPING',
            'MODULE_ORDER_TOTAL_GV_INC_TAX',
            'MODULE_ORDER_TOTAL_GV_QUEUE',
            'MODULE_ORDER_TOTAL_GV_SHOW_INFO',
            'MODULE_ORDER_TOTAL_GV_STATUS',

            // order total status
            'MODULE_ORDER_TOTAL_COD_FEE_STATUS',
            'MODULE_ORDER_TOTAL_COUPON_STATUS',
            'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING',
            'MODULE_ORDER_TOTAL_COUPON_INC_TAX',
            'MODULE_ORDER_TOTAL_COUPON_SHOW_INFO',

            'MODULE_ORDER_TOTAL_DISCOUNT_STATUS',

            'MODULE_ORDER_TOTAL_GAMBIOULTRA_STATUS',
            'MODULE_ORDER_TOTAL_GAMBIOULTRA_DETAILS',

            'MODULE_ORDER_TOTAL_GM_TAX_FREE_STATUS',

            'MODULE_ORDER_TOTAL_TSEXCELLENCE_STATUS',

            'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS',
            'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE',

            'MODULE_ORDER_TOTAL_PAYMENT_STATUS',
            'MODULE_ORDER_TOTAL_PAYMENT_INC_SHIPPING',
            'MODULE_ORDER_TOTAL_PAYMENT_INC_TAX',
            'MODULE_ORDER_TOTAL_PAYMENT_CALC_TAX',

            'MODULE_ORDER_TOTAL_PAYPAL3_INSTFEE_STATUS',

            'MODULE_ORDER_TOTAL_PS_FEE_STATUS',

            'MODULE_ORDER_TOTAL_SHIPPING_STATUS',
            'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING',

            'MODULE_ORDER_TOTAL_SOFORT_STATUS',
            'MODULE_ORDER_TOTAL_SOFORT_INC_SHIPPING',
            'MODULE_ORDER_TOTAL_SOFORT_INC_TAX',
            'MODULE_ORDER_TOTAL_SOFORT_CALC_TAX',
            'MODULE_ORDER_TOTAL_SOFORT_BREAK',

            'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS',
            'MODULE_ORDER_TOTAL_SUBTOTAL_NO_TAX_STATUS',

            'MODULE_ORDER_TOTAL_TAX_STATUS',

            'MODULE_ORDER_TOTAL_TOTAL_STATUS',

            'MODULE_ORDER_TOTAL_TOTAL_NETTO_STATUS',

            'MODULE_HERMES_STATUS',
            'MODULE_GLS_STATUS',
            'MODULE_DELISPRINT_STATUS',

            'MODULE_BRICKFOX_STATUS',
            'MODULE_PAYMENT_GAMBIO_HUB_DATA_OBSERVER',
            'MODULE_(PAYMENT|SHIPPING|ORDER_TOTAL)_.+_STATUS'
        ],
        'number'                 => [
            // gId 2 (minimum values)
            'ENTRY_FIRST_NAME_MIN_LENGTH',
            'ENTRY_LAST_NAME_MIN_LENGTH',
            'ENTRY_DOB_MIN_LENGTH',
            'ENTRY_EMAIL_ADDRESS_MIN_LENGTH',
            'ENTRY_STREET_ADDRESS_MIN_LENGTH',
            'ENTRY_HOUSENUMBER_MIN_LENGTH',
            'ENTRY_COMPANY_MIN_LENGTH',
            'ENTRY_POSTCODE_MIN_LENGTH',
            'ENTRY_CITY_MIN_LENGTH',
            'ENTRY_STATE_MIN_LENGTH',
            'ENTRY_TELEPHONE_MIN_LENGTH',
            'ENTRY_PASSWORD_MIN_LENGTH',
            'CC_OWNER_MIN_LENGTH',
            'CC_NUMBER_MIN_LENGTH',
            'REVIEW_TEXT_MIN_LENGTH',
            'MIN_DISPLAY_BESTSELLERS',
            'MIN_DISPLAY_ALSO_PURCHASED',

            // gId 3 (maximum values)
            'MAX_ADDRESS_BOOK_ENTRIES',
            'MAX_DISPLAY_SEARCH_RESULTS',
            'MAX_DISPLAY_PAGE_LINKS',
            'MAX_DISPLAY_SPECIAL_PRODUCTS',
            'MAX_DISPLAY_UPCOMING_PRODUCTS',
            'MAX_MANUFACTURERS_LIST',
            'MAX_DISPLAY_MANUFACTURERS_IN_A_LIST',
            'MAX_DISPLAY_MANUFACTURER_NAME_LEN',
            'MAX_DISPLAY_NEW_REVIEWS',
            'MAX_RANDOM_SELECT_REVIEWS',
            'MAX_RANDOM_SELECT_NEW',
            'MAX_RANDOM_SELECT_SPECIALS',
            'MAX_DISPLAY_CATEGORIES_PER_ROW',
            'MAX_DISPLAY_PRODUCTS_NEW',
            'MAX_DISPLAY_BESTSELLERS',
            'MAX_DISPLAY_CROSSSELLING',
            'MAX_DISPLAY_ALSO_PURCHASED',
            'MAX_DISPLAY_ALSO_PURCHASED_DAYS',
            'MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX',
            'MAX_DISPLAY_ORDER_HISTORY',
            'PRODUCT_REVIEWS_VIEW',
            'MAX_PRODUCTS_QTY',
            'MAX_DISPLAY_NEW_PRODUCTS_DAYS',

            // gId 4 (image options)
            'IMAGE_QUALITY',
            'MO_PICS',
            'PRODUCT_IMAGE_THUMBNAIL_WIDTH',
            'PRODUCT_IMAGE_THUMBNAIL_HEIGHT',
            'PRODUCT_IMAGE_INFO_WIDTH',
            'PRODUCT_IMAGE_INFO_HEIGHT',
            'PRODUCT_IMAGE_POPUP_WIDTH',
            'PRODUCT_IMAGE_POPUP_HEIGHT',

        ],
        'textarea'               => [
            'STORE_NAME_ADDRESS',
            'EMAIL_SIGNATURE'
        ],
        'country'                => [
            'STORE_COUNTRY',
            'SHIPPING_ORIGIN_COUNTRY'
        ],
        'country-zone'           => [
            'STORE_ZONE'
        ],
        'ordering'               => [
            'EXPECTED_PRODUCTS_SORT'
        ],
        'default-customer-group' => [
            'DEFAULT_CUSTOMERS_STATUS_ID',
            'DEFAULT_CUSTOMERS_VAT_STATUS_ID',
            'DEFAULT_CUSTOMERS_VAT_STATUS_ID_LOCAL',
        ],
        'download-order-status'  => [
            'DOWNLOAD_MIN_ORDERS_STATUS',
        ],
        'products-order-by'      => [
            'EXPECTED_PRODUCTS_FIELD'
        ],
        'search-operator'        => [
            'ADVANCED_SEARCH_DEFAULT_OPERATOR'
        ],
        'account-type'           => [
            'ACCOUNT_OPTIONS',
        ],
        'account-template'       => [
            'ACCOUNT_TYPE_DEFAULT',
        ],
        'email-transport-method' => [
            'EMAIL_TRANSPORT'
        ],
        'line-break'             => [
            'EMAIL_LINEFEED'
        ],
        'smtp-encryption'        => [
            'SMTP_ENCRYPTION'
        ],
        'sender-mail'            => [
            'SEND_EMAIL_BY_BILLING_ADRESS'
        ],
        'order-status'           => [
            'AFTERBUY_ORDERSTATUS'
        ],
        'tax-calculation-mode'   => [
            'MODULE_ORDER_TOTAL_GV_CALC_TAX',
            'MODULE_ORDER_TOTAL_COUPON_CALC_TAX'
        ],
        'tax-class'              => [
            'MODULE_(PAYMENT|SHIPPING|ORDER_TOTAL)_.+_TAX_CLASS'
        ],
        'shipping-destination'   => [
            'MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION',
            'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION'
        ],
        'cod-fee'                => [
            'MODULE_ORDER_TOTAL_COD_FEE_RULES',
            'configuration/MODULE_ORDER_TOTAL_COD_FEE_TRANSFER_CHARGE'
        ]
    ];


    public const CONFIGURATION = [
        'configuration_key'      => 'key',
        'configuration_value'    => 'value',
        'configuration_group_id' => 'legacy_group_id',
        'sort_order'             => 'sort_order',
        'type'                   => 'type',
    ];

    public const GM_CONTENTS = [
        'languages_id'  => 'language_id',
        'gm_key'        => 'key',
        'gm_value'      => 'value',
        'gm_group_id'   => 'legacy_group_id',
        'gm_sort_order' => 'sort_order',
    ];

    public const GM_CONFIGURATION = [
        'gm_key'        => 'key',
        'gm_value'      => 'value',
        //'gm_group_id'   => 'group_id',
        'gm_sort_order' => 'sort_order',
    ];

    public const CONFIGURATION_STORAGE = [
        'key'           => 'key',
        'value'         => 'value',
        'last_modified' => 'last_modified',
    ];
}