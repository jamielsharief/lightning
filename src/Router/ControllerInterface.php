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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ControllerInterface
{
    /**
     * This is called before the controller action is invoked, if a reponse is returned
     * then is will be returned by the request handler
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface|null
     */
    public function startup(ServerRequestInterface $request): ?ResponseInterface;

    /**
     * This is called after the controller action was invoked
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function shutdown(ServerRequestInterface  $request, ResponseInterface $response): ResponseInterface;
}
