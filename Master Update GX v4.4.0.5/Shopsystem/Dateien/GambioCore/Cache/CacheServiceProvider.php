<?php
/* --------------------------------------------------------------
 CacheFactoryServiceProvider.php 2020-04-29
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Cache;

use Gambio\Core\Filesystem\Interfaces\Filesystem;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class CacheFactoryServiceProvider
 * @package Gambio\Core\Cache
 */
class CacheServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            CacheFactory::class
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->application->registerShared(CacheFactory::class, FileCacheFactory::class)->addArgument(
                EventDispatcherInterface::class
            )->addArgument(Filesystem::class);
    }
}