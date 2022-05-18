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

namespace Lightning\Router\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractFilterEvent
{
    protected ServerRequestInterface $request;
    protected ?ResponseInterface $response = null;

    /**
     * Constructor
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Gets the request
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Sets the request
     */
    public function setRequest(ServerRequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Sets the Response
     */
    public function setResponse(ResponseInterface  $response): static
    {
        $this->response = $response;

        return $this;
    }
    /**
     * Gets the Response if Available
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
