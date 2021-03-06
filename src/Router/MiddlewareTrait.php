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

use Psr\Http\Server\MiddlewareInterface;

trait MiddlewareTrait
{
    /**
     * @var \Psr\Http\Server\MiddlewareInterface[] $middlewares
     */
    protected array $middlewares = [];

    /**
     * Adds middleware to queue
     *
     * @param MiddlewareInterface $middleware
     * @return static
     */
    public function middleware(MiddlewareInterface $middleware): static
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Adds middleware to the start of queue
     *
     * @param MiddlewareInterface $middleware
     * @return static
     */
    public function prependMiddleware(MiddlewareInterface $middleware): static
    {
        array_unshift($this->middlewares, $middleware);

        return $this;
    }

    /**
     * @return \Psr\Http\Server\MiddlewareInterface[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
