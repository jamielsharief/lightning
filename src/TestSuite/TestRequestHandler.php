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

namespace Lightning\TestSuite;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Test Request Handler
 *
 * A request handler for testing middleware
 * @example
 *
 *  $middleware = new FooMiddleware();
 *  $response = $middleware->process(new ServerRequest('GET', '/'), new TestRequestHandler(new Response()));
 */
class TestRequestHandler implements RequestHandlerInterface
{
    private ResponseInterface $response;
    private ?ServerRequestInterface $request = null;
    private MiddlewareInterface $middleware;

    /**
     * @var callback
     */
    private $callback = null;

    /**
     * Constructor
     *
     * @param MiddlewareInterface $middleware
     * @param ResponseInterface $response
     */
    public function __construct(MiddlewareInterface $middleware, ResponseInterface $response)
    {
        $this->middleware = $middleware;
        $this->response = $response;
    }

    /**
     * Registers a callback which will be called before the handle method is called to produce a Response
     *
     * @param callable $callback
     * @return self
     */
    public function beforeHandle(callable $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Dispatches the request
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($request, $this);
    }

    /**
     * Handles the ServerRequest and returns the response
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $callback = $this->callback;
        if ($callback) {
            $callback($request);
        }

        return $this->response;
    }

    /**
     * Gets the Server Request Object that was passed to handle method
     *
     * @return ServerRequestInterface|null
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }
}
