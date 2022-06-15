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

use Lightning\Autowire\Autowire;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Lightning\Router\Event\AfterFilterEvent;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Router\Event\BeforeFilterEvent;
use Lightning\Router\Exception\RouterException;
use Psr\EventDispatcher\EventDispatcherInterface;

class DispatcherMiddleware implements MiddlewareInterface
{
    private $callable;
    private ?ResponseInterface $response;
    private ?EventDispatcherInterface $eventDispatcher;
    private ?Autowire $autowire;

    /**
     * Constructor
     */
    public function __construct(callable $callable, ?ResponseInterface $response = null, ?EventDispatcherInterface $eventDispatcher = null, ?Autowire $autowire = null)
    {
        $this->callable = $callable;
        $this->response = $response;
        $this->eventDispatcher = $eventDispatcher;
        $this->autowire = $autowire;
    }

    /**
     * Processes the incoming request
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $callable = $this->callable;
        $params = [ServerRequestInterface::class => $request,ResponseInterface::class => $this->response];

        if ($this->eventDispatcher) {
            $event = $this->eventDispatcher->dispatch(new BeforeFilterEvent($request));
            if ($response = $event->getResponse()) {
                return $response;
            }

            $request = $event->getRequest();
        }

        if ($this->autowire) {
            $response = is_array($callable) ? $this->autowire->method($callable[0], $callable[1], $params) : $this->autowire->function($callable, $params);
        } else {
            $response = $callable($request, $this->response);
        }

        if (! $response instanceof ResponseInterface) {
            throw new RouterException('No response was returned');
        }

        if ($this->eventDispatcher) {
            $response = $this->eventDispatcher->dispatch(new AfterFilterEvent($request, $response))->getResponse();
        }

        return $response;
    }
}
