<?php
/*--------------------------------------------------------------------------------------------------
    CookieConsentGoogleMapsPurposeDTO.php 2020-10-30
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace GXModules\Gambio\CookieConsentPanel\Shop\Classes\DTO;

class CookieConsentGoogleMapsPurposeDTO extends \CookieConsentPurposeDTO
{
    public function __construct()
    {
        parent::__construct(
            2,
            'google_maps.purpose_title',
            'google_maps.purpose_description',
            'gambio/googleMaps',
            false
        );
    }
}