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

use Closure;
use Lightning\Autowire\Autowire;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Lightning\Router\ControllerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Router\Exception\RouterException;

class DispatcherMiddleware implements MiddlewareInterface
{
    private $callable;
    private ?ResponseInterface $response;
    private ?Autowire $autowire;

    /**
     * Constructor
     */
    public function __construct(callable $callable, ?ResponseInterface $response = null, ?Autowire $autowire = null)
    {
        $this->callable = $callable;
        $this->response = $response;
        $this->autowire = $autowire;
    }

    /**
     * Processes the incoming request
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $callable = $this->callable;
        $params = [ServerRequestInterface::class => $request,ResponseInterface::class => $this->response];

        $isController = is_array($callable) && $callable[0] instanceof ControllerInterface;
        if ($isController && $response = $callable[0]->beforeFilter($request)) {
            return $response;
        }

        if ($this->autowire) {
            if ($callable instanceof Closure) {
                $response = $this->autowire->function($callable, $params);
            } elseif (is_object($callable)) {
                $response = $this->autowire->method($callable, '__invoke', $params);
            } else {
                $response = $this->autowire->method($callable[0], $callable[1], $params);
            }
        } else {
            $response = $callable($request, $this->response);
        }

        if (! $response instanceof ResponseInterface) {
            throw new RouterException('No response was returned');
        }

        return $isController ? $callable[0]->afterFilter($request, $response) : $response;
    }
}
