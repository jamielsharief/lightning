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

class RouteCollection implements RoutesInterface
{
    use MiddlewareTrait;
    use RouteTrait;

    /**
    * Collection of Route
    *
    * @var Route[]
    */
    protected array $routes = [];

    protected ?string $prefix = null;

    protected ?object $callback;

    protected string $pattern;

    /**
     * Prefix
     *
     * @param string|null $prefix
     */
    public function __construct(string $prefix = null, callable $callback = null)
    {
        $this->prefix = $prefix;
        $this->callback = $callback;

        $pattern = $this->prefix ? preg_replace('/\//', '\\/', $this->prefix) : '';   // Escape forward slashes for ReGex
        $this->pattern = "/^{$pattern}($|\\/)/"; // '/admin' or /admin/* Removed case insensitive
    }

    /**
     * Checks if the route group is match
     *
     * @param string $uri
     * @return boolean
     */
    public function matchPrefix(string $uri): bool
    {
        return (bool) preg_match($this->pattern, $uri);
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
    public function map(string $method, string $path, $handler, array $constraints): Route
    {
        return $this->routes[] = $this->createRoute(
            $method, sprintf('%s/%s', $this->prefix, trim($path, '/')), $handler, $constraints
        );
    }

    /**
     * Factory method
     *
     * @param string $method
     * @param string $path
     * @param string|callable $handler
     * @param array $constraints
     * @return Route
     */
    private function createRoute(string $method, string $path, $handler, array $constraints = []): Route
    {
        return new Route($method, $path, $handler, $constraints);
    }

    /**
     * Gets the Routes
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        if ($this->callback) {
            $collection = $this->callback;
            $collection($this);
        }

        return $this->routes;
    }
}
