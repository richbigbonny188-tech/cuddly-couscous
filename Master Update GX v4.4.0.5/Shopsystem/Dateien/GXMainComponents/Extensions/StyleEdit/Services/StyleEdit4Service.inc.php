<?php
/*--------------------------------------------------------------------------------------------------
    StyleEdit4Service.inc.php 2020-04-06
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Gambio\StyleEdit\Core\Components\Theme\Entities\PreviewThemeSettings;
use Gambio\StyleEdit\Core\Components\Theme\Repositories\PreviewSettingsRepository;
use Gambio\StyleEdit\StyleEditConfiguration;

/**
 * Class StyleEdit4Service
 */
class StyleEdit4Service implements StyleEditServiceInterface
{
    /**
     * @var string
     */
    protected const DEFAULT_THEME = 'Honeygrid';
    
    
    /**
     * @var StyleEditConfiguration
     */
    protected $settings;
    
    /**
     * @var PreviewThemeSettings
     */
    protected $previewThemeSettings = false;

    /**
     * @var StyleEdit4ReaderWrapper
     */
    protected $reader;


    /**
     * @return string|null
     */
    public function getCacheFilePath(): ?string
    {
        if ($this->previewThemeSettings()) {
            return DIR_FS_CATALOG . 'cache/__dynamics-' . md5($this->previewThemeSettings()->id()) . '.css';
        }
        
        return null;
    }
    
    
    /**
     * @return string
     */
    public function getStyleFileName(): ?string
    {
        if ($this->previewThemeSettings()) {
            return DIR_FS_CATALOG . $this->previewThemeSettings()->id() . '.css';
        }
        
        return null;
    }
    
    
    /**
     * @return PreviewThemeSettings
     */
    public function previewThemeSettings(): ?PreviewThemeSettings
    {
        if ($this->previewThemeSettings === false) {
            $this->previewThemeSettings = null;
            if (isset($_COOKIE['STYLE_EDIT_PREVIEW_THEME'])) {
                try {
                    $repository                 = new PreviewSettingsRepository();
                    $this->previewThemeSettings = $repository->get($_COOKIE['STYLE_EDIT_PREVIEW_THEME']);
                } catch (Exception $e) {
                    setcookie("STYLE_EDIT_PREVIEW_THEME", null, time() - 1, '/');
                }
            }
        }
        
        return $this->previewThemeSettings;
    }
    
    
    /**
     * @return bool
     */
    public function isThemeSystemActive(): bool
    {
        return $this->previewThemeSettings() !== null;
    }
    
    
    /**
     * @return bool
     */
    public function styleEditStylesExists(): bool
    {
        return true;
    }


    /**
     * @param $themeId
     *
     * @return StyleEditReaderInterface
     * @throws Exception
     */
    public function getStyleEditReader($themeId): StyleEditReaderInterface
    {
        if($this->reader === null){
            $this->reader = new StyleEdit4ReaderWrapper($themeId);
        }
        return $this->reader;
    }
    
    
    /**
     * @return bool
     */
    public function styleEditIsInstalled(): bool
    {
        $styleEditGXModulesPath = SHOP_ROOT . 'GXModules/Gambio/StyleEdit';
        
        return is_dir($styleEditGXModulesPath);
    }
    
    
    /**
     * @return bool
     */
    public function styleEditTemplateExists(): bool
    {
        return true;
    }
    
    
    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->previewThemeSettings() !== null;
    }
    
    
    /**
     * @return array
     */
    public function getCacheFiles(): array
    {
        return [];
    }
    
    
    /**
     * @return string
     */
    public function getCurrentTheme(): ?string
    {
        if ($this->previewThemeSettings()) {
            return $this->previewThemeSettings()->id();
        }
        
        return null;
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
        return $this->previewThemeSettings() !== null;
    }
    
    
    /**
     * @return string|null
     */
    public function getPublishedThemePath(): ?string
    {
        if ($this->previewThemeSettings()) {
            return $this->previewThemeSettings()->publishPath();
        }
        
        return null;
    }
    
    
    /**
     * @return string|null
     */
    public function getCompiledTemplatesFolder(): ?string
    {
        if ($this->previewThemeSettings()) {
            return $this->previewThemeSettings()->compilePath();
        }
        
        return null;
    }
    
    
    /**
     * @return StyleEditConfiguration
     */
    protected function settings(): StyleEditConfiguration
    {
        if (!isset($this->settings)) {
            
            $this->settings = new StyleEditConfiguration;
        }
        
        return $this->settings;
    }
    
    
    /**
     * @return bool - Needed to implement the StyleEdit4 Authentication class
     */
    public function isEditing(): bool
    {
        return $this->previewThemeSettings() !== null;
    }
    
    
    /**
     * @inheritcDoc
     */
    public function isInEditMode(): bool
    {
        return isset($_COOKIE['STYLE_EDIT_PREVIEW_THEME']);
    }
}