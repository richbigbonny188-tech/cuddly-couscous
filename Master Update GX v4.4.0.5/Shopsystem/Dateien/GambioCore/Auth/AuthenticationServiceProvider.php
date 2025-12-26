<?php
/* --------------------------------------------------------------
 AuthenticationServiceProvider.php 2020-04-16
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Auth;

use Doctrine\DBAL\Connection;
use Gambio\Core\Auth\HashStrategies\Md5HashStrategy;
use Gambio\Core\Auth\HashStrategies\PhpNativeHashStrategy;
use Gambio\Core\Auth\Repositories\JsonWebTokenRepository;
use Gambio\Core\Auth\Repositories\JsonWebTokenSecretProvider;
use Gambio\Core\Auth\Repositories\UserReader;
use Gambio\Core\Auth\Repositories\UserRepository;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;

/**
 * Class AuthenticationServiceProvider
 *
 * @package Gambio\Core\Auth
 */
class AuthenticationServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            UserAuthenticator::class,
            JsonWebTokenAuthenticator::class,
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->register(UserReader::class)->addArgument(Connection::class);
        $this->application->register(UserRepository::class)->addArgument(UserReader::class);
        $this->application->register(PhpNativeHashStrategy::class);
        $this->application->register(Md5HashStrategy::class);
        
        $this->application->registerShared(UserAuthenticator::class, Services\UserAuthenticator::class)->addArgument(
                UserRepository::class
            )->addArgument(PhpNativeHashStrategy::class)->addArgument(Md5HashStrategy::class);
        
        $this->application->register(JsonWebTokenSecretProvider::class)->addArgument(Connection::class);
        $this->application->register(JsonWebTokenRepository::class)->addArgument(JsonWebTokenSecretProvider::class);
        
        $this->application->registerShared(JsonWebTokenAuthenticator::class, Services\JsonWebTokenAuthenticator::class)
                          ->addArgument(JsonWebTokenRepository::class);
    }
}