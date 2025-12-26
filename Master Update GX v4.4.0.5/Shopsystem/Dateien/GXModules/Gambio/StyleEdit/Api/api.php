<?php
/* --------------------------------------------------------------
   api.php 2020-09-29
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

namespace Gambio\StyleEdit\Api;

use Exception;
use Gambio\Core\Logging\Builder\TextAndJsonLoggerBuilder;
use Gambio\StyleEdit\Core\Logger\Formatter\ExceptionFormatter;
use Gambio\StyleEdit\Core\TranslatedException;
use Gambio\StyleEdit\DependencyInjector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Slim\Factory\AppFactory;
use Throwable;

require_once __DIR__ . "/../../../../vendor/autoload.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Expose-Headers: Content-Length, X-JSON');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Theme-Id, HTTP_AUTHORIZATION');
// ----------------------------------------------------------------------------
// INITIALIZE API - SLIM FRAMEWORK
// ----------------------------------------------------------------------------

/**
 * API Version
 *
 * The current API version will be included within every response in the "X-API-Version" header so that
 * clients know which exact version they are using.
 *
 * @var string
 */
try {
    DependencyInjector::inject();
} catch (Exception $e) {
    
    if ($e instanceof \Doctrine\DBAL\Exception\DriverException) {
        throw $e;
    }
}
$devEnvironmentFilePath = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . '.dev-environment';
$environment            = file_exists($devEnvironmentFilePath) ? 'development' : 'production';

switch ($environment) {
    case 'development': // Complete verbose (HTML) output when errors occur.
        $config = [
            'mode'  => 'development',
            'debug' => false
        ];
        break;
    case 'test': // Includes PHP errors in the response body (stack trace).
        $config = [
            'mode'  => 'test',
            'debug' => false
        ];
        break;
    case 'production': // Will display error info in JSON format but hide extra information.
        $config = [
            'mode'  => 'production',
            'debug' => false
        ];
        break;
    default:
        throw new \RuntimeException('Invalid APIv2 environment selected: ' . $environment);
}

$version                                   = '2.6.0';
$config['version']                         = $version;
$config['settings']['displayErrorDetails'] = true;

$app = AppFactory::create();
$app->setBasePath($_SERVER['SCRIPT_NAME']);

// Define routes
(require __DIR__ . '/config/routes.php')($app, $config);

$errorMiddleware = $app->addErrorMiddleware(
    $config['settings']['displayErrorDetails'],
    $config['debug'],
    false
);


$customErrorHandler = function (
    ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app, $environment) {
    $responseErrorCode = 500; // The default value for exceptions on server.

    if ($exception instanceof TranslatedException) {
        $responseErrorCode = $exception->httpStatusCode();
    }

    /**
     * @var UriInterface $uri
     */
    $uri = $request->getUri();

    $payload = [
        'code'    => $exception->getCode(),
        'status'  => 'error',
        'message' => $exception->getMessage(),
        'request' => [
            'method' => $request->getMethod(),
            'url'    => $uri->getHost(),
            'path'   => $uri->getPath(),
            'uri'    => [
                'root'     => $uri->getPath(),
                'resource' => ''
            ]
        ]
    ];

    // Provide error stack only in 'test' mode.
    if ($environment === 'development') {
        $payload['error'] = [
            'file'  => $exception->getFile(),
            'line'  => $exception->getLine(),
            'stack' => $exception->getTrace()
        ];
    }
    
    $logger  = (new TextAndJsonLoggerBuilder('StyleEdit4', false))->build();
    $message = (new ExceptionFormatter)->format($request, $exception);
    $logger->error($message);

    if (defined('JSON_PRETTY_PRINT') && defined('JSON_UNESCAPED_SLASHES')) {
        $responseJsonString = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        $responseJsonString = json_encode($payload); // PHP v5.3
    }

    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()
        ->write($responseJsonString);

    return $response
        ->withStatus($responseErrorCode)
        ->withHeader('Content-Type', 'application/json');
};

$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

$app->run();
