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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Router\Exception\RouterException;

class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var \Psr\Http\Server\MiddlewareInterface[] $middleware
     */
    private $middleware = [];

    /**
     * Constructor
     *
     * @param array $middleware
     */
    public function __construct(array $middleware = [])
    {
        $this->middleware = $middleware;
    }

    /**
     * Handles a server request and produces a response
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = array_shift($this->middleware);

        if ($middleware) {
            return $middleware->process($request, $this);
        }

        throw new RouterException('No Middleware');
    }
}
