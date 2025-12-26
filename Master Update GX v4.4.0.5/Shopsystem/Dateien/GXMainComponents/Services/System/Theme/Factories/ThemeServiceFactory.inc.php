<?php
/*--------------------------------------------------------------------------------------------------
    ThemeServiceFactory.inc.php 2020-09-14
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\GX\Services\System\ThemeSettings\Factories\ThemeSettingsDataFactory;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class ThemeServiceFactory
 */
class ThemeServiceFactory
{
    /**
     * @var ThemeRepository
     */
    protected static $repository;


    /**
     * Creates an instance of theme service.
     *
     * @param ExistingDirectory $source
     *
     * @return ThemeService
     */
    public static function createThemeService(ExistingDirectory $source)
    {
        $locker              = MainFactory::create(RoutineLocker::class, $source, ThemeService::class);
        $cacheControl        = MainFactory::create_object('CacheControl');
        $entryStorage        = MainFactory::create(ThemeContentManagerEntryStorage::class);
        $queryBuilder        = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $contentWriteService = StaticGXCoreLoader::getService('ContentWrite');
        
        return MainFactory::create(ThemeService::class,
                                   static::_createRepository($source),
                                   $cacheControl,
                                   $entryStorage,
                                   $queryBuilder,
                                   $contentWriteService,
                                   $locker);
    }


    /**
     * Returns an instance of theme repository.
     *
     * @param ExistingDirectory $source
     *
     * @return ThemeRepository
     */
    protected static function _createRepository(ExistingDirectory $source)
    {
        if (null === static::$repository) {
            $filesystemAdapter = new Local($source->getAbsolutePath(), LOCK_EX, Local::DISALLOW_LINKS, [
                'file' => [
                    'public'  => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ]
            ]);
            $filesystem        = new Filesystem($filesystemAdapter);
            $shopAdapter       = MainFactory::create(FilesystemAdapter::class, $filesystem);
            $reader            = MainFactory::create(ThemeReader::class);
            $factory           = MainFactory::create(ThemeSettingsDataFactory::class, $shopAdapter, $source->getAbsolutePath());
            $writer            = MainFactory::create(ThemeWriter::class, $shopAdapter, $source, GXModulesCache::getInstalledThemeFiles(), $factory);
            
            static::$repository = MainFactory::create(ThemeRepository::class, $reader, $writer);
        }
        
        return static::$repository;
    }
}