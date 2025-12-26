<?php
/*--------------------------------------------------------------------------------------------------
    ThemeTranslator.php 2019-10-28
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2019 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */
namespace Gambio\StyleEdit\Core\Components\Theme\Entities\Translators;


use Exception;
use Gambio\StyleEdit\Core\Language\Services\LanguageService;
use stdClass;

/**
 * Class LanguageTranslator
 * @package Gambio\StyleEdit\Core\Components\Theme\Entities\Translators
 */
class LanguageTranslator
{
    /**
     * @var LanguageService
     */
    protected $languageService;
    
    
    /**
     * ThemeTranslator constructor.
     *
     * @param LanguageService $languageService
     */
    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }
    
    
    /**
     * Language Specific Content
     *
     * @param stdClass $themeConfig
     *
     * @throws Exception
     */
    public function translateContent(stdClass $themeConfig): void
    {
        if (isset($themeConfig->config, $themeConfig->config->basics)) {
            $this->translateCategories($themeConfig->config->basics);
        }
        if (isset($themeConfig->config, $themeConfig->config->areas)) {
            $this->translateCategories($themeConfig->config->areas);
        }
    }
    
    
    /**
     * Recursive function for the language specific content
     *
     * @param stdClass|array $content
     *
     * @throws Exception
     */
    protected function translateCategories($content): void
    {
        if (isset($content->categories)) {
            foreach ($content->categories as &$category) {
                if (isset($category->title) && $category->title !== null) {
                    $category->title = $this->languageService->translate($category->title);
                }
                
                if (isset($category->categories) || isset($category->fieldsets)) {
                    $this->translateCategories($category);
                }
            }
            
            unset($category);
        } elseif (isset($content->fieldsets)) {
            foreach ($content->fieldsets as &$fieldset) {
                if (isset($fieldset->title) && $fieldset->title !== null) {
                    $fieldset->title = $this->languageService->translate($fieldset->title);
                }
                if (isset($fieldset->options)) {
                    $this->translateCategories($fieldset);
                }
            }
            
            unset($fieldset);
        } elseif (isset($content->options)) {
            foreach ($content->options as &$option) {
                if (isset($option->title) && $option->title !== null) {
                    $option->title = $this->languageService->translate($option->title);
                }
                
                if (isset($option->default->thumbnail) && $option->default->thumbnail !== null) {
                    $option->default->thumbnail = $this->languageService->translate($option->default->thumbnail);
                }
                
                if (isset($option->thumbnail) && $option->thumbnail !== null) {
                    $option->thumbnail = $this->languageService->translate($option->thumbnail);
                }
                
                if (isset($option->label) && $option->label !== null) {
                    $option->label = $this->languageService->translate($option->label);
                }
                
                if (isset($option->options)) {
                    $this->translateCategories($option);
                }
                
                if (isset($option->default) && is_array($option->default)) {
                    foreach ($option->default as &$default) {
                        $default->label = $this->languageService->translate($default->label);
                    }
                    unset($default);
                }
            }
            unset($option);
        }
    }
    
    
}