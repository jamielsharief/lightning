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
use Lightning\Router\Event\AfterDispatchEvent;
use Lightning\Http\Exception\NotFoundException;
use Lightning\Router\Event\BeforeDispatchEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
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
    protected ?EventDispatcherInterface $eventDispatcher;
    protected ?ResponseInterface $emptyResponse ;
    protected ?Autowire $autowire;

    protected RouteCollection $routes;
    protected array $groups = [];

    /**
     * Constructor
     *
     * @param ContainerInterface|null $container
     * @param EventDispatcherInterface|null $eventDispatcher
     * @param Autowire|null $autowire
     * @param ResponseInterface|null $emptyResponse
     */
    public function __construct(
        ?ContainerInterface $container = null, ?EventDispatcherInterface $eventDispatcher = null, ?Autowire $autowire = null, ?ResponseInterface $emptyResponse = null
        ) {
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        $this->emptyResponse = $emptyResponse;
        $this->autowire = $autowire;
        $this->routes = $this->createRouteCollection();
    }

    /**
    * Create a group to organize your routes
    *
    * @param string $path e.g. /admin
    * @param callable $callable
    * @return RouteCollection
        */
    public function group(string $path, callable $callable): RouteCollection
    {
        $path = sprintf('/%s', trim($path, '/'));

        return $this->groups[$path] = $this->createRouteCollection($path, $callable);
    }

    /**
     * Matches a Route
     *
     * @param ServerRequestInterface $request
     * @return Route|null
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
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->eventDispatcher) {
            $event = $this->eventDispatcher->dispatch(new BeforeDispatchEvent($request));
            if ($response = $event->getResponse()) {
                return $response;
            }
            $request = $event->getRequest();
        }

        $route = $this->match($request);

        // Add vars to request
        $variables = $route ? $route->getVariables() : [];
        foreach ($variables as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $response = (new RequestHandler($this->createMiddlewareStack($route, $this->createCallable($route))))->handle($request);

        if ($this->eventDispatcher) {
            $response = $this->eventDispatcher->dispatch(new AfterDispatchEvent($request, $response))->getResponse();
        }

        return $response;
    }

    private function createCallable(?Route $route): callable
    {
        if ($route) {
            return $this->autowire ? $this->createAutowireCallable($route->getCallable()) : $route->getCallable();
        }

        return function (ServerRequestInterface $request) {
            throw new NotFoundException(sprintf('The requested URL %s was not found', $request->getRequestTarget()));
        };
    }

    private function createMiddlewareStack(?Route $route, callable $callable): array
    {
        $middleware = $route ? $route->getMiddlewares() : $this->middlewares; # Important: to add Router main middlewares
        array_push($middleware, new DispatcherMiddleware($callable, $this->emptyResponse));

        return $middleware;
    }

    /**
     * Calls dispatch part of the RequestHandlerInterface
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->dispatch($request);
    }

    /**
     * Creates a callable that can be autowired
     *
     * @param callable $callable
     * @return mixed
     */
    private function createAutowireCallable(callable $callable): mixed
    {
        return function (ServerRequestInterface $request, ?ResponseInterface $response = null) use ($callable) {
            $params = [ServerRequestInterface::class => $request];
            if ($response) {
                $params[ResponseInterface::class] = $response;
            }

            if (is_array($callable)) {
                if ($callable[0] instanceof ControllerInterface && $result = $callable[0]->startup($request)) {
                    return $result;
                }

                $response = $this->autowire->method($callable[0], $callable[1], $params);

                if ($callable[0] instanceof ControllerInterface) {
                    $response = $callable[0]->shutdown($request, $response);
                }

                return $response;
            }

            return $this->autowire->function($callable, $params);
        };
    }

    /**
     * Creates the regular expression and add to routes
     *
     * @param string $method
     * @param string $path
     * @param callable|array|string $handler
     * @param array $constraints
     * @return Route
     */
    public function map(string $method, string $path, $handler, array $constraints = []): Route
    {
        return $this->routes->map($method, $path, $handler, $constraints);
    }

    /**
     * Factory method
     *
     * @param string|null $prefix
     * @param callable|null $callback
     * @return RouteCollection
     */
    private function createRouteCollection(string $prefix = null, callable $callback = null): RouteCollection
    {
        return new RouteCollection($prefix, $callback);
    }
}
