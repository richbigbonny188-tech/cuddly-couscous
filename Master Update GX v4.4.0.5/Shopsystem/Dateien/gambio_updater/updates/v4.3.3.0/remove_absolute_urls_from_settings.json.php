<?php
/*--------------------------------------------------------------
   remove_absolute_urls_from_settings.json.php 2021-01-07
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------*/

declare(strict_types=1);

$rootDirectory      = dirname(__DIR__, 3);
$themeDirectoryPath = $rootDirectory . '/themes/';
$themeDirectories   = array_filter(glob($themeDirectoryPath . '*'),
    static function (string $path) {
        
        return is_dir($path) && preg_match('#_preview$#', $path) === 0;
    });

if (count($themeDirectories) !== 0) {
    
    /**
     * Since the the user could have switched domains after creating
     * the settings.json the HTTP_SERVER constants are not reliable.
     *
     * This function searches for the relative path
     *
     * @param string $url
     *
     * @return string|null string if file exist and null if it does not exist locally
     */
    $relativePathFinder = static function (string $url) use ($rootDirectory): ?string {
        
        $parts = explode('/', $url);
        
        while (array_shift($parts) !== null && count($parts) !== 0) {
            
            $concatenated = implode('/', $parts);
            
            if (file_exists($rootDirectory . '/' . $concatenated)) {
                
                return $concatenated;
            }
        }
        
        return null;
    };
    
    /**
     * @param stdClass ...$options
     *
     * @return bool was any option changed?
     */
    $modifySettingsCollection = static function (stdClass ...$options) use ($relativePathFinder): bool {
        
        $optionsWhereChanged = false;
        $urlOptions          = array_filter($options,
            static function (stdClass $option) {
            
                $typeIsUrl              = $option->type === 'url';
                $valueIsNoneEmptyString = is_string($option->value) && $option->value !== '';
            
                return $typeIsUrl && $valueIsNoneEmptyString
                       && preg_match('#^https?://#', $option->value) !== 0;
            });
    
        foreach ($urlOptions as $option) {
    
            $relativePath = $relativePathFinder($option->value);
        
            if ($relativePath !== null) {
            
                $option->value       = $relativePath;
                $optionsWhereChanged = true;
            }
        }
        
        return $optionsWhereChanged;
    };
    
    foreach ($themeDirectories as $themeDirectory) {
        
        //  replacing absolute urls in the main settings.json
        $hasSettingsJson = file_exists($themeDirectory . '/settings.json');
        
        if ($hasSettingsJson) {
    
            $settingsJsonPath    = $themeDirectory . '/settings.json';
            $settingsJsonContent = file_get_contents($settingsJsonPath);
            $settingsJson        = json_decode($settingsJsonContent, false);
    
            if (is_array($settingsJson) && count($settingsJson) !== 0) {
        
                $settingsJsonChanged = $modifySettingsCollection(...$settingsJson);
        
                if ($settingsJsonChanged) {
            
                    $settingsJsonContent = json_encode($settingsJson, JSON_PRETTY_PRINT);
                    file_put_contents($settingsJsonPath, $settingsJsonContent);
                }
            }
        }
        
        //  replacing absolute urls in variant settings.json's
        $variantJsonPaths = glob($themeDirectory . '/variants/*/*/settings.json');
        
        if (count($variantJsonPaths) !== 0) {

            foreach ($variantJsonPaths as $variantJsonPath) {

                $variantJsonContent = file_get_contents($variantJsonPath);
                $variantJson        = json_decode($variantJsonContent, false);
                
                if (is_array($variantJson) && count($variantJson) !== 0) {
                    
                    $variantJsonChanged = $modifySettingsCollection(...$variantJson);
                    
                    if ($variantJsonChanged) {

                        $variantJsonContent = json_encode($variantJson, JSON_PRETTY_PRINT);
                        file_put_contents($variantJsonPath, $variantJsonContent);
                    }
                }
            }
        }
    
        //  replacing absolute urls in theme extensions
        $themeExtensions = glob($themeDirectory . '/theme_extensions/*.json');
        
        if (count($themeExtensions) !== 0) {
    
            foreach ($themeExtensions as $themeExtensionPath) {
    
                $themeExtensionContent = file_get_contents($themeExtensionPath);
                $themeExtension        = json_decode($themeExtensionContent, false);
    
                if ($themeExtension->extension_type === 'MERGE'
                    && isset($themeExtension->default, $themeExtension->default->image, $themeExtension->default->image->url)
                    && $themeExtension->default->image->url !== ''
                    && strpos($themeExtension->default->image->url, "http") === 0) {
    
                    $relativePath = $relativePathFinder($themeExtension->default->image->url);
                    
                    if ($relativePath !== null) {
    
                        $themeExtension->default->image->url = $relativePath;
                        $themeExtensionContent = json_encode($themeExtension);
                        
                        file_put_contents($themeExtensionPath, $themeExtensionContent);
                    }
                }
            }
        }
        
        $contentZoneJsons = glob($themeDirectory . '/contentzones/*json');
        $contentZoneJsons = array_filter($contentZoneJsons, static function(string $path): bool {
            return preg_match('#default\.json$#', $path) === 0;
        });
        
        if (count($contentZoneJsons) !== 0) {
    
            foreach ($contentZoneJsons as $contentZoneJsonPath) {
    
                $contentZoneJsonString = file_get_contents($contentZoneJsonPath);
                $contentZoneJson       = json_decode($contentZoneJsonString, false);
    
                if (is_array($contentZoneJson->rows) && count($contentZoneJson->rows)) {
    
                    foreach ($contentZoneJson->rows as $row) {
        
                        if (is_string($row->background->items->image->url)) {
            
                            $relativePath = $relativePathFinder($row->background->items->image->url);
            
                            if ($relativePath !== null) {
                
                                $row->background->items->image->url = $relativePath;
                            }
                        }
    
                        if (is_string($row->background->default->image->url)) {
        
                            $relativePath = $relativePathFinder($row->background->default->image->url);
        
                            if ($relativePath !== null) {
            
                                $row->background->default->image->url = $relativePath;
                            }
                        }
        
                        if (is_array($row->cols) && count($row->cols)) {
            
                            foreach ($row->cols as $col) {
                
                                if (is_string($col->background->items->image->url)) {
                    
                                    $relativePath = $relativePathFinder($col->background->items->image->url);
                    
                                    if ($relativePath !== null) {
                        
                                        $col->background->items->image->url = $relativePath;
                                    }
                                }
    
                                if (is_string($col->background->default->image->url)) {
        
                                    $relativePath = $relativePathFinder($col->background->default->image->url);
        
                                    if ($relativePath !== null) {
            
                                        $col->background->default->image->url = $relativePath;
                                    }
                                }
                            }
                        }
                    }
                    
                    file_put_contents($contentZoneJsonPath, json_encode($contentZoneJson, JSON_PRETTY_PRINT));
                }
            }
        }
    }
}