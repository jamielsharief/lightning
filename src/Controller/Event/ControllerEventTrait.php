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

trait ControllerEventTrait
{
    private AbstractController $controller;
    private ?ServerRequestInterface $request;
    private ?ResponseInterface $response;

    /**
     * Get the Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * set the cController
     */
    public function setController(AbstractController $controller): static
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Get the Request object
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request ?? null;
    }

    /**
     * Set the Request object
     */
    public function setRequest(ServerRequestInterface  $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the Response object
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response ?? null;
    }

    /**
     * Set the Response object
     */
    public function setResponse(ResponseInterface $response): static
    {
        $this->response = $response;

        return $this;
    }
}
