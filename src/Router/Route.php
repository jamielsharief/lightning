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

use BadMethodCallException;
use Psr\Container\ContainerInterface;
use Lightning\Router\Exception\RouterException;

class Route
{
    use MiddlewareTrait;

    protected string $method;
    protected string $path;
    protected string $pattern;
    protected array $constraints = [];
    protected array $variables = [];
    protected ?string $uri = null;

    private $handler;

    /**
     * Constructor
     *
     * @param string $method
     * @param string $path
     * @param callable|array|string $handler
     * @param array $constraints
     */
    public function __construct(string $method, string $path, callable|array|string $handler, array $constraints = [])
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
        $this->constraints = $constraints;

        $pattern = preg_replace('/\//', '\\/', $this->path);   // Escape forward slashes for ReGex
        $pattern = preg_replace('/\:([a-z]+)/i', '(?P<\1>[^\.\/]+)', $pattern);  // Convert vars e.g. :id :name
        $this->pattern = "/^{$pattern}$/";
    }

    /**
     * Matches a route and params to this object
     *
     * @param string $method
     * @param string $uri
     * @return boolean
     */
    public function match(string $method, string $uri): bool
    {
        if ($this->method !== $method) {
            return false;
        }

        $matches = $variables = [];

        if (! preg_match($this->pattern, $uri, $matches)) {
            return false;
        }

        $this->uri = $uri;

        // extract params
        foreach ($matches as $key => $match) {
            if (is_string($key)) {
                $variables[$key] = ctype_digit($match) ? (int) $match : $match;
            }
        }

        // process constriants
        foreach ($this->constraints as $attribute => $pattern) {
            if (isset($variables[$attribute]) && ! preg_match('#^' . $pattern . '$#', (string) $variables[$attribute])) {
                return false;
            };
        }

        $this->variables = $variables;

        return true;
    }

    /**
     * Gets the handler for this Route, if it is using a proxy it will be resolved.
     *
     * @internal this was in the Middleware, then moved to router, and now is here. Still not sure
     *
     *
     * @return callable|string callable or proxy
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Gets the Method for this Route
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Gets the Path for this Route
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Gets the matched URI
     *
     * @return string|null
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }

    /**
     * Get the Route vars
     *
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Gets the Constraints
     *
     * @return array
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * Gets the callable for this route (must be invoked first)
     *
     * @return callable
     */
    public function getCallable(): callable
    {
        if (! is_callable($this->handler)) {
            throw new BadMethodCallException('Route must be invoked first');
        }

        return $this->handler;
    }

    /**
     * Invokes the handler for the route
     *
     * @param ContainerInterface|null $container
     * @return callable
     */
    public function __invoke(?ContainerInterface $container = null): callable
    {
        $handler = $this->handler;

        // convert 'App\Http\Articles\Controller::index' proxy to [App\Http\Articles\Controller::class,'index'];
        if (is_string($handler) && strpos($handler, '::') !== false) {
            $handler = explode('::', $handler);
        }

        // Convert  [App\Http\Articles\Controller::class,'index'] to [object,'index']
        if (is_array($handler) && is_string($handler[0])) {
            $handler = [$this->resolve($handler[0], $container), $handler[1]];
        } elseif (is_string($handler)) {
            $handler = $this->resolve($handler);
        }

        if (is_callable($handler)) {
            return $this->handler = $handler;
        }

        throw new RouterException(
            sprintf('The handler for `%s %s` is not a callable', $this->method, $this->path)
        );
    }

    /**
     * Resolves the class, if it is not in the container then the container will be passed.
     *
     * @param string $class
     * @return object
     */
    private function resolve(string $class, ?ContainerInterface $container = null)
    {
        if ($container && $container->has($class)) {
            return $container->get($class);
        }

        if (! class_exists($class)) {
            throw new RouterException(sprintf('Error resolving `%s`', $class), 404);
        }

        return new $class();
    }
}
