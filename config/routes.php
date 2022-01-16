<?php

use Lightning\Router\Router;
use Lightning\Router\RoutesInterface;
use App\Controllers\ArticlesController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Http\Exception\NotFoundException;

return function (RoutesInterface $routes) {
    $routes->get('/error', function (ServerRequestInterface $request) {
        throw new NotFoundException('Page not found');
    });

    // $routes->get('/articles/edit/:id', [ArticlesController::class,'edit']);
    $routes->get('/articles/show/:id', function (ServerRequestInterface $request, ResponseInterface $response) {
        $response->getBody()->write(json_encode($request->getAttribute('id')));

        return $response;
    }, ['id' => Router::NUMERIC]);

    $routes->get('/articles/index', [ArticlesController::class,'index']);

    // $routes->addMiddleware(new FooMiddleware);
};
