<?php
/* --------------------------------------------------------------
   ApiErrorHandler.php 2020-05-04
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Application;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\ServerRequest;
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;
use function Gambio\Core\Logging\logger;

/**
 * Class ApiErrorHandler
 *
 * @package Gambio\Core\HTTP
 */
class ApiErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;
    
    /**
     * ApiErrorHandler constructor.
     *
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }
    
    
    /**
     * @param ServerRequestInterface|ServerRequest $request
     * @param Throwable                            $exception
     * @param bool                                 $displayErrorDetails
     * @param bool                                 $logErrors
     * @param bool                                 $logErrorDetails
     *
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        if ($exception instanceof HttpNotFoundException) {
            $content = [
                'error'   => 'Could not find requested resource.',
                'details' => $displayErrorDetails ? [
                    'target' => $request->getRequestTarget(),
                    'method' => $request->getMethod(),
                ] : '',
            ];
            
            return $this->responseFactory->createResponse(404)->withJson($content);
        }
        
        $content = [
            'error'   => 'An unexpected error occurred.',
            'details' => $displayErrorDetails ? [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTrace(),
            ] : '',
        ];
        
        logger('api', true)->error(
            'An unexpected error occurred while handling an incoming request.',
            ['error' => $exception]
        );
        
        return $this->responseFactory->createResponse(500)->withJson($content);
    }
}