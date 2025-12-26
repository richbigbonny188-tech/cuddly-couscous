<?php
/* --------------------------------------------------------------
 FilesystemServiceProvider.php 2020-04-29
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Filesystem;

use Gambio\Core\Application\ValueObjects\Path;
use Gambio\Core\Filesystem\Interfaces\Filesystem;
use Gambio\Core\Application\DependencyInjection\AbstractServiceProvider;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem as LeagueFileSystem;

/**
 * Class FilesystemServiceProvider
 * @package Gambio\Core\Filesystem
 */
class FilesystemServiceProvider extends AbstractServiceProvider
{
    /**
     * Cache file/dir permissions.
     *
     * @var array
     */
    private static $permissions = [
        'file' => [
            'public'  => 0777,
            'private' => 0777,
        ],
        'dir'  => [
            'public'  => 0777,
            'private' => 0777,
        ]
    ];
    
    
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            Filesystem::class
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        /** @var Path $path */
        $path     = $this->application->get(Path::class);
        $basePath = $path->base();
        
        $this->application->register(Local::class)->addArgument($basePath)->addArgument(LOCK_EX)->addArgument(
                Local::DISALLOW_LINKS
            )->addArgument(static::$permissions);
        $this->application->register(LeagueFileSystem::class)->addArgument(Local::class);
        
        $this->application->registerShared(Filesystem::class, FlysystemAdapter::class)->addArgument(
                LeagueFileSystem::class
            )->addArgument($basePath);
    }
}