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

namespace Lightning\Router\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Router\Exception\RouterException;

class DispatcherMiddleware implements MiddlewareInterface
{
    private $callable;
    private ?ResponseInterface $response;

    /**
     * Constructor
     *
     * @param callable $callable
     * @param ResponseInterface|null $response
     */
    public function __construct(callable $callable, ?ResponseInterface $response = null)
    {
        $this->callable = $callable;
        $this->response = $response;
    }

    /**
     * Processes the incoming request
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $callable = $this->callable;
        $arguments = $this->response ? [$request, $this->response] : [$request];

        $response = $callable(...$arguments);
        if (! $response instanceof ResponseInterface) {
            throw new RouterException('No response was returned');
        }

        return $response;
    }
}
