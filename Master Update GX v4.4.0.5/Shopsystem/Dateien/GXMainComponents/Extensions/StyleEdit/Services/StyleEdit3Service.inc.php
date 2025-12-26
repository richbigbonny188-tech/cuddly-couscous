<?php
/* --------------------------------------------------------------
  StyleEdit3Service.inc.php 2019-09-23
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

/**
 * Class StyleEdit3Service
 */
class StyleEdit3Service implements StyleEditServiceInterface
{
    /**
     * @var string
     */
    protected const DEFAULT_TEMPLATE = 'Honeygrid';
    
    
    /**
     * StyleEdit3Service constructor.
     */
    public function __construct()
    {
        $this->includeBootstrap();
    }
    
    
    /**
     * @return string|null
     */
    public function getCacheFilePath(): ?string
    {
        $styleName = $this->getStyleName();
        
        if ($styleName !== null
            && file_exists(SHOP_ROOT . 'StyleEdit3/bootstrap.inc.php')
            && file_exists(SHOP_ROOT . 'public/theme/styles/styleedit')) {
            
            try {
                include_once 'StyleEdit3/bootstrap.inc.php';
                
                if (\StyleEdit\Authentication::isAuthenticated()) {
                    return DIR_FS_CATALOG . 'cache/__dynamics-' . md5($styleName) . '.css';
                }
            } catch (\Exception $exception) {
                //
            }
        }
        
        return null;
    }
    
    
    /**
     *
     */
    private function includeBootstrap(): void
    {
        $filePath = SHOP_ROOT . 'StyleEdit3/bootstrap.inc.php';
        
        if (file_exists($filePath)) {
            
            include_once $filePath;
        }
    }
    
    
    /**
     * @return bool
     */
    public function styleEditStylesExists(): bool
    {
        return file_exists(SHOP_ROOT . 'StyleEdit3/bootstrap.inc.php')
               && file_exists(SHOP_ROOT . 'public/theme//styles/styleedit');
    }
    
    
    /**
     * @param $themeId
     *
     * @return StyleEditReaderInterface
     */
    public function getStyleEditReader($themeId): StyleEditReaderInterface
    {
        return MainFactory::create(StyleEdit3ReaderWrapper::class, $themeId);
    }
    
    
    /**
     * @return bool
     */
    public function styleEditIsInstalled(): bool
    {
        return file_exists(DIR_FS_CATALOG . 'StyleEdit3/bootstrap.inc.php');
    }
    
    
    /**
     * @return string
     */
    public function getCurrentTheme(): ?string
    {
        if (class_exists('\StyleEdit\Authentication') && \StyleEdit\Authentication::isAuthenticated()) {
            return self::DEFAULT_TEMPLATE;
        }
        
        return null;
    }
    
    
    /**
     * @return string
     */
    public function getStyleFileName(): ?string
    {
        $styleName = $this->getStyleName();
        if (\StyleEdit\Authentication::isAuthenticated()) {
            return '__dynamics-' . md5($styleName) . '.css';
        }
        
        return null;
    }
    
    
    /**
     * @return bool - Specific for StyleEdit3 - Always return true on StyleEdit 4
     */
    public function styleEditTemplateExists(): bool
    {
        return file_exists(DIR_FS_CATALOG . 'StyleEdit3/templates/' . StaticGXCoreLoader::getThemeControl()
                               ->getCurrentTheme())
               || file_exists(DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getPublishedThemePath()
                              . '/styles/styleedit');
    }
    
    
    /**
     * @return string
     */
    public function getStyleName(): ?string
    {
        if (class_exists('\StyleEdit\Authentication') && \StyleEdit\Authentication::isAuthenticated()) {
            if (isset($_GET['style_edit_style_name']) && $_GET['style_edit_style_name'] !== '') {
                $_SESSION['style_edit_style_name'] = (string)$_GET['style_edit_style_name'];
            }
    
            return $_SESSION['style_edit_style_name'];
        } elseif (isset($_SESSION['style_edit_style_name'])) {
            unset($_SESSION['style_edit_style_name']);
        }
        
        if (isset($_GET['style_name'])) {
            
            return $_GET['style_name'];
        }
        
        return null;
    }
    
    
    /**
     * @return array
     */
    public function getCacheFiles(): array
    {
        $styleEdit3CacheFilesPattern = \StyleEditConfig::getCacheDirectoryPath() . 'StyleEdit3_*.json';
        
        return glob($styleEdit3CacheFilesPattern);
    }
    
    
    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return \StyleEdit\Authentication::isAuthenticated();
    }
    
    
    /**
     * @return string
     */
    public function getMasterFontVariableName(): string
    {
        return 'gx-font-import-url';
    }
    
    
    /**
     * @return bool
     */
    public function forceCssCacheRenewal(): bool
    {
        return false;
    }
    
    
    /**
     * @return string|null
     */
    public function getPublishedThemePath(): ?string
    {
        return null;
    }
    
    
    /**
     * @return string|null
     */
    public function getCompiledTemplatesFolder(): ?string
    {
        return null;
    }
    
    
    /**
     * @return bool
     */
    public function isThemeSystemActive(): bool
    {
        return false;
    }
    
    
    /**
     * @return bool - Needed to implement the StyleEdit4 Authentication class
     */
    public function isEditing(): bool
    {
        return isset($_SESSION['style_edit_mode']) && $_SESSION['style_edit_mode'] == 'edit';
    }
    
    
    /**
     * @inheritcDoc
     */
    public function isInEditMode(): bool
    {
        return false;
    }
}