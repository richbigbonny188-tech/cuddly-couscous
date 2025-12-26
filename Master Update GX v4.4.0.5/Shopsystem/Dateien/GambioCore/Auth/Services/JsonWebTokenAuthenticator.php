<?php
/* --------------------------------------------------------------
   JsonWebTokenAuthenticator.php 2020-04-16
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Auth\Services;

use Gambio\Core\Auth\Exceptions\AuthenticationException;
use Gambio\Core\Auth\Repositories\JsonWebTokenRepository;
use Gambio\Core\Auth\UserId;

/**
 * Class JsonWebTokenAuthenticator
 *
 * @package Gambio\Core\Auth
 */
class JsonWebTokenAuthenticator implements \Gambio\Core\Auth\JsonWebTokenAuthenticator
{
    /**
     * @var JsonWebTokenRepository
     */
    private $repository;
    
    
    /**
     * JsonWebTokenAuthenticator constructor.
     *
     * @param JsonWebTokenRepository $repository
     */
    public function __construct(JsonWebTokenRepository $repository)
    {
        $this->repository = $repository;
    }
    
    
    /**
     * @inheritDoc
     */
    public function authenticate(string $token): UserId
    {
        [$header, $payload, $inboundSignature] = explode('.', $token);
        
        $jwt = $this->repository->getJsonWebToken($token);
        if ($jwt->getSignature() !== $inboundSignature) {
            $jwt = $this->repository->getCompatibilityWebToken($token);
            if ($jwt->getSignature() !== $inboundSignature) {
                throw new AuthenticationException('Can not authenticate provided JSON web token.');
            }
        }
        
        $iatTimestamp = abs((int)$jwt->payload()['iat']);
        $expTimestamp = abs((int)$jwt->payload()['exp']);
        $userId       = (int)$jwt->payload()['customer_id'];
        
        $time = time();
        if ($userId <= 0 || $iatTimestamp > $time || $expTimestamp < $time) {
            throw new AuthenticationException('Can not authenticate provided JSON web token.');
        }
        
        return \Gambio\Core\Auth\Model\UserId::create($userId);
    }
}