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

namespace Lightning\Controller\Event;

use Psr\Http\Message\ResponseInterface;
use Lightning\Controller\AbstractController;
use Psr\Http\Message\ServerRequestInterface;

final class AfterRedirectEvent
{
    use ControllerEventTrait;
    private string $uri;

    public function __construct(AbstractController $controller, string $uri, ?ServerRequestInterface $request = null, ResponseInterface $response = null)
    {
        $this->controller = $controller;
        $this->uri = $uri;
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * Get the value of uri
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Set the value of uri
     */
    public function setUri(string $uri): static
    {
        $this->uri = $uri;

        return $this;
    }
}
