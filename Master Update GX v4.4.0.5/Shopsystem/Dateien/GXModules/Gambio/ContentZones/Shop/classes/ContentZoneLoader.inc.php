<?php
/* --------------------------------------------------------------
  ContentZoneLoader.php 2020-11-06
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2020 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

/**
 * Class ContentZoneLoader
 */
class ContentZoneLoader
{
    /**
     * Does string contain smarty function call?
     */
    protected const SMARTY_FUNCTION_PATTERN = '/\{([\w]+)((?:\s[\w]+[\s]*=[\s]*(?:(?:[\w\s]+)|(\{?(.*)\}?)|(?:[\'"%][\w\s,-]*[\'"])))+)\}/';
    
    /**
     * @var null|string
     */
    protected $contentZoneFilename;
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string absolute path /public/theme
     */
    protected $publishedThemePath;
    /**
     * @var Smarty_Internal_Template
     */
    protected $template;
    
    
    /**
     * ContentZoneLoader constructor.
     *
     * @param array                    $params
     * @param Smarty_Internal_Template $template
     *
     */
    public function __construct(array $params, Smarty_Internal_Template $template)
    {
        $this->id                 = $params['id'];
        $this->template           = $template;
        $this->publishedThemePath = DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getPublishedThemePath();
        
        $this->findContentZoneFile();
    }
    
    
    /**
     * Detecting and saving the content zone html
     */
    protected function findContentZoneFile(): void
    {
        $htmlDirectory = $this->publishedThemePath . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'system'
                         . DIRECTORY_SEPARATOR;
        
        $languageHelper = MainFactory::create('LanguageHelper', StaticGXCoreLoader::getDatabaseQueryBuilder());
        $languageCode   = strtolower($languageHelper->getLanguageCodeById(new IdType($_SESSION['languages_id'])));
        $filename       = 'content_zone_' . $this->id . '_' . $languageCode;
        
        if (file_exists($htmlDirectory . $filename . '.html')) {
            $contentZoneFilePath = $htmlDirectory . $filename . '.html';
        } else {
            $contentZoneFilePath = $htmlDirectory . $filename . 'default.html';
        }
        
        if (file_exists($contentZoneFilePath)) {
            $this->contentZoneFilename = $contentZoneFilePath;
        }
    }
    
    
    /**
     * @return string html of the content zone
     */
    public function __toString(): string
    {
        $html = "<div id=\"{$this->id}\" data-gx-content-zone=\"{$this->id}\" class=\"gx-content-zone row\">" . PHP_EOL;
        
        if ($this->contentZoneFilename !== null) {
            
            $htmlContent = file_get_contents($this->contentZoneFilename);
            $matches     = [];
            if (preg_match_all(self::SMARTY_FUNCTION_PATTERN, $htmlContent, $matches) !== 0) {
                
                for ($index = 0, $indexMax = count(current($matches)); $index < $indexMax; $index++) {
                    
                    $completeString   = $matches[0][$index];
                    $smartyFunction   = $matches[1][$index];
                    $parametersString = $matches[2][$index];
                    $functionName     = 'smarty_function_' . $smartyFunction;
                    $pattern          = '/([^,= ]+)=(([^,="\' ]+)|(?:["\']([^\']*)["\']))/';
                    preg_match_all($pattern, $parametersString, $r);
                    $args = array_combine($r[1], $r[2]);
                    array_walk($args,
                        function (&$item1) {
                            $item1 = trim($item1, '"\'');
                        });
                    
                    try {
                        if (!function_exists($functionName)) {
                            $this->template->smarty->loadPlugin($functionName);
                        }
                        
                        if (function_exists($functionName)) {
                            $smartyFunctionContent = $functionName($args, $this->template);
                            $htmlContent           = str_replace($completeString,
                                                                 $smartyFunctionContent,
                                                                 $htmlContent);
                        }
                    } catch (Exception $exception) {
                        //suppress the exception
                    }
                }
            }
            $html .= $htmlContent . PHP_EOL;
        }
        
        return $html . PHP_EOL . '</div>';
    }
}