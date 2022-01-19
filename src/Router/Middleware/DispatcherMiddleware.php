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

namespace Lightning\Router\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Lightning\Router\Event\AfterFilterEvent;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Router\Event\BeforeFilterEvent;
use Psr\Http\Message\ResponseFactoryInterface;
use Lightning\Router\Exception\RouterException;
use Psr\EventDispatcher\EventDispatcherInterface;

class DispatcherMiddleware implements MiddlewareInterface
{
    private $callable;
    private array $arguments;

    private ?ResponseFactoryInterface $responseFactory;
    private ?EventDispatcherInterface $eventDispatcher;

    /**
     * Constructor
     *
     * @param callable $callable
     * @param array $arguments
     * @param EventDispatcherInterface|null $eventDispatcher
     * @param ResponseFactoryInterface|null $responseFactory
     */
    public function __construct(callable $callable, array $arguments, ?EventDispatcherInterface $eventDispatcher = null, ?ResponseFactoryInterface $responseFactory = null)
    {
        $this->callable = $callable;
        $this->arguments = $arguments;

        $this->eventDispatcher = $eventDispatcher;
        $this->responseFactory = $responseFactory;
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
        foreach ($this->arguments as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        if ($this->eventDispatcher) {
            $event = $this->eventDispatcher->dispatch(new BeforeFilterEvent($request));
            if ($response = $event->getResponse()) {
                return $response;
            }
        }

        $response = $this->dispatch($this->callable, $request);

        if ($this->eventDispatcher) {
            $response = $this->eventDispatcher->dispatch(new AfterFilterEvent($request, $response))->getResponse();
        }

        return $response;
    }

    /**
     * Dispatch
     *
     * @param callable $callable
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    private function dispatch(callable $callable, ServerRequestInterface $request): ResponseInterface
    {
        $arguments = $this->responseFactory ? [$request, $this->responseFactory->createResponse()] : [$request];

        $response = $callable(...$arguments);
        if (! $response instanceof ResponseInterface) {
            throw new RouterException('No response was returned');
        }

        return $response;
    }
}
