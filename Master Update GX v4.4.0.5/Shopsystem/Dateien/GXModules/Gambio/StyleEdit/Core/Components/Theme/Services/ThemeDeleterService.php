<?php
/**
 * ThemeDeleterService.php 2021-03-21
 * Gambio GmbH
 * http://www.gambio.de
 * Copyright (c) 2021 Gambio GmbH
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

namespace Gambio\StyleEdit\Core\Components\Theme\Services;

use Exception;
use FileNotFoundException;
use Gambio\StyleEdit\Adapters\Interfaces\CacheCleanerInterface;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\BasicThemeInterface;
use Gambio\StyleEdit\Core\Components\Theme\Exceptions\UndeletableThemeException;
use Gambio\StyleEdit\Core\Components\Theme\Repositories\StyleEditThemeRepository;
use Gambio\StyleEdit\Core\Components\Theme\Repositories\ThemeConfigurationRepository;
use Gambio\StyleEdit\Core\Json\FileIO;
use Gambio\StyleEdit\Core\SingletonPrototype;
use Gambio\StyleEdit\Core\TranslatedException;

/**
 * Class ThemeDeleterService
 * @package Gambio\StyleEdit\Core\Components\Theme\Services
 */
class ThemeDeleterService
{
    /**
     * @var CacheCleanerInterface
     */
    private $cacheCleaner;
    /**
     * @var ThemeConfigurationRepository
     */
    private $configurationRepository;
    /**
     * @var StyleEditThemeRepository
     */
    private $repository;
    
    /**
     * @var FileIO
     */
    protected $fileIO;
    
    
    /**
     * ThemeDeleterService constructor.
     *
     * @param ThemeConfigurationRepository $configurationRepository
     * @param StyleEditThemeRepository     $repository
     * @param CacheCleanerInterface        $cacheCleaner
     */
    public function __construct(
        ThemeConfigurationRepository $configurationRepository,
        StyleEditThemeRepository $repository,
        CacheCleanerInterface $cacheCleaner
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->repository              = $repository;
        $this->cacheCleaner            = $cacheCleaner;
    }
    
    
    /**
     * @param BasicThemeInterface $theme
     *
     * @throws FileNotFoundException
     * @throws TranslatedException
     * @throws UndeletableThemeException
     * @throws Exception
     */
    public function deleteTheme(BasicThemeInterface $theme): void
    {
        if ($this->themeIsAPreview($theme) === true) {
    
            $this->deletePreview($theme);
            return;
        }
        
        $theme = $this->configurationRepository->getById($theme->id());
        
        if ($theme->isActive()) {
            throw new UndeletableThemeException('Is not possible to delete the active theme!');
        }
    
        if (count($theme->children()) > 0) {
            throw new UndeletableThemeException('Theme has children!');
        }
    
        if (!$theme->isRemovable()) {
            throw new UndeletableThemeException('The theme cannot be deleted!');
        }
        
        $this->deletePreviewOf($theme);
        $this->repository->delete($theme);
        $this->cacheCleaner->clearShopCache();
    }
    
    
    /**
     * @param BasicThemeInterface $themeId
     */
    protected function deletePreviewOf(BasicThemeInterface $themeId): void
    {
        try {
            $theme = $this->configurationRepository->getById($themeId->id() . '_preview');
            if ($theme) {
                $this->repository->delete($theme);
            }
        } catch (Exception $e) {
            //ignore exception
        }
    }
    
    
    /**
     * @param BasicThemeInterface $theme
     *
     * @throws Exception
     */
    protected function deletePreview(BasicThemeInterface $theme): void
    {
        if ($this->fileIO()->exists($this->themePath($theme))) {
            
            $this->fileIO()->recursive_rmdir($this->themePath($theme));
        }
    }
    
    
    /**
     * @param BasicThemeInterface $theme
     *
     * @return bool
     * @throws Exception
     */
    protected function themeIsAPreview(BasicThemeInterface $theme): bool
    {
        $previewPattern = '#_preview$#';
        
        if (preg_match($previewPattern, $theme->id()) === 1) {
            
            $themeJsonPath = $this->themePath($theme) . DIRECTORY_SEPARATOR . 'theme.json';
            $themeJson     = $this->fileIO()->read($themeJsonPath);
            
            return isset($themeJson->preview) && $themeJson->preview;
        }
        
        return false;
    }
    
    
    /**
     * @param BasicThemeInterface $theme
     *
     * @return string
     */
    protected function themePath(BasicThemeInterface $theme): string
    {
        return DIR_FS_CATALOG . 'themes' . DIRECTORY_SEPARATOR . $theme->id();
    }
    
    /**
     * @return FileIO
     * @throws Exception
     */
    protected function fileIO(): FileIO
    {
        if ($this->fileIO === null) {
    
            $this->fileIO = SingletonPrototype::instance()->get(FileIO::class);
        }
        
        return $this->fileIO;
    }
}