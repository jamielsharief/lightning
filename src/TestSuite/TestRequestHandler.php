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

namespace Lightning\TestSuite;

use Psr\Http\Message\ResponseInterface;
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

    /**
     * @var callback
     */
    private $callback = null;

    /**
     * Constructor
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function beforeHandle(callable $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Handles the ServerRequest and returns the response
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $callback = $this->callback;

        if ($callback) {
            $callback($request);
        }

        return $this->response;
    }
}
