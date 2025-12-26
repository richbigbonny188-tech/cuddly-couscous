<?php
/*--------------------------------------------------------------------------------------------------
    dynamic_theme_style.css.php 2020-05-06
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/*
 * DYNAMIC THEME STYLE
 *
 * This script will handle the creation and delivery of the requested theme CSS.
 *
 * OPTIONAL GET PARAMETERS
 *
 * - style_name: the currently active StyleEdit3 style name.
 * - renew_cache: enforces the style cache renewal.
 *
 */

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_CORE_ERROR & ~E_CORE_WARNING);
@date_default_timezone_set('Europe/Berlin');
require_once __DIR__ . '/GXMainComponents/ApplicationCss.inc.php';
$application = new Gambio\GX\ApplicationCss();
$application->run();


// Setup style required constants.
define('CACHE_FILENAME', '__dynamics.css');
define('DIR_PUBLISHED_THEME_PATH', StyleEditServiceFactory::service()->getPublishedThemePath() ?? 'public/theme');
define('DIR_FS_PUBLISHED_THEME_PATH', DIR_FS_CATALOG . DIR_PUBLISHED_THEME_PATH);

// Setup optional parameter aliases.
$styleName  = $_GET['style_name'] ?? StyleEditServiceFactory::service()->getStyleFileName();
$renewCache = $_GET['renew_cache'] ?? StyleEditServiceFactory::service()->forceCssCacheRenewal();
// Fetch theme name from theme configuration file.
$themeConfigurationFilePath = DIR_FS_PUBLISHED_THEME_PATH . '/theme.json';
$themeConfiguration         = json_decode(file_get_contents($themeConfigurationFilePath), true);
$themeId                    = $themeConfiguration['id'];
$suffix                     = file_exists(DIR_FS_CATALOG . '.dev-environment') ? '' : '.min';
$additionalCssFiles         = [];
$additionalScssFiles        = [];
$additionalScssPaths        = [];

appendExistingFile($additionalCssFiles, DIR_FS_PUBLISHED_THEME_PATH . '/styles/system/stylesheet.css');
appendExistingFile($additionalCssFiles, DIR_FS_PUBLISHED_THEME_PATH . '/styles/system/vendor' . $suffix . '.css');
appendExistingFile($additionalCssFiles, DIR_FS_CATALOG . 'JSEngine/build/vendor' . $suffix . '.css');
appendExistingFile($additionalCssFiles, DIR_FS_PUBLISHED_THEME_PATH . '/styles/system/vendor_fixes.css');
$themeFiles = GXModulesCache::getInstalledThemeFiles();
$gxModulesFiles = GXModulesCache::getInstalledModuleFiles();
$themes = array_reverse(StaticGXCoreLoader::getThemeControl()->getCurrentThemeHierarchy());
$themes[] = 'all';
foreach ($themes as $currentTheme) {
    $lowerCaseCurrentTheme = strtolower(trim($currentTheme));
    if(isset($themeFiles[$lowerCaseCurrentTheme]))
    {
        foreach ($themeFiles[$lowerCaseCurrentTheme] as $gxGroup) {
            foreach ($gxGroup['css'] as $file) {
            
                if (substr($file, -4) === '.css') {
                    $additionalCssFiles[] = $file;
                } elseif (substr($file, -9) === 'main.scss') {
                    $additionalScssFiles[] = $file;
                    $additionalScssPaths[] = substr(substr($file, 0, strrpos($file, '/')), strlen(DIR_FS_CATALOG));
                }
            }
        }
    }
}

$additionalScssFiles = array_unique($additionalScssFiles);
$additionalScssPaths = array_unique($additionalScssPaths);
$cacheFilePath       = StyleEditServiceFactory::service()->getCacheFilePath() ??
                       DIR_FS_CATALOG . 'cache/' . CACHE_FILENAME;

$createCache = false;

if ($renewCache !== null || !file_exists($cacheFilePath) || filesize($cacheFilePath) < 10) {
    $createCache = true;
}

if ($createCache === false) {
    ob_start();
    
    foreach ($additionalCssFiles as $additionalCssFile) {
        include $additionalCssFile;
        
        // Add comment to close unclosed comment in included file.
        echo "\n/**/\n";
    }
    
    $additionalCss = ob_get_clean();
    
    if ($suffix === '.min') {
        $filters = [
            'ImportImports'                 => false,
            'RemoveComments'                => true,
            'RemoveEmptyRulesets'           => true,
            'RemoveEmptyAtBlocks'           => true,
            'ConvertLevel3AtKeyframes'      => false,
            'ConvertLevel3Properties'       => false,
            'Variables'                     => true,
            'RemoveLastDelarationSemiColon' => true
        ];
        
        $plugins = [
            'Variables'                => true,
            'ConvertFontWeight'        => true,
            'ConvertHslColors'         => true,
            'ConvertRgbColors'         => true,
            'ConvertNamedColors'       => true,
            'CompressColorValues'      => true,
            'CompressUnitValues'       => true,
            'CompressExpressionValues' => true
        ];
        
        $additionalCss = minifyCss($additionalCss, $filters, $plugins);
    }
    
    echo file_get_contents($cacheFilePath) . PHP_EOL . $additionalCss;
} else {
    $css = '';
    
    $fileToServe = 'main.scss';
    $basePath    = DIR_PUBLISHED_THEME_PATH . '/styles/system/';  // Default theme directory.
    $pathPattern = DIR_FS_CATALOG . 'cache/*.css*';
    $cachePaths  = glob($pathPattern);
    
    if (is_array($cachePaths)) {
        foreach ($cachePaths as $result) {
            if (strpos($result, '__dynamic') === false) {
                unlink($result);
            }
        }
    }
    
    if (file_exists($cacheFilePath)) {
        @unlink($cacheFilePath);
    }
    
    $errorOccurred         = false;
    $errorMessage          = '';
    $styleEditErrorMessage = '';
    
    if (StyleEditServiceFactory::service()->styleEditStylesExists()) {
        try {
            $_REQUEST['theme'] = 'true'; // Toggle theme mode for StyleEdit.
            $styleConfigReader = StyleEditServiceFactory::service()->getStyleEditReader($themeId);
            $variableAliases   = getFileContent(DIR_FS_PUBLISHED_THEME_PATH. '/styles/system/_variable-aliases.scss');
            $variableAliases  .= "\n";
            $bootstrapScss     = $styleConfigReader->getScss('bootstrap');
            $templateScss      = $styleConfigReader->getScss('template') . $variableAliases;
            $customStylesScss  = $styleConfigReader->getCustomStyles();

            // Download google fonts and use the local fonts
            $fontVariableName = StyleEditServiceFactory::service()->getMasterFontVariableName();
            $fontUrl          = $styleConfigReader->findSettingValueByName($fontVariableName);
            if (strpos($fontUrl, '"') === 0 || strpos($fontUrl, '\'') === 0) {
                $fontUrl = substr($fontUrl, 1, strlen($fontUrl) - 2);
            }
    
            $fontDownloader   = new GoogleFontDownloader;
            $fontManager      = new GoogleFontManager($fontDownloader, $fontUrl);
            $localFontContent = $fontManager->getFontCss();
            
            if (strlen($localFontContent) !== 0) {
                
                $templateScss .= "\n\n" . '$gx-font-import-url: "";';
                $templateScss .= "\n" . $localFontContent;
            }
            
            if ($styleConfigReader->getErrorMessage() !== '') {
                $styleEditErrorMessage = 'body:before {
                    content: "' . str_replace('"', '\"', $styleConfigReader->getErrorMessage()) . '";
                    position: absolute;
                    background: white;
                    top: 0;
                    left: 0;
                    z-index: 100000;
                }';
            }
            
            $customDirectoryPath = DIR_FS_PUBLISHED_THEME_PATH . '/styles/system/custom/';
            
            if (file_exists($customDirectoryPath . '_usermod.scss')) {
                $customStylesScss .= "\n\n@import \"custom/usermod\";";
            }
            
            if (@file_put_contents($customDirectoryPath . '_bootstrap_variables.scss', $bootstrapScss) === false) {
                $errorOccurred = true;
            }
            
            if (@file_put_contents($customDirectoryPath . '_template_variables.scss', $templateScss) === false) {
                $errorOccurred = true;
            }
            
            if (@file_put_contents($customDirectoryPath . '_custom_styles.scss', $customStylesScss) === false) {
                $errorOccurred = true;
            }
            
            if ($errorOccurred) {
                $errorMessage = 'body:before {
                                    content: "The directory ' . $customDirectoryPath . ' and/or containing files are not writable. CSS cache file cannot be created causing slow page speed.";
                                    position: absolute;
                                    background: white;
                                    top: 0;
                                    left: 0;
                                    z-index: 100000;
                                }';
                
                $css .= $errorMessage;
            }
        } catch (Exception $e) {
            $errorOccurred = true;
            $errorMessage  = '
		        body:before {
					content: "' . str_replace('"', '\"', $e->getMessage()) . '";
					position: absolute;
					background: white;
					top: 0;
					left: 0;
					z-index: 100000;
				}
			';
            
            $css .= $errorMessage;
        }
    } else {
        $customDirectoryPath = DIR_FS_PUBLISHED_THEME_PATH . '/styles/custom/';
        
        if (!file_exists($customDirectoryPath . '_bootstrap_variables.scss')) {
            @file_put_contents($customDirectoryPath . '_bootstrap_variables.scss', '');
        }
        
        if (!file_exists($customDirectoryPath . '_template_variables.scss')) {
            @file_put_contents($customDirectoryPath . '_template_variables.scss', '');
        }
        
        $customStylesScss = getFileContent($customDirectoryPath . '_custom_styles.scss');
        
        if (file_exists($customDirectoryPath . '_usermod.scss')) {
            if (strpos($customStylesScss, '@import "custom/usermod";') === false) {
                $customStylesScss .= "\n\n@import \"custom/usermod\";";
                
                @file_put_contents($customDirectoryPath . '_custom_styles.scss', $customStylesScss);
            }
        } elseif (strpos($customStylesScss, '@import "custom/usermod";') !== false) {
            $customStylesScss = str_replace('@import "custom/usermod";', '', $customStylesScss);
            
            @file_put_contents($customDirectoryPath . '_custom_styles.scss', $customStylesScss);
        }
    }
    
    $compiler = ScssCompilerFactory::create()->createCompiler();
    
    // Serve CSS
    $compiler->setVariables([
                                'theme-base-path' => '"' . DIR_WS_CATALOG . DIR_PUBLISHED_THEME_PATH . '/"'
                            ]);
    
    $compiler->addImportPath($basePath);
    $compiler->setBasePath(DIR_FS_CATALOG . $basePath);
    
    if (count($additionalScssPaths) > 0) {
        foreach ($additionalScssPaths as $additionalScssPath) {
            $compiler->addImportPath($additionalScssPath);
        }
    }
    
    if ($suffix === '.min') {
        $compiler->setFormatter(ScssCompilerInterface::STYLE_COMPRESSED);
    } else {
        $compiler->setFormatter(ScssCompilerInterface::STYLE_NESTED);
    }
    
    $compiler->setAdditionalScssFiles($additionalScssFiles);
    
    ob_start();
    try {
        $compiler->serve($fileToServe);
    } catch (Exception $exception) {
        $errorMessage = '
			body:before {
				content: "' . str_replace('"', '\"', $exception->getMessage()) . '";
				position: absolute;
				background: white;
				top: 0;
				left: 0;
				z-index: 100000;
			}
		';
        
        echo $errorMessage . $styleEditErrorMessage;
        
        echo getFileContent(DIR_FS_CATALOG . 'cache/' . CACHE_FILENAME);
        $errorOccurred = true;
    }
    
    $scss = ob_get_clean();
    
    if (strpos($scss, 'Parse error') === 0) {
        $errorMessage = 'body:before {
							content: "' . str_replace('"', '\"', str_replace("\n", ' ', str_replace("\r", '', $scss))) . '";
							position: absolute;
							background: white;
							top: 0;
							left: 0;
							z-index: 100000;
						}';
        
        $scss = $errorMessage . $styleEditErrorMessage;
        
        $scss          .= getFileContent(DIR_FS_CATALOG . 'cache/' . CACHE_FILENAME);
        $errorOccurred = true;
    }
    
    // Compile default css in error case.
    if ($errorOccurred) {
        ob_start();
        
        try {
            $compiler = ScssCompilerFactory::create()->createCompiler();
            $compiler->setVariables([
                                        'theme-base-path' => '"' . DIR_WS_CATALOG . DIR_PUBLISHED_THEME_PATH . '/"'
                                    ]);
            $compiler->addImportPath($basePath);
            $compiler->setBasePath(DIR_FS_CATALOG . $basePath);
            if (count($additionalScssPaths) > 0) {
                foreach ($additionalScssPaths as $additionalScssPath) {
                    $compiler->addImportPath($additionalScssPath);
                }
            }
            
            if ($suffix === '.min') {
                $compiler->setFormatter(ScssCompilerInterface::STYLE_COMPRESSED);
            } else {
                $compiler->setFormatter(ScssCompilerInterface::STYLE_EXPANDED);
            }
            
            $compiler->setAdditionalScssFiles($additionalScssFiles);
            
            // delete custom scss forcing to compile default css to ensure a working frontend
            @file_put_contents($customDirectoryPath . '_bootstrap_variables.scss', '');
            @file_put_contents($customDirectoryPath . '_template_variables.scss', '');
            @file_put_contents($customDirectoryPath . '_custom_styles.scss', '');
            
            $compiler->serve($fileToServe);
            
            $scss = ob_get_clean();
            $scss = $errorMessage . "\n" . $styleEditErrorMessage . "\n" . $scss;
        } catch (Exception $e) {
            ob_clean();
        }
    }
    
    $scss = str_replace('\"', '"', $scss);
    $css  .= $scss;
    
    if (!$errorOccurred && !$application->errorOccurred()) {
        if ($suffix === '.min') {
            $css = preg_replace('!/\*.*?\*/!s', '', $css);
        }
        
        file_put_contents($cacheFilePath, $css);
    }
    
    header('HTTP/1.1 200 OK');
    header('Last-Modified: ' . gmdate('r'), true);
    header('ETag: ', true);
    header('Cache-Control: no-cache, must-revalidate', true);
    
    ob_start();
    
    foreach ($additionalCssFiles as $additionalCssFile) {
        include $additionalCssFile;
        
        // print comment to close unclosed comment in included file
        echo "\n/**/\n";
    }
    
    $additionalCss = ob_get_clean();
    
    if ($suffix === '.min') {
        $filters = [
            'ImportImports'                 => false,
            'RemoveComments'                => true,
            'RemoveEmptyRulesets'           => true,
            'RemoveEmptyAtBlocks'           => true,
            'ConvertLevel3AtKeyframes'      => false,
            'ConvertLevel3Properties'       => false,
            'Variables'                     => true,
            'RemoveLastDelarationSemiColon' => true
        ];
        
        $plugins = [
            'Variables'                => true,
            'ConvertFontWeight'        => true,
            'ConvertHslColors'         => true,
            'ConvertRgbColors'         => true,
            'ConvertNamedColors'       => true,
            'CompressColorValues'      => true,
            'CompressUnitValues'       => true,
            'CompressExpressionValues' => true
        ];
        
        $additionalCss = minifyCss($additionalCss, $filters, $plugins);
    }
    
    $destinationDirectoryPath = DIR_FS_PUBLISHED_THEME_PATH . '/styles/system';
    
    if ($styleName === null && is_writable($destinationDirectoryPath)) {
        $mainCssFilePath = $destinationDirectoryPath . '/main' . $suffix . '.css';
        
        if (file_exists($mainCssFilePath)) {
            unlink($mainCssFilePath);
        }
        
        file_put_contents($mainCssFilePath, $css . PHP_EOL . $additionalCss);

        $fileFlag = DIR_FS_CATALOG . 'cache/update_shop_offline_page_css.flag';

        if (!file_exists($fileFlag)) {
            touch($fileFlag);
        }
    }
    
    echo $css . PHP_EOL . $additionalCss;
}

/**
 * @param $array
 * @param $filePath
 */
function appendExistingFile(&$array, $filePath)
{
    if (file_exists($filePath)) {
        $array[] = $filePath;
    }
}

function getFileContent($filename)
{
    if (file_exists($filename)) {
        return file_get_contents($filename);
    } else {
        return '';
    }
}

function minifyCss($css, array $filters, array $plugins)
{
    $cssMinifier = new CssMinifier($css, $filters, $plugins);
    
    return $cssMinifier->getMinified();
}

