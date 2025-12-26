<?php
/* --------------------------------------------------------------
   StyleEditController.inc.php 2020-08-05
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

namespace Gambio\StyleEdit\Api\Controllers;

use Exception;
use Gambio\StyleEdit\Api\StyleEditAuthenticator;
use Gambio\StyleEdit\Core\Components\BasicController;
use Gambio\StyleEdit\Core\Components\Theme\Entities\BasicTheme;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\RequestedThemeInterface;
use Gambio\StyleEdit\Core\Components\Theme\StyleEditThemeService;
use Gambio\StyleEdit\Core\Language\Entities\Language;
use Gambio\StyleEdit\Core\Language\Services\LanguageService;
use Gambio\StyleEdit\Core\SingletonPrototype;
use Gambio\StyleEdit\Core\TranslatedException;
use Slim\App;
use Slim\Slim;

/**
 * Class StyleEditController
 * @package Gambio\StyleEdit\Api\Controllers
 */
class StyleEditController
{
    public const COMPONENT_URI_INDEX = 1;
    public const THEME_ID_URI_INDEX  = 2;
    /**
     * @var Slim
     */
    protected $api;
    protected $body;
    /**
     * @var BasicController
     */
    protected $componentController;
    /**
     * @var string
     */
    protected $themeId = false;
    /**
     * @var string[]
     */
    protected $uri;
    
    
    /**
     * StyleEditController constructor.
     *
     * @param App        $api
     * @param array      $uri
     * @param            $languageCode
     *
     * @throws TranslatedException
     * @throws Exception
     */
    public function __construct(App $api, array $uri, string $languageCode)
    {

        $this->uri  = $this->cleanUri($uri);
        $this->api  = $api;
        $this->body = file_get_contents('php://input');
        SingletonPrototype::instance()->setUp(Slim::class, $api);
        $this->initializeTheme();
        
        $this->setupLanguage($languageCode);
        
        //Ex: StyleEdit/theme/HoneyGrid
        $controllerName = $this->getComponentControllerClassName($this->uri[self::COMPONENT_URI_INDEX]);
        
        $this->componentController = SingletonPrototype::instance()->get($controllerName);
        if (!$this->componentController) {
            throw new TranslatedException('INVALID_COMPONENT', [$controllerName]);
        }
        
        if ($this->componentController->requiresAuthentication()) {
            SingletonPrototype::instance()->get(StyleEditAuthenticator::class)->authorize();
        }
    }
    
    
    /**
     * @param array $uri
     *
     * @return array
     */
    private function cleanUri(array $uri): array
    {
        while (count($uri) > 0 && (!trim($uri[count($uri) - 1]))) {
            array_pop($uri);
        }
        
        return $uri;
    }
    
    
    /**
     *
     * @throws Exception
     */
    private function initializeTheme(): void
    {
        /**
         * @var StyleEditThemeService $themeService
         */
        $themeService = SingletonPrototype::instance()->get(StyleEditThemeService::class);

        if (array_key_exists(self::THEME_ID_URI_INDEX, $this->uri)) {
            $requestedThemeId = $this->uri[self::THEME_ID_URI_INDEX];

            if ($themeService->exists($requestedThemeId)) {
                $this->themeId = $requestedThemeId;
                SingletonPrototype::instance()->get(StyleEditThemeService::class)->initialize($this->themeId);
            }
            $requestedTheme = new class($requestedThemeId, null) extends BasicTheme implements RequestedThemeInterface{};
            SingletonPrototype::instance()->setUp(RequestedThemeInterface::class, $requestedTheme);
        }
    }
    
    
    /**
     * @param string $languageCode
     *
     * @throws Exception
     */
    protected function setupLanguage(string $languageCode): void
    {
        SingletonPrototype::instance()->setUp(Language::class,
            static function () use ($languageCode) {
                /**
                 * @var LanguageService $languageService
                 */
                $languageService = SingletonPrototype::instance()->get(LanguageService::class);
                return $languageService->getByCode($languageCode);
            });

    }
    
    
    /**
     * @param $name
     *
     * @return string
     */
    protected function getComponentControllerClassName($name): string
    {
        //first - check inner components
        $classname         = str_replace('-', '', ucwords($name, '-'));
        $controllerName    = "{$classname}Controller";
        $internalClassName = "Gambio\\StyleEdit\\Core\\Components\\$classname\\$controllerName";
        if (class_exists($internalClassName)) {
            return $internalClassName;
        }
        
        return $controllerName;
    }
    
    
    /**
     * @return mixed
     * @throws Exception
     */
    public function get()
    {
        return $this->componentController->get($this->uri);
    }
    
    
    /**
     * @return mixed
     */
    public function delete()
    {
        return $this->componentController->delete($this->uri, $this->body);
    }
    
    
    /**
     * @return mixed
     */
    public function patch()
    {
        return $this->componentController->patch($this->uri, $this->body);
    }
    
    
    /**
     * @return mixed
     */
    public function put()
    {
        return $this->componentController->put($this->uri, $this->body);
    }
    
    
    /**
     * @throws Exception
     */
    public function post()
    {
        return $this->componentController->post($this->uri, $this->body);
    }
    
}