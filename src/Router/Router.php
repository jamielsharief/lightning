<?php declare(strict_types=1);
/**
 * LightningPHP
 * Copyright 2021 - 2022 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Lightning\Router;

use Lightning\Autowire\Autowire;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Http\Exception\NotFoundException;
use Lightning\Router\Middleware\DispatcherMiddleware;

/**
 * Router
 *
 * @internal
 *  - HTTP Methods are typically uppercase, but are case senstive, so these should not be modified.
 *    @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec5.html#sec5.1.1
 */
class Router implements RequestHandlerInterface, RoutesInterface
{
    use RouteTrait;
    use MiddlewareTrait;

    public const ALPHA = '[a-fA-F]+';
    public const ALPHANUMERIC = '\w+';
    public const HEX = '[a-fA-F0-9]+';
    public const NUMERIC = '[0-9]+';

    protected ?ContainerInterface $container;
    protected ?ResponseInterface $emptyResponse ;
    protected ?Autowire $autowire;

    protected RouteCollection $routes;
    protected array $groups = [];

    /**
     * Constructor
     */
    public function __construct(
        ?ContainerInterface $container = null,
        ?Autowire $autowire = null,
        ?ResponseInterface $emptyResponse = null
        ) {
        $this->container = $container;
        $this->emptyResponse = $emptyResponse;
        $this->autowire = $autowire;
        $this->routes = $this->createRouteCollection();
    }

    /**
    * Create a group to organize your routes
    *
    * @param string $path e.g. /admin
        */
    public function group(string $path, callable $callable): RouteCollection
    {
        $path = sprintf('/%s', trim($path, '/'));

        return $this->groups[$path] = $this->createRouteCollection($path, $callable);
    }

    /**
     * Matches a Route
     */
    public function match(ServerRequestInterface $request): ?Route
    {
        $method = $request->getMethod();
        $path = urldecode($request->getUri()->getPath());

        $routes = $this->routes->getRoutes();
        $middlewares = $this->middlewares; // First add middlewares to router

        foreach ($this->groups as $routeGroup) {
            if ($routeGroup->matchPrefix($path)) {
                $routes = $routeGroup->getRoutes();
                array_push($middlewares, ...$routeGroup->getMiddlewares()); // Now add group based middleware

                break;
            }
        }

        foreach ($routes as $route) {
            if ($route->match($method, $path)) {
                asort($middlewares); // sort so they end up in the same order

                foreach ($middlewares as $middleware) {
                    $route->prependMiddleware($middleware); // Insert so route specific middleware are last
                }

                $route($this->container);

                return $route;
            }
        }

        return null;
    }

    /**
     * Dispatches a ServerRequest
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $route = $this->match($request);

        // Add vars to request
        $variables = $route ? $route->getVariables() : [];
        foreach ($variables as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        if ($route) {
            $callable = $route->getCallable();
        } else {
            $callable = function (ServerRequestInterface $request) {
                throw new NotFoundException(sprintf('The requested URL %s was not found', $request->getRequestTarget()));
            };
        }

        $response = (new RequestHandler($this->createMiddlewareStack($route, $callable)))->handle($request);

        return $response;
    }

    private function createMiddlewareStack(?Route $route, callable $callable): array
    {
        $middleware = $route ? $route->getMiddlewares() : $this->middlewares; # Important: to add Router main middlewares
        array_push($middleware, new DispatcherMiddleware($callable, $this->emptyResponse, $this->autowire));

        return $middleware;
    }

    /**
     * Calls dispatch part of the RequestHandlerInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->dispatch($request);
    }

    /**
     * Creates the regular expression and add to routes
     */
    public function map(string $method, string $path, callable|array|string $handler, array $constraints = []): Route
    {
        return $this->routes->map($method, $path, $handler, $constraints);
    }

    /**
     * Factory method
     */
    private function createRouteCollection(string $prefix = null, callable $callback = null): RouteCollection
    {
        return new RouteCollection($prefix, $callback);
    }
}
