<?php
/*--------------------------------------------------------------------------------------------------
    ThemeControl.inc.php 2020-06-05
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

class ThemeControl
{
    /**
     * @var \CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var string
     */
    protected $currentTemplate;
    
    /**
     * @var bool
     */
    protected $themeSystemActive;
    
    /**
     * @var string
     */
    protected $currentTheme;
    
    /**
     * @var string
     */
    protected $themesPath;
    
    /**
     * @var string
     */
    protected $themePath;
    
    /**
     * @var string
     */
    protected $themeHtmlPath;
    
    /**
     * @var string
     */
    protected $themeSmartyPath;
    
    /**
     * @var string
     */
    protected $themeCssPath;
    
    /**
     * @var string
     */
    protected $themeJsPath;
    
    /**
     * @var string
     */
    protected $themeImagePath;
    
    /**
     * @var string
     */
    protected $themeFontsPath;
    
    /**
     * @var string
     */
    protected $themeConfigPath;
    
    /**
     * @var string
     */
    protected $categoryListingTemplatePath;
    
    /**
     * @var string
     */
    protected $filterSectionTemplatePath;
    
    /**
     * @var string
     */
    protected $productInfoTemplatePath;
    
    /**
     * @var string
     */
    protected $productListingTemplatePath;
    
    /**
     * @var string
     */
    protected $productOptionsTemplatePath;
    
    /**
     * @var string
     */
    protected $gmProductOptionsTemplatePath;
    
    /**
     * @var string
     */
    protected $propertiesTemplatePath;
    
    /**
     * @var string
     */
    protected $dynamicCssFilePath;
    
    /**
     * @var string
     */
    protected $templateSettingsFilePath;
    
    /**
     * @var array
     */
    protected $themeSettings;
    
    /**
     * @var float
     */
    protected $themeVersion;
    
    /**
     * @var string
     */
    protected $publishedThemePath;
    /**
     * @var ViewSettings
     */
    protected $viewSettings = null;
    
    
    /**
     * ThemeControl constructor.
     *
     * @param ViewSettings $viewSettings
     */
    public function __construct(ViewSettings $viewSettings)
    {
        $this->viewSettings       = $viewSettings;
        $this->publishedThemePath = 'public/theme';
    }
    
    
    /**
     * Returns the status of the theme system. True, if theme system is active, otherwise false.
     *
     * @return bool
     */
    public function isThemeSystemActive()
    {
        if ($this->themeSystemActive === null) {
            $this->_determineCurrentThemeStatus();
        }
        
        return $this->themeSystemActive;
    }
    
    
    /**
     * Returns the current theme name. If the theme system is not active, the current template name will be returned.
     *
     * @return string
     */
    public function getCurrentTheme()
    {
        if ($this->currentTheme === null) {
            $this->_determineCurrentThemeStatus();
        }
        
        return $this->currentTheme;
    }
    
    
    /**
     * @return string
     */
    public function getCompiledTemplatesFolder(): ?string
    {
        return StyleEditServiceFactory::service()->getCompiledTemplatesFolder() ??
               'cache' . DIRECTORY_SEPARATOR . 'smarty' . DIRECTORY_SEPARATOR;
    }
    
    
    /**
     * Returns the current theme hierarchy. If the theme system is not active, the current template name will be
     * returned as array.
     *
     * @return array
     */
    public function getCurrentThemeHierarchy()
    {
        if (!$this->isThemeSystemActive()) {
            return (array)$this->currentTheme;
        }
        
        // TODO SPRINT #42: HIER SOLLTE SPÄTER EINMAL DER THEME SERVICE VERWENDET WERDEN
        
        $hierarchy   = [];
        $parentTheme = $this->getCurrentTheme();
        
        while ($parentTheme !== null) {
            $hierarchy[]   = $parentTheme;
            $themeJsonFile = DIR_FS_CATALOG . $this->getThemesPath() . $parentTheme . '/theme.json';
            $parentTheme   = null;
            
            if (file_exists($themeJsonFile)) {
                $themeJson = json_decode(file_get_contents($themeJsonFile), true);
                if (isset($themeJson['extends'])) {
                    $parentTheme = $themeJson['extends'];
                }
            }
        }
        
        return $hierarchy;
    }
    
    
    /**
     * Returns the path to the themes, based on the shop root directory.
     *
     * @return string
     */
    public function getThemesPath()
    {
        if ($this->themesPath === null) {
            $this->themesPath = 'templates/';
            if ($this->isThemeSystemActive()) {
                $this->themesPath = 'themes/';
            }
        }
        
        return $this->themesPath;
    }
    
    
    /**
     * Returns the path to the theme, based on the shop root directory.
     *
     * @return string
     */
    public function getThemePath()
    {
        if ($this->themePath === null) {
            if ($this->isThemeSystemActive()) {
                $this->themePath = $this->getPublishedThemePath() . '/';
            } else {
                $this->themePath = 'templates/' . $this->currentTemplate . '/';
            }
        }
        
        return $this->themePath;
    }
    
    
    /**
     * Returns the path to the published theme path.
     *
     * @return string
     */
    public function getPublishedThemePath()
    {
        return StyleEditServiceFactory::service()->getPublishedThemePath() ?? $this->publishedThemePath;
    }
    
    
    /**
     * Returns the path to the theme html directory, based on the shop root directory.
     *
     * @return string
     */
    public function getThemeHtmlPath()
    {
        if ($this->themeHtmlPath === null) {
            if ($this->isThemeSystemActive()) {
                $this->themeHtmlPath = $this->getPublishedThemePath() . '/html/system/';
            } else {
                $this->themeHtmlPath = 'templates/' . $this->currentTemplate . '/';
            }
        }
        
        return $this->themeHtmlPath;
    }
    
    
    /**
     * Returns the path to the theme smarty directory, based on the shop root directory.
     *
     * @return string
     */
    public function getThemeSmartyPath()
    {
        if ($this->themeSmartyPath === null) {
            if ($this->isThemeSystemActive()) {
                $this->themeSmartyPath = $this->getPublishedThemePath() . '/html/smarty/';
            } else {
                $this->themeSmartyPath = 'templates/' . $this->currentTemplate . '/smarty/';
            }
        }
        
        return $this->themeSmartyPath;
    }
    
    
    /**
     * Returns the path to the theme css directory, based on the shop root directory.
     *
     * @return string
     */
    public function getThemeCssPath()
    {
        if ($this->themeCssPath === null) {
            if ($this->isThemeSystemActive()) {
                $this->themeCssPath = $this->getPublishedThemePath() . '/styles/system/';
            } else {
                $this->themeCssPath = 'templates/' . $this->currentTemplate . '/assets/styles/';
            }
        }
        
        return $this->themeCssPath;
    }
    
    
    /**
     * Returns the path to the theme javascript directory, based on the shop root directory.
     *
     * @return string
     */
    public function getThemeJsPath()
    {
        if ($this->themeJsPath === null) {
            if ($this->isThemeSystemActive()) {
                $this->themeJsPath = $this->getPublishedThemePath() . '/javascripts/system/';
            } else {
                $this->themeJsPath = 'templates/' . $this->currentTemplate . '/assets/javascript/';
            }
        }
        
        return $this->themeJsPath;
    }
    
    
    /**
     * Returns the path to the theme image directory, based on the shop root directory.
     *
     * @return string
     */
    public function getThemeImagePath()
    {
        if ($this->themeImagePath === null) {
            if ($this->isThemeSystemActive()) {
                $this->themeImagePath = $this->getPublishedThemePath() . '/images/';
            } else {
                $this->themeImagePath = 'templates/' . $this->currentTemplate . '/assets/images/';
            }
        }
        
        return $this->themeImagePath;
    }
    
    
    /**
     * Returns the path to the theme image directory, based on the shop root directory.
     *
     * @return string
     */
    public function getThemeFontsPath()
    {
        if ($this->themeFontsPath === null) {
            if ($this->isThemeSystemActive()) {
                $this->themeFontsPath = $this->getPublishedThemePath() . '/fonts/';
            } else {
                $this->themeFontsPath = 'templates/' . $this->currentTemplate . '/assets/fonts/';
            }
        }
        
        return $this->themeFontsPath;
    }
    
    
    /**
     * Returns the path to the theme config directory, based on the shop root directory.
     *
     * @return string
     */
    public function getThemeConfigPath()
    {
        if ($this->themeConfigPath === null) {
            if ($this->isThemeSystemActive()) {
                $this->themeConfigPath = $this->getPublishedThemePath() . '/config/';
            } else {
                $this->themeConfigPath = 'templates/' . $this->currentTemplate . '/';
            }
        }
        
        return $this->themeConfigPath;
    }
    
    
    /**
     * Returns the path to the category listing templates directory, based on the shop root directory.
     *
     * @return string
     */
    public function getCategoryListingTemplatePath()
    {
        if ($this->categoryListingTemplatePath === null) {
            if ($this->isThemeSystemActive()) {
                $this->categoryListingTemplatePath = $this->getPublishedThemePath() . '/html/system/';
            } else {
                $this->categoryListingTemplatePath = 'templates/' . $this->currentTemplate
                                                     . '/module/categorie_listing/';
            }
        }
        
        return $this->categoryListingTemplatePath;
    }
    
    
    /**
     * Returns the path to the category listing templates directory, based on the shop root directory.
     *
     * @return string
     */
    public function getFilterSelectionTemplatePath()
    {
        if ($this->filterSectionTemplatePath === null) {
            if ($this->isThemeSystemActive()) {
                $this->filterSectionTemplatePath = $this->getPublishedThemePath() . '/html/system/';
            } else {
                $this->filterSectionTemplatePath = 'templates/' . $this->currentTemplate . '/module/filter_selection/';
            }
        }
        
        return $this->filterSectionTemplatePath;
    }
    
    
    /**
     * Returns the path to the product info templates directory, based on the shop root directory.
     *
     * @return string
     */
    public function getProductInfoTemplatePath()
    {
        if ($this->productInfoTemplatePath === null) {
            if ($this->isThemeSystemActive()) {
                $this->productInfoTemplatePath = $this->getPublishedThemePath() . '/html/system/';
            } else {
                $this->productInfoTemplatePath = 'templates/' . $this->currentTemplate . '/module/product_info/';
            }
        }
        
        return $this->productInfoTemplatePath;
    }
    
    
    /**
     * Returns the path to the product listing templates directory, based on the shop root directory.
     *
     * @return string
     */
    public function getProductListingTemplatePath()
    {
        if ($this->productListingTemplatePath === null) {
            if ($this->isThemeSystemActive()) {
                $this->productListingTemplatePath = $this->getPublishedThemePath() . '/html/system/';
            } else {
                $this->productListingTemplatePath = 'templates/' . $this->currentTemplate . '/module/product_listing/';
            }
        }
        
        return $this->productListingTemplatePath;
    }
    
    
    /**
     * Returns the path to the product options templates directory, based on the shop root directory.
     *
     * @return string
     */
    public function getProductOptionsTemplatePath()
    {
        if ($this->productOptionsTemplatePath === null) {
            if ($this->isThemeSystemActive()) {
                $this->productOptionsTemplatePath = $this->getPublishedThemePath() . '/html/system/';
            } else {
                $this->productOptionsTemplatePath = 'templates/' . $this->currentTemplate . '/module/product_options/';
            }
        }
        
        return $this->productOptionsTemplatePath;
    }
    
    
    /**
     * Returns the path to the gm product option templates directory, based on the shop root directory.
     *
     * @return string
     */
    public function getGmProductOptionsTemplatePath()
    {
        if ($this->gmProductOptionsTemplatePath === null) {
            if ($this->isThemeSystemActive()) {
                $this->gmProductOptionsTemplatePath = $this->getPublishedThemePath() . '/html/system/';
            } else {
                $this->gmProductOptionsTemplatePath = 'templates/' . $this->currentTemplate
                                                      . '/module/gm_product_options/';
            }
        }
        
        return $this->gmProductOptionsTemplatePath;
    }
    
    
    /**
     * Returns the path to the properties templates directory, based on the shop root directory.
     *
     * @return string
     */
    public function getPropertiesTemplatePath()
    {
        if ($this->propertiesTemplatePath === null) {
            if ($this->isThemeSystemActive()) {
                $this->propertiesTemplatePath = $this->getPublishedThemePath() . '/html/system/';
            } else {
                $this->propertiesTemplatePath = 'templates/' . $this->currentTemplate . '/module/properties/';
            }
        }
        
        return $this->propertiesTemplatePath;
    }
    
    
    /**
     * Returns the path to the dynamic css file, based on the shop root directory.
     *
     * @return string
     */
    public function getDynamicCssFilePath()
    {
        if ($this->dynamicCssFilePath === null) {
            if ($this->isThemeSystemActive()) {
                $this->dynamicCssFilePath = 'dynamic_theme_style.css.php';
            } else {
                $this->dynamicCssFilePath = 'templates/' . $this->currentTemplate . '/gm_dynamic.css.php';
            }
        }
        
        return $this->dynamicCssFilePath;
    }
    
    
    /**
     * Returns the path to the template settings file, based on the shop root directory.
     *
     * @return string
     */
    public function getTemplateSettingsFilePath()
    {
        if ($this->templateSettingsFilePath === null) {
            if ($this->isThemeSystemActive()) {
                $this->templateSettingsFilePath = $this->getPublishedThemePath() . '/config/theme_settings.php';
            } else {
                $this->templateSettingsFilePath = 'templates/' . $this->currentTemplate . '/template_settings.php';
            }
        }
        
        return $this->templateSettingsFilePath;
    }
    
    
    /**
     * Returns the path to the template settings file, based on the shop root directory.
     *
     * @return array
     */
    public function getThemeSettings()
    {
        if ($this->themeSettings === null) {
            $this->themeSettings = [];
            $filename            = DIR_FS_CATALOG . $this->getTemplateSettingsFilePath();
            if (file_exists($filename)) {
                $themeSettingsArray = [];
                include $filename;
                
                if (isset($t_template_settings_array)) {
                    $themeSettingsArray = $t_template_settings_array;
                }
                
                $this->themeSettings = $themeSettingsArray;
            }
        }
        
        return $this->themeSettings;
    }
    
    
    /**
     * Returns the path to the template settings file, based on the shop root directory.
     *
     * @return float
     */
    public function getThemeVersion()
    {
        if ($this->themeVersion === null) {
            $this->themeVersion = 1.0;
            if ($this->isThemeSystemActive() && isset($this->getThemeSettings()['THEME_PRESENTATION_VERSION'])) {
                $this->themeVersion = $this->getThemeSettings()['THEME_PRESENTATION_VERSION'];
            } elseif (isset($this->getThemeSettings()['TEMPLATE_PRESENTATION_VERSION'])) {
                $this->themeVersion = $this->getThemeSettings()['TEMPLATE_PRESENTATION_VERSION'];
            }
        }
        
        return $this->themeVersion;
    }
    
    
    /**
     * Determines the current theme name and theme system status.
     */
    protected function _determineCurrentThemeStatus()
    {
        $this->themeSystemActive = StyleEditServiceFactory::service()->isThemeSystemActive()
                                   || $this->viewSettings->isThemeSystemActive();
        $this->currentTheme      = StyleEditServiceFactory::service()->getCurrentTheme() ?? $this->viewSettings->name();
        $this->currentTemplate   = $this->currentTheme;
    }
    
    
    /**
     * @return ViewSettings
     */
    public function getViewSettings(): ViewSettings
    {
        if ($this->viewSettings === null) {
            $dbSettings = ['type' => 'theme', 'name' => 'Honeygrid'];
            
            $themeConfig = $this->db->select('value')
                ->from('gx_configurations')
                ->where('key', 'configuration/CURRENT_THEME')
                ->get()
                ->row_array();
            
            if ($themeConfig !== null && $themeConfig['value'] !== ''
                && is_dir(DIR_FS_CATALOG . 'themes/' . $themeConfig['value'])) {
                $dbSettings['type'] = 'theme';
                $dbSettings['name'] = $themeConfig['value'];
            } else {
                $templateConfig = $this->db->select('value')
                    ->from('gx_configurations')
                    ->where('key', 'configuration/CURRENT_TEMPLATE')
                    ->get()
                    ->row_array();
                if ($templateConfig !== null && $templateConfig['value'] !== ''
                    && is_dir(DIR_FS_CATALOG . 'templates/' . $themeConfig['value'])) {
                    $dbSettings['type'] = 'template';
                    $dbSettings['name'] = $themeConfig['value'];
                }
            }
            
            $this->viewSettings = new ThemeDBSettings($dbSettings['type'], $dbSettings['name']);
        }
        
        return $this->viewSettings;
    }
}
