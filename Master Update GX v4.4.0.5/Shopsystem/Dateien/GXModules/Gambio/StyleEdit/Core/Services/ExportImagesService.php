<?php
/* --------------------------------------------------------------
  ExportImagesService.php 2020-12-21
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Services;

use Exception;
use FileNotFoundException;
use FilesystemAdapter;
use Gambio\StyleEdit\Configurations\ShopBaseUrl;
use Gambio\StyleEdit\Core\SingletonPrototype;
use stdClass;

/**
 * Class ExportImagesService
 * @package Gambio\StyleEdit\Core\Services
 */
class ExportImagesService
{
    /**
     * @var FilesystemAdapter
     */
    protected $filesystem;
    
    /**
     * @var ShopBaseUrl
     */
    protected $shopBaseUrl;
    
    /**
     * @var string[]
     */
    protected $changedFiles = [];
    
    
    /**
     * ExportImagesService constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->filesystem  = SingletonPrototype::instance()->get('FilesystemAdapterShopRoot');
        $this->shopBaseUrl = SingletonPrototype::instance()->get(ShopBaseUrl::class);
    }
    
    
    /**
     * @param string $themeId
     *
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function moveImageDependenciesToThemeDirectory(string $themeId): void
    {
        $this->updateUrlsInSettingsJson($themeId);
        $this->updateUrlsInContentZoneJson($themeId);
        
        if (count($this->changedFiles)) {
            
            foreach ($this->changedFiles as $oldPath => $newPath) {

                $source      = $this->webPathToLocalPath($oldPath, $themeId);
                $destination = $this->webPathToLocalPath($newPath, $themeId);

                //  image is already inside the theme and does not need to be copied
                if (strpos($oldPath, 'public/theme') === 0 || $this->filesystem->has($source) === false) {
                    
                    continue;
                }
                
                if ($this->filesystem->has($destination) === false) {
                    
                    $this->filesystem->copy($source, $destination);
                }
            }
        }
    }
    
    
    /**
     * @param string $themeId
     *
     * @throws FileNotFoundException
     * @throws Exception
     */
    protected function updateUrlsInSettingsJson(string $themeId): void
    {
        $settings = $this->settingsJsonForTheme($themeId);
        
        if (is_array($settings) && count($settings)) {
            
            foreach ($settings as &$setting) {
                
                if ($setting->type === 'url' && $this->urlIsLocal($setting->value)) {
                    
                    $setting->value = $this->urlForTheme($setting->value, $themeId);
                }
            }
            unset($setting);
            
            if (count($this->changedFiles)) {
                
                $this->updateSettingsJson($settings, $themeId);
            }
        }
    }
    
    
    /**
     * @param string $themeId
     *
     * @throws FileNotFoundException
     * @throws Exception
     */
    protected function updateUrlsInContentZoneJson(string $themeId): void
    {
        $contentZoneJsons = $this->getContentZoneJsonPaths($themeId);
        
        if (!count($contentZoneJsons)) {
            
            return;
        }
        
        foreach ($contentZoneJsons as $path) {
            
            $jsonStr    = $this->filesystem->read($path);
            $jsonObject = json_decode($jsonStr, false);
            
            foreach ($jsonObject->rows as &$row) {
                
                $this->makePathsInColumnsLayoutRelative($row->columnsLayout);
                
                foreach ($row->cols as &$col) {
                    
                    if (count($col->widgets) !== 0) {
                        
                        foreach ($col->widgets as &$widget) {
                            
                            foreach ($widget->fieldsets as &$fieldset) {
                                
                                $this->updateUrlsInFieldset($fieldset, $themeId);
                            }
                        }
                    }
                }
            }
            unset($row, $col, $widget, $fieldset);
            
            $this->updateContentZoneJson($jsonObject, $path);
        }
    }
    
    
    /**
     * @param stdClass $columnsLayout
     */
    protected function makePathsInColumnsLayoutRelative(stdClass $columnsLayout): void
    {
        if (count($columnsLayout->options)) {
            
            foreach ($columnsLayout->options as &$option) {
                
                $option->thumbnail = str_replace($this->shopBaseUrl->value(), '../../../../', $option->thumbnail);
            }
        }
    }
    
    
    /**
     * @param string $themeId
     *
     * @return string[]
     */
    protected function getContentZoneJsonPaths(string $themeId): array
    {
        $result          = [];
        $contentZonePath = 'themes' . DIRECTORY_SEPARATOR . $themeId . DIRECTORY_SEPARATOR . 'contentzones';
        
        $jsonFiles = $this->filesystem->listContents($contentZonePath);
        
        if (count($jsonFiles)) {
            
            foreach ($jsonFiles as $file) {
                
                if ($file['extension'] === 'json') {
                    
                    $result[] = $file['path'];
                }
            }
        }
        
        return $result;
    }
    
    
    /**
     * @param stdClass $fieldset
     *
     * @param string   $themeId
     *
     * @throws Exception
     */
    protected function updateUrlsInFieldset(stdClass $fieldset, string $themeId): void
    {
        foreach ($fieldset->options as &$option) {
            
            if (in_array($option->type, ['imageupload', 'url'])) {
                
                if (isset($option->default)) {
                    
                    $this->updateUrlsInOptionValue($option->default, $themeId);
                }
                
                if (isset($option->value)) {
                    
                    $this->updateUrlsInOptionValue($option->value, $themeId);
                }
            }
        }
        unset($option);
    }
    
    
    /**
     * @param stdClass $optionValue
     *
     * @param string   $themeId
     *
     * @throws Exception
     */
    protected function updateUrlsInOptionValue(stdClass $optionValue, string $themeId): void
    {
        foreach ($optionValue as &$value) {
            
            if (0 !== strpos($value, "http") && 0 !== strpos($value, "//")) {
                
                $value = $this->urlForWeb($value, $themeId);
            }
        }
        unset($value);
    }
    
    
    /**
     * @param string $path
     *
     * @param string $themeId
     *
     * @return string
     */
    protected function webPathToLocalPath(string $path, string $themeId): string
    {
        $themesDirectory = $this->shopBaseUrl->value() . 'themes' . DIRECTORY_SEPARATOR;
        $result          = str_replace($themesDirectory . $this->realThemeId($themeId),
                                       $themesDirectory . $themeId,
                                       $path);
        $result          = str_replace($this->shopBaseUrl->value(), '', $result);
        $result          = preg_replace('#^/#', '', $result);
        
        return $result;
    }
    
    
    /**
     * @param string $themeId
     *
     * @return stdClass[]
     * @throws FileNotFoundException
     */
    protected function settingsJsonForTheme(string $themeId): array
    {
        $settingsJsonPath = 'themes' . DIRECTORY_SEPARATOR . $themeId . DIRECTORY_SEPARATOR;
        $settingsDefaultJson = $settingsJsonPath . 'settings.default.json';
        $settingsJson = $settingsJsonPath . 'settings.json';

        $settingsJsonDefaultExists = $this->filesystem->has($settingsDefaultJson);
        $settingsJsonExists = $this->filesystem->has($settingsJson);

        if(!$settingsJsonExists && !$settingsJsonDefaultExists) {
            throw new FileNotFoundException();
        }

        if($settingsJsonExists) {
            $settingJsonStr = $this->filesystem->read($settingsJson);
        } else {
            $settingJsonStr = $this->filesystem->read($settingsDefaultJson);
        }

        return json_decode($settingJsonStr);
    }
    
    
    /**
     * @param string $url
     * @param string $themeId
     *
     * @return string
     *
     * @throws Exception
     */
    protected function urlForTheme(string $url, string $themeId): string
    {
        $fileName          = basename($url);
        $fileNameIncrement = 1;
        $newPath           = $this->shopBaseUrl->value() . 'themes' . DIRECTORY_SEPARATOR . $this->realThemeId($themeId)
                             . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $fileName;
        
        while (in_array($newPath, $this->changedFiles)) {
            
            $newPath = $this->shopBaseUrl->value() . 'themes' . DIRECTORY_SEPARATOR . $this->realThemeId($themeId)
                       . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $fileNameIncrement++ . $fileName;
        }
        
        if (!array_key_exists($url, $this->changedFiles)) {
            
            $this->changedFiles[$url] = $newPath;
        }
        
        return str_replace($this->shopBaseUrl->value(), '__SHOP_BASE_URL__', $this->changedFiles[$url]);
    }
    
    
    /**
     * @param string $url
     * @param string $themeId
     *
     * @return string
     * @throws Exception
     */
    protected function urlForWeb(string $url, string $themeId): string
    {
        $this->urlForTheme($url, $themeId); #adds path for the theme to the array
        
        $publicThemeUrl = str_replace('themes/' . $this->realThemeId($themeId),
                                      'public/theme',
                                      $this->changedFiles[$url]);
        
        return str_replace($this->shopBaseUrl->value(), '__SHOP_BASE_URL__', $publicThemeUrl);
    }
    
    
    /**
     * @param string $themeId
     *
     * @return string
     */
    protected function realThemeId(string $themeId): string
    {
        return preg_replace('#_export$#', '', $themeId);
    }
    
    
    /**
     * @param string $url
     *
     * @return bool
     * @throws Exception
     */
    protected function urlIsLocal(?string $url): bool
    {
        if (in_array($url, [null, ''], true)) {
            
            return false;
        }
        
        $regex = '#^' . preg_quote($this->shopBaseUrl->value(), '#') . '#';
        
        return preg_match($regex, $url) === 1;
    }
    
    
    /**
     * @param stdClass $contentZone
     * @param string   $path
     *
     * @throws FileNotFoundException
     */
    protected function updateContentZoneJson(stdClass $contentZone, string $path): void
    {
        if ($this->filesystem->has($path)) {
            
            $this->filesystem->update($path, json_encode($contentZone, JSON_PRETTY_PRINT));
        }
    }
    
    
    /**
     * @param array  $settings
     * @param string $themeId
     *
     * @throws FileNotFoundException
     */
    protected function updateSettingsJson(array $settings, string $themeId): void
    {
        $settingsJsonPath = 'themes' . DIRECTORY_SEPARATOR . $themeId . DIRECTORY_SEPARATOR . 'settings.json';
        
        if ($this->filesystem->has($settingsJsonPath)) {
            
            $this->filesystem->update($settingsJsonPath, json_encode($settings, JSON_PRETTY_PRINT));
        }
    }
    
    
    /**
     * @return string[]
     */
    public function changedFiles(): array
    {
        return $this->changedFiles;
    }
    
    
    /**
     * @param string[] $changedFiles
     * @param string   $themeId
     *
     * @throws Exception
     */
    public function addChangedFiles(array $changedFiles, string $themeId): void
    {
        foreach ($changedFiles as $file) {
            
            $this->urlForTheme($file, $themeId);
        }
    }
}