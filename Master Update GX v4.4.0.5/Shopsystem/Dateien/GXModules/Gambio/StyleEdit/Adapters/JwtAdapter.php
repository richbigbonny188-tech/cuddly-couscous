<?php
/*--------------------------------------------------------------------------------------------------
    JwtAdapter.php 2020-05-06
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2016 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */
namespace Gambio\StyleEdit\Adapters;


use Firebase\JWT\JWT;
use Gambio\StyleEdit\Adapters\Interfaces\JwtAdapterInterface;
use SessionHandler;
use StyleEdit4AuthenticationController;

class JwtAdapter implements JwtAdapterInterface
{

    public function getCurrentUserToken(): string
    {
        session_destroy();
        session_set_save_handler(new SessionHandler(), true);
        session_start();
        $secret     = StyleEdit4AuthenticationController::getSecret();
        $tokenArray = StyleEdit4AuthenticationController::getTokenArray();

        return JWT::encode($tokenArray, $secret);
    }

    public function getShoppingSecret(): string
    {
        return StyleEdit4AuthenticationController::getSecret();
    }
}