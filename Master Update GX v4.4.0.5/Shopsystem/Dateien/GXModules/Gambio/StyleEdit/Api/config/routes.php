<?php

use Gambio\StyleEdit\Api\Controllers\StyleEditController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

return function (App $app) {

    $app->map(
        ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        '/styleedit[/{uri:.*}]',
        function (
            ServerRequestInterface $request,
            ResponseInterface $response,
            array $args
        ) use ($app) {
            $uri = explode("/", $args['uri']);
            //first parameter must always be the language
            $language = array_shift($uri);
            array_unshift($uri, "styleedit");

            $controller = new StyleEditController($app, $uri, $language);
            $method = strtolower($request->getMethod());


            $payload = "";
            if ($method !== 'options') {
                $payload = $controller->$method();
            }
            if(is_array($payload) && isset($payload['headers'], $payload['body']) && is_array($payload['headers'])) {
                $response->getBody()->write($payload['body']);
                foreach ($payload['headers'] as $name=>$value){
                    $response = $response->withHeader($name,$value);
                }
                return $response;
            } else {
                $response->getBody()
                    ->write($payload ?? '');

                return $response
                    ->withHeader('Content-Type', 'application/json');
            }


        }
    );
};