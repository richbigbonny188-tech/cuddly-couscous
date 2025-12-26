<?php
/* --------------------------------------------------------------
  WidgetController.php 2019-05-06
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\StyleEdit\Core\Components\Widget;

use Gambio\StyleEdit\Adapters\Interfaces\WidgetAdapterInterface;
use Gambio\StyleEdit\Core\Components\BasicController;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\RequestedThemeInterface;
use Gambio\StyleEdit\Core\Components\Theme\Entities\ThemeConfiguration;
use Gambio\StyleEdit\Core\Components\Theme\StyleEditThemeService;
use Gambio\StyleEdit\Core\Language\Entities\Language;
use Gambio\StyleEdit\Core\SingletonPrototype;
use Gambio\StyleEdit\Core\Widgets\Abstractions\Interfaces\ContentGeneratorInterface;
use Gambio\StyleEdit\Core\Widgets\Abstractions\Interfaces\StyleEditApiDataProviderInterface;
use Gambio\StyleEdit\Core\Widgets\Exceptions\ContentGeneratorDoesNotImplementStyleEditApiDataProviderInterface;
use Gambio\StyleEdit\Core\Widgets\Exceptions\ContentGeneratorNotFoundException;
use stdClass;

/**
 * Class WidgetController
 */
class WidgetController extends BasicController
{
    /**
     * @var WidgetAdapterInterface
     */
    protected $adapter;

    /**
     * @var WidgetRepository
     */
    private $repository;
    
    /**
     * @var ThemeConfiguration
     */
    private $themeConfiguration;


    /**
     * WidgetController constructor.
     * @param RequestedThemeInterface $requestedTheme
     * @param WidgetRepository $repository
     * @param WidgetAdapterInterface $adapter
     */
    public function __construct(
        RequestedThemeInterface $requestedTheme,
        WidgetRepository $repository,
        WidgetAdapterInterface $adapter
    ) {
        parent::__construct($requestedTheme);
        $this->repository = $repository;

        $this->adapter = $adapter;
    }
    
    
    /**
     * @param array $uri
     *
     * @return mixed
     * @throws ContentGeneratorDoesNotImplementStyleEditApiDataProviderInterface
     * @throws ContentGeneratorNotFoundException
     * @throws \ReflectionException
     */
    public function get(array $uri)
    {
        $this->themeConfiguration = SingletonPrototype::instance()
            ->get(StyleEditThemeService::class)
            ->getConfigurationById($uri[2]);
        
        if (count($uri) === 3) {
            return $this->getWidgetList();
        } elseif (count($uri) === 4) {
    
            $widgetId = ucfirst(end($uri));
            /** @var StyleEditApiDataProviderInterface $generatorName */
            $generatorName = $widgetId . 'Widget';
    
            if (class_exists($generatorName) === false) {
        
                throw new ContentGeneratorNotFoundException($generatorName);
            }
    
            if (!in_array(StyleEditApiDataProviderInterface::class, class_implements($generatorName), true)) {
        
                throw new ContentGeneratorDoesNotImplementStyleEditApiDataProviderInterface($generatorName);
            }
    
            return $this->outputJson($generatorName::apiData());
        }
    }
    
    
    /**
     * @param array $uri
     * @param       $data
     *
     * @return mixed
     */
    public function put(array $uri, $data)
    {
        // TODO: Implement put() method.
    }


    /**
     * @param array $uri
     * @param       $data
     *
     * @return mixed
     * @throws \Exception
     */
    public function post(array $uri, $data)
    {
        if (count($uri) < 4) {
            throw new \RuntimeException('Invalid Request!');
        }
        
        $widgetName = $uri[3];
        $widgetName = str_replace('-', '', ucwords($widgetName, '-')) . 'Widget';
        
        $json = json_decode($data, false);
        
        /** @var ContentGeneratorInterface $widget */
        $widget = $this->adapter->createWidget($widgetName, $json);
        
        $response        = new stdClass;
        $currentLanguage = SingletonPrototype::instance()->get(Language::class);
        $response->html  = $widget->previewContent($currentLanguage);
        return json_encode($response);
    }
    
    
    /**
     * @param array $uri
     * @param       $data
     *
     * @return mixed
     */
    public function delete(array $uri, $data)
    {
        // TODO: Implement delete() method.
    }
    
    
    /**
     * @param array $uri
     * @param       $data
     *
     * @return mixed
     */
    public function patch(array $uri, $data)
    {
        // TODO: Implement patch() method.
    }
    
    
    /**
     * @return BasicController
     */
    public function __clone()
    {
        // TODO: Implement __clone() method.
    }
    
    
    /**
     *
     */
    protected function getWidgetList(): string
    {
        $widgetList = [];
        
        foreach ($this->repository->widgets() as $widget) {
            
            $widgetList[] = $widget->jsonSerialize();
        }
        
        return $this->outputJson($widgetList);
    }
}