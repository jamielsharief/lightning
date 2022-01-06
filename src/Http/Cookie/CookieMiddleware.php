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

namespace Lightning\Http\Cookie;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CookieMiddleware implements MiddlewareInterface
{
    protected Cookies $cookies;

    /**
     * Constructor
     *
     * @param Cookies $cookies
     */
    public function __construct(Cookies $cookies)
    {
        $this->cookies = $cookies;
    }
    /**
     * Undocumented function
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withAttribute('cookies', $this->cookies);

        return $this->cookies
            ->setServerRequest($request)
            ->addToResponse($handler->handle($request));
    }
}
