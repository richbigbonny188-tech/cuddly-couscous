<?php
/*--------------------------------------------------------------------------------------------------
    PagesInterfaces.php 2020-07-03
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

/**
 * Interface PageType
 */
interface PageType
{
    public const  CALLBACK_SERVICE     = 'CallbackService';
    public const  MANUFACTURERS        = 'Manufacturers';
    public const  PRODUCT_INFO         = 'ProductInfo';
    public const  CAT                  = 'Cat';
    public const  SEARCH               = 'Search';
    public const  PRICE_OFFER          = 'PriceOffer';
    public const  CART                 = 'Cart';
    public const  CONTENT              = 'Content';
    public const  WISH_LIST            = 'Wishlist';
    public const  ADDRESS_BOOK_PROCESS = 'AddressBookProcess';
    public const  GV_SEND              = 'GVSend';
    public const  CHECKOUT             = 'Checkout';
    public const  ACCOUNT_HISTORY      = 'AccountHistory';
    public const  ACCOUNT              = 'Account';
    public const  INDEX                = 'Index';
    public const  WITHDRAWAL           = 'Withdrawal';
    public const  LOGOFF               = 'Logoff';

}