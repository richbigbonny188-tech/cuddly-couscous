<?php
/*--------------------------------------------------------------------------------------------------
    ButtonImageController.php 2020-07-17
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */
namespace Gambio\StyleEdit\Core\Components\ButtonImage;


use Gambio\StyleEdit\Core\Components\BasicController;
use Gambio\StyleEdit\Core\Components\ButtonImage\Services\ButtonImageService;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\RequestedThemeInterface;

class ButtonImageController extends BasicController
{
    /**
     * @var ButtonImageService
     */
    protected $buttonImageService;

    /**
     * ButtonImageController constructor.
     *
     * @param ButtonImageService $buttonImageService
     * @param RequestedThemeInterface|null $requestedTheme
     */
    public function __construct(ButtonImageService $buttonImageService, RequestedThemeInterface $requestedTheme = null)
    {
        parent::__construct($requestedTheme);
        $this->buttonImageService = $buttonImageService;
    }

    public function get(array $uri)
    {
        $color = $uri[3];
        $fallback = $uri[4];
        $text = $uri[5];
        $result = ['headers'=>[]];
        $result['headers']['Content-type'] = 'image/png';
        $result['body'] = $this->buttonImageService->getImage($color, $fallback, $text);
        return $result;
    }

    public function put(array $uri, $data)
    {
        // TODO: Implement put() method.
    }

    public function post(array $uri, $data)
    {
        // TODO: Implement post() method.
    }

    public function delete(array $uri, $data)
    {
        // TODO: Implement delete() method.
    }

    public function patch(array $uri, $data)
    {
        // TODO: Implement patch() method.
    }

    public function __clone()
    {
        // TODO: Implement __clone() method.
    }
}