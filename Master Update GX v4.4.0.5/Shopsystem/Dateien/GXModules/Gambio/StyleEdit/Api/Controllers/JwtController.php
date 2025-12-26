<?php
/* --------------------------------------------------------------
  JwtController.php 2020-05-06
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

namespace Gambio\StyleEdit\Api\Controllers;

use Exception;
use Gambio\StyleEdit\Adapters\Interfaces\JwtAdapterInterface;
use Gambio\StyleEdit\Core\Components\BasicController;
use Gambio\StyleEdit\Core\TranslatedException;
use Slim\App;
use stdClass;

/**
 * Class JwtController
 */
class JwtController extends BasicController
{
    /**
     * @var \Slim\Slim
     */
    protected $app;

    /**
     * @var string
     */
    protected $env;
    /**
     * @var JwtAdapterInterface
     */
    protected $jwtAdapter;

    /**
     * BasicController constructor.
     *
     * @param App $app
     * @param JwtAdapterInterface $jwtAdapter
     */
    public function __construct(App $app, JwtAdapterInterface $jwtAdapter)
    {
        parent::__construct();
        $this->app = $app;

        $devEnvironmentFilePath = dirname(__DIR__, 5) . DIRECTORY_SEPARATOR . '.dev-environment';
        $this->env = file_exists($devEnvironmentFilePath) ? 'development' : 'production';
        $this->jwtAdapter = $jwtAdapter;
    }

    /**
     * @return BasicController
     */
    public function __clone()
    {
        // TODO: Implement __clone() method.
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
     *
     * @return mixed
     * @throws Exception
     */
    public function get(array $uri)
    {
        if ($this->env === 'development') {

            $result = new stdClass;
            $result->token = $this->jwtAdapter->getCurrentUserToken();
            return $this->outputJson($result);
        }

        throw new TranslatedException('FORBIDDEN', [], 403);
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
     * @param array $uri
     * @param       $data
     *
     * @return mixed
     */
    public function post(array $uri, $data)
    {
        // TODO: Implement post() method.
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
     * @return bool
     */
    public function requiresAuthentication(): bool
    {
        return false;
    }
}