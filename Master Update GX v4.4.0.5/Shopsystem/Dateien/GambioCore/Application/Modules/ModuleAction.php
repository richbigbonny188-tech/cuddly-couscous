<?php
/* --------------------------------------------------------------
 ModuleAction.php 2020-09-11
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2020 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

declare(strict_types=1);

namespace Gambio\Core\Application\Modules;

use Gambio\Core\Application\Modules\App\Http\Request as HttpRequest;
use Gambio\Core\Application\Modules\App\Http\Response as HttpResponse;
use Gambio\Core\Application\Modules\Http\Request;
use Gambio\Core\Application\Modules\Http\Response;
use Gambio\Core\Application\ValueObjects\Url;
use Gambio\Core\TemplateEngine\Exceptions\RenderingFailedException;
use Slim\Http\Response as SlimResponse;
use Slim\Http\ServerRequest as SlimRequest;

/**
 * Class ModuleAction
 * @package Gambio\Core\Framework\Module
 */
abstract class ModuleAction
{
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var Response
     */
    protected $response;
    
    /**
     * @var array
     */
    protected $args;
    
    /**
     * @var Url
     */
    protected $url;
    
    
    /**
     * Module action initialization.
     *
     * @param Url $url
     */
    public function initModuleAction(Url $url): void
    {
        $this->url = $url;
    }
    
    
    /**
     * Handles the action callback.
     *
     * This method creates the response that is sent back to the client.
     *
     * @return Response
     * @throws RenderingFailedException
     */
    abstract protected function handle(): Response;
    
    
    /**
     * This method will be called from the slim framework in order to process
     * the incoming request.
     *
     * @param SlimRequest  $request
     * @param SlimResponse $response
     * @param              $args
     *
     * @return SlimResponse
     * @throws RenderingFailedException
     */
    public function __invoke(SlimRequest $request, SlimResponse $response, $args): SlimResponse
    {
        $this->request  = new HttpRequest($request);
        $this->response = new HttpResponse($response);
        $this->args     = $args;
        $httpResponse   = $this->handle();
        
        return $httpResponse->toSlimResponse();
    }
}