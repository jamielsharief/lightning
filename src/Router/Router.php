<?php declare(strict_types=1);
/**
 * LightningPHP
 * Copyright 2021 Jamiel Sharief.
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
use Psr\Http\Message\ResponseFactoryInterface;
use Lightning\Http\Exception\NotFoundException;
use Lightning\Router\Event\BeforeDispatchEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Lightning\Router\Middleware\DispatcherMiddleware;

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
    protected ?ResponseFactoryInterface $responseFactory;
    protected ?Autowire $autowire;

    protected RouteCollection $routes;
    protected array $groups = [];

    /**
     * Constructor
     *
     * @param ContainerInterface|null $container
     * @param EventDispatcherInterface|null $eventDispatcher
     * @param ResponseFactoryInterface|null $responseFactory
     * @param Autowire|null $autowire
     */
    public function __construct(
        ?ContainerInterface $container = null, ?EventDispatcherInterface $eventDispatcher = null, ?ResponseFactoryInterface $responseFactory = null, ?Autowire $autowire = null
        ) {
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        $this->responseFactory = $responseFactory;
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
        $middlewares = $this->middlewares;

        foreach ($this->groups as $routeGroup) {
            if ($routeGroup->matchPrefix($path)) {
                $routes = $routeGroup->getRoutes();
                $middlewares = array_merge($middlewares, $routeGroup->getMiddlewares());

                break;
            }
        }

        arsort($middlewares);

        foreach ($routes as $route) {
            if ($route->match($method, $path)) {
                foreach ($middlewares as $middleware) {
                    $route->prependMiddleware($middleware);
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
            $event = new BeforeDispatchEvent($request);
            $this->eventDispatcher->dispatch($event);
            if ($response = $event->getResponse()) {
                return $response;
            }
        }

        $route = $this->match($request);
        $middleware = $route ? $route->getMiddlewares() : $this->middlewares; # Important: to add Router main middlewares
        $variables = $route ? $route->getVariables() : [];

        if ($route) {
            $callable = $this->autowire ? $this->createAutowireCallable($route->getCallable()) : $route->getCallable();
        } else {
            $message = 'The requested URL %s was not found';
            $callable = function (ServerRequestInterface $request) use ($message) {
                throw new NotFoundException(sprintf($message, $request->getRequestTarget()));
            };
        }

        array_push($middleware, new DispatcherMiddleware($callable, $variables, $this->responseFactory));

        $response = (new RequestHandler($middleware))->handle($request);

        if ($this->eventDispatcher) {
            $response = $this->eventDispatcher->dispatch(new AfterDispatchEvent($request, $response))->getResponse();
        }

        return $response;
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
    private function createAutowireCallable(callable $callable)
    {
        return function (ServerRequestInterface $request, ?ResponseInterface $response = null) use ($callable) {
            $params = [ServerRequestInterface::class => $request];

            if ($response) {
                $params[ResponseInterface::class] = $response;
            }

            if (is_array($callable)) {
                // decided to this way rather than events or hooks
                if (method_exists($callable[0], 'setRequest')) {
                    $callable[0]->setRequest($request);
                }

                return $this->autowire->method($callable[0], $callable[1], $params);
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
