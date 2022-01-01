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
     * @return self
     */
    public function middleware(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Adds middleware to the start of queue
     *
     * @param MiddlewareInterface $middleware
     * @return self
     */
    public function prependMiddleware(MiddlewareInterface $middleware): self
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
