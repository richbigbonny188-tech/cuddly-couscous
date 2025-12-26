<?php
/* --------------------------------------------------------------
  StyleEditServiceInterface.inc.php 2019-09-12
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

/**
 * Interface StyleEditServiceInterface
 */
interface StyleEditServiceInterface
{
    /**
     * @return string|null
     */
    public function getCacheFilePath(): ?string;
    
    
    /**
     * @return bool--Checking id StyleEdit is installed _ isStyleEditInstalled
     */
    public function styleEditIsInstalled(): bool;
    
    
    /**
     * @return bool - Check if there is styles available
     */
    public function styleEditStylesExists(): bool;
    
    
    /**
     * @return bool - Specific for StyleEdit3 - Always return true on StyleEdit 4
     */
    public function styleEditTemplateExists(): bool;
    
    
    /**
     * @return bool - Needed to implement the StyleEdit4 Authentication class
     */
    public function isAuthenticated(): bool;
    
    
    /**
     * @return bool - Needed to implement the StyleEdit4 Authentication class
     */
    public function isEditing(): bool;
    
    
    /**
     * @return array - return an empty array
     */
    public function getCacheFiles(): array;
    
    
    /** - Create the class StyleEdit4Reader with the same interface as StyleEdit3Reader (create a wrapper and a factory)
     *
     * @param $themeId
     *
     * @return StyleEditReaderInterface
     */
    public function getStyleEditReader($themeId): StyleEditReaderInterface;
    
    
    /**
     * @return string
     */
    public function getCurrentTheme(): ?string;
    
    
    /**
     * @return string
     */
    public function getStyleFileName(): ?string;
    
    
    /**
     * @return string
     */
    public function getMasterFontVariableName(): string;
    
    
    /**
     * @return bool
     */
    public function forceCssCacheRenewal(): bool;
    
    
    /**
     *
     *
     * @return string|null
     */
    public function getPublishedThemePath(): ?string;
    
    
    /**
     * @param string $default
     *
     * @return string|null
     */
    public function getCompiledTemplatesFolder(): ?string;
    
    
    /**
     * @return bool
     */
    public function isThemeSystemActive(): bool;
    
    
    /**
     * @return bool
     */
    public function isInEditMode(): bool;
}