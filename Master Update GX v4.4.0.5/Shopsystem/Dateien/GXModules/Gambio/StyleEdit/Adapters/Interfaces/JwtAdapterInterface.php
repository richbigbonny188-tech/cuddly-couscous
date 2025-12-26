<?php
/*--------------------------------------------------------------------------------------------------
    JwtAdapterInterface.php 2020-05-06
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\StyleEdit\Adapters\Interfaces;


interface JwtAdapterInterface
{
    /**
     * @return string
     */
    public function getCurrentUserToken(): string;

    public function getShoppingSecret() : string;

}