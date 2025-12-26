<?php
/*--------------------------------------------------------------------------------------------------
    PagesController.php 2020-07-16
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
 -------------------------------------------------------------------------------------------------*/
namespace Gambio\StyleEdit\Core\Components\Pages;

use Gambio\StyleEdit\Adapters\Interfaces\PagesAdapterInterface;
use Gambio\StyleEdit\Core\Components\BasicController;
use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\RequestedThemeInterface;

class PagesController extends BasicController
{
    /**
     * @var PagesAdapterInterface
     */
    private $adapter;
    
    
    /**
     * PagesController constructor.
     *
     * @param PagesAdapterInterface        $adapter
     * @param RequestedThemeInterface|null $requestedTheme
     */
    public function __construct(PagesAdapterInterface $adapter, RequestedThemeInterface $requestedTheme = null)
    {
        parent::__construct($requestedTheme);
        $this->adapter = $adapter;
    }
    
    
    /**
     * @param array $uri
     *
     * @return mixed|void
     */
    public function get(array $uri)
    {
        echo json_encode($this->adapter->getPagesNamespaces());
    }
    
    
    /**
     * @param array $uri
     * @param       $data
     *
     * @return mixed
     */
    public function put(array $uri, $data)
    {
    
    }
    
    
    /**
     * @inheritDoc
     */
    public function post(array $uri, $data)
    {
    
    }
    
    /**
     * @inheritDoc
     */
    public function delete(array $uri, $data)
    {
    
    }
    
    /**
     * @inheritDoc
     */
    public function patch(array $uri, $data)
    {
    
    }
    
    /**
     * @inheritDoc
     */
    public function __clone()
    {
    
    }
}