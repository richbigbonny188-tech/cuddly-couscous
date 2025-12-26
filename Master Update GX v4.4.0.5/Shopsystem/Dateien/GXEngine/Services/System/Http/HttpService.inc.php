<?php
/* --------------------------------------------------------------
   HttpService.inc.php 2021-02-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2021 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

MainFactory::load_class('HttpServiceInterface');

/**
 * Class HttpService
 *
 * @category   System
 * @package    Http
 * @implements HttpServiceInterface
 */
class HttpService implements HttpServiceInterface
{
    /**
     * @var AbstractHttpContextFactory
     */
    protected $httpContextFactory;
    
    /**
     * @var HttpDispatcherInterface
     */
    protected $httpDispatcher;
    
    
    /**
     * Initializes the http service.
     *
     * @param AbstractHttpContextFactory $httpContextFactory Factory instance to create context objects.
     * @param HttpDispatcherInterface    $httpDispatcher     Instance to dispatch the http response message.
     */
    public function __construct(AbstractHttpContextFactory $httpContextFactory, HttpDispatcherInterface $httpDispatcher)
    {
        $this->httpContextFactory = $httpContextFactory;
        $this->httpDispatcher     = $httpDispatcher;
    }
    
    
    /**
     * Creates and returns a new instance of an http context object.
     *
     * @return HttpContextInterface
     */
    public function getHttpContext()
    {
        return $this->httpContextFactory->create();
    }
    
    
    /**
     * Handles the current http request by the given context.
     *
     * @param HttpContextInterface $httpContext Context object which holds information about the current request.
     */
    public function handle(HttpContextInterface $httpContext)
    {
        try {
            $this->httpDispatcher->dispatch($httpContext);
        } catch (HttpControllerException $e) {
            if (!headers_sent()) {
                Gambio\GX\Application::send404HttpResponse();
            }
        }
    }
}