<?php
/* --------------------------------------------------------------
  PublishedThemeRemover.php 2020-09-02
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

/**
 * Class PublishedThemeRemover
 */
class PublishedThemeRemover implements PublishedThemeRemoverInterface
{
    /**
     * @var FilesystemAdapter
     */
    protected $filesystem;
    
    /**
     * @var ShopPathsInterface
     */
    protected $shopPaths;
    
    
    /**
     * PublishedThemeRemover constructor.
     *
     * @param FilesystemAdapter  $filesystem
     * @param ShopPathsInterface $shopPaths
     */
    public function __construct(FilesystemAdapter $filesystem, ShopPathsInterface $shopPaths)
    {
        $this->filesystem = $filesystem;
        $this->shopPaths  = $shopPaths;
    }
    
    
    public function removePublishedTheme(): void
    {
        $publishedThemePath = $this->shopPaths->publishedThemePath();
        $gitkeepFilePath    = $publishedThemePath . '/.gitkeep';
        $gitkeepTmpFilePath = str_replace('/theme', '', $publishedThemePath) . '/.gitkeep';

        if ($this->filesystem->has($publishedThemePath)) {
            if ($this->filesystem->has($gitkeepFilePath) && !$this->filesystem->has($gitkeepTmpFilePath)) {
                $this->filesystem->rename($gitkeepFilePath, $gitkeepTmpFilePath);
            }

            $this->filesystem->deleteDir($publishedThemePath);
        }

        $this->filesystem->createDir($publishedThemePath);

        if (!$this->filesystem->has($gitkeepFilePath) && $this->filesystem->has($gitkeepTmpFilePath)) {
            $this->filesystem->rename($gitkeepTmpFilePath, $gitkeepFilePath);
        }
        
        clearstatcache();
    }
}