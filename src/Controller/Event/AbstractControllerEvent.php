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

abstract class AbstractControllerEvent
{
    protected AbstractController $controller;
    protected ?ResponseInterface $response;
    protected ?ServerRequestInterface $request;

    /**
     * Constructor
     *
     * @param AbstractController $controller
     * @param ServerRequestInterface|null $request
     * @param ResponseInterface|null $response
     */
    public function __construct(AbstractController $controller, ?ServerRequestInterface $request = null, ?ResponseInterface $response = null)
    {
        $this->controller = $controller;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Gets the Controller for this Event
     *
     * @return AbstractController
     */
    public function getController(): AbstractController
    {
        return $this->controller;
    }

    /**
     * Undocumented function
     *
     * @return ServerRequestInterface|null
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Sets the request
     *
     * @param ServerRequestInterface $request
     * @return static
     */
    public function setRequest(ServerRequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Sets the Response
     *
     * @param ResponseInterface $response
     * @return static
     */
    public function setResponse(ResponseInterface  $response): static
    {
        $this->response = $response;

        return $this;
    }
    /**
     * Gets the Response if Available
     *
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
