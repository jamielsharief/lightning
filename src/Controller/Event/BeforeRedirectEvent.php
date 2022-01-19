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

namespace Lightning\Controller\Event;

use Psr\Http\Message\ResponseInterface;
use Lightning\Controller\AbstractController;
use Psr\Http\Message\ServerRequestInterface;

class BeforeRedirectEvent extends AbstractControllerStoppableEvent
{
    private string $url;

    /**
     * Constructor
     *
     * @param AbstractController $controller
     * @param ServerRequestInterface|null $request
     * @param ResponseInterface|null $response
     */
    public function __construct(AbstractController $controller, string $url, ?ServerRequestInterface $request = null, ?ResponseInterface $response = null)
    {
        $this->url = $url;
        parent::__construct($controller, $request, $response);
    }

    /**
     * Get the value of url
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set the value of url
     *
     * @param string $url
     *
     * @return self
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
