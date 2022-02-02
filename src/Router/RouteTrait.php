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

trait RouteTrait
{
    /**
    * Creates a GET route
    *
    * @param string $path
    * @param callable|array|string $handler
    * @param array $constraints
    * @return Route
    */
    public function get(string $path, $handler, array $constraints = []): Route
    {
        return $this->map('GET', $path, $handler, $constraints);
    }
    /**
     * Creates a POST route
     *
     * @param string $path
     * @param callable|array|string $handler
     * @param array $constraints
     * @return Route
     */
    public function post(string $path, $handler, array $constraints = []): Route
    {
        return $this->map('POST', $path, $handler, $constraints);
    }

    /**
     * Creates a PATCH route
     *
     * @param string $path
     * @param callable|array|string $handler
     * @param array $constraints
     * @return Route
     */
    public function patch(string $path, $handler, array $constraints = []): Route
    {
        return $this->map('PATCH', $path, $handler, $constraints);
    }

    /**
     * Creates a PUT route
     *
     * @param string $path
     * @param callable|array $handler
     * @param array $constraints
     * @return Route
     */
    public function put(string $path, $handler, array $constraints = []): Route
    {
        return $this->map('PUT', $path, $handler, $constraints);
    }

    /**
     * Creates a DELETE route
     *
     * @param string $path
     * @param callable|array|string $handler
     * @param array $constraints
     * @return Route
     */
    public function delete(string $path, $handler, array $constraints = []): Route
    {
        return $this->map('DELETE', $path, $handler, $constraints);
    }

    /**
     * Creates a HEAD route
     *
     * @param string $path
     * @param callable|array|string $handler
     * @param array $constraints
     * @return Route
     */
    public function head(string $path, $handler, array $constraints = []): Route
    {
        return $this->map('HEAD', $path, $handler, $constraints);
    }

    /**
     * Creates a OPTIONS route
     *
     * @param string $path
     * @param callable|array|string $handler
     * @param array $constraints
     * @return Route
     */
    public function options(string $path, $handler, array $constraints = []): Route
    {
        return $this->map('OPTIONS', $path, $handler, $constraints);
    }
}
