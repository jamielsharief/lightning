<?php

use Lightning\Router\Router;
use App\Controllers\ArticlesController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Http\Exception\NotFoundException;

return function (Router $router) {
    $router->get('/error', function (ServerRequestInterface $request) {
        throw new NotFoundException('Page not found');
    });

    // $router->get('/articles/edit/:id', [ArticlesController::class,'edit']);
    $router->get('/articles/show/:id', function (ServerRequestInterface $request, ResponseInterface $response) {
        $response->getBody()->write(json_encode($request->getAttribute('id')));

        return $response;
    }, ['id' => '[0-9]+']);

    $router->get('/articles/index', [ArticlesController::class,'index']);

    // $router->addMiddleware(new FooMiddleware);
};
