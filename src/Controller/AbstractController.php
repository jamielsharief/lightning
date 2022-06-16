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

namespace Lightning\Controller;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Controller\Event\InitializeEvent;
use Lightning\Controller\Event\AfterRenderEvent;
use Lightning\TemplateRenderer\TemplateRenderer;
use Lightning\Controller\Event\BeforeRenderEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Lightning\Controller\Event\AfterRedirectEvent;
use Lightning\Controller\Event\BeforeRedirectEvent;

/**
 * Abstract Controller
 *
 * @internal design has been changed with hook methods added rather than hard coding events etc, these can be overridden with a trait to get
 * the behavior that you want, eg. PSR-14 events
 */
abstract class AbstractController
{
    protected TemplateRenderer $templateRenderer;
    protected EventDispatcherInterface $eventDispatcher;
    protected ?ServerRequestInterface $request = null;

    protected ?string $layout = null;

    /**
     * Default settings for the renderJson method
     */
    protected const JSON_FLAGS = 0;

    /**
     * Constructor
     */
    public function __construct(TemplateRenderer $templateRenderer, EventDispatcherInterface $eventDispatcher)
    {
        $this->templateRenderer = $templateRenderer;
        $this->eventDispatcher = $eventDispatcher;

        $this->initialize();
        $this->eventDispatcher->dispatch(new InitializeEvent($this));
    }

    /**
     * Hook is called when the Controller object is created
     */
    public function initialize(): void
    {
    }

    /**
     * Renders a template using the View package
     *
     * @param string $template e.g. articles/index
     */
    public function render(string $template, array $data = [], int $statusCode = 200): ResponseInterface
    {
        if ($event = $this->eventDispatcher->dispatch(new BeforeRenderEvent($this, $this->request))) {
            if ($response = $event->getResponse()) {
                return $response;
            }
        }

        $response = $this->buildResponse(
            $this->templateRenderer->withLayout($this->layout ?? null)->render($template, $data), 'text/html', $statusCode
        );

        $event = $this->eventDispatcher->dispatch(new AfterRenderEvent($this, $this->request, $response));

        return $event ? $event->getResponse() : $response;
    }

    /**
     * Renders a JSON response
     */
    public function renderJson($payload, int $statusCode = 200, int $jsonFlags = self::JSON_FLAGS): ResponseInterface
    {
        if ($event = $this->eventDispatcher->dispatch(new BeforeRenderEvent($this, $this->request))) {
            if ($response = $event->getResponse()) {
                return $response;
            }
        }

        $response = $this->buildResponse(
            json_encode($payload, $jsonFlags), 'application/json', $statusCode
        );

        $event = $this->eventDispatcher->dispatch(new AfterRenderEvent($this, $this->request, $response));

        return $event ? $event->getResponse() : $response;
    }

    /**
     * Sends a file as a Response
     */
    public function renderFile(string $path, array $options = []): ResponseInterface
    {
        if ($event = $this->eventDispatcher->dispatch(new BeforeRenderEvent($this, $this->request))) {
            if ($response = $event->getResponse()) {
                return $response;
            }
        }

        $response = $this->buildFileResponse($path, $options['download'] ?? true);

        $event = $this->eventDispatcher->dispatch(new AfterRenderEvent($this, $this->request, $response));

        return $event ? $event->getResponse() : $response;
    }

    /*
     * Sets the response as a redirect, return this from your Controller action
     *
     * @param string $uri e.g /articles or https://app.test/articles
     */
    public function redirect(string $uri, int $status = 302): ResponseInterface
    {
        if ($event = $this->eventDispatcher->dispatch(new BeforeRedirectEvent($this, $uri, $this->request))) {
            if ($response = $event->getResponse()) {
                return $response;
            }
        }

        $response = $this->createResponse()
            ->withHeader('Location', $uri)
            ->withStatus($status);

        $event = $this->eventDispatcher->dispatch(new AfterRedirectEvent($this, $uri, $this->request, $response));

        return $event ? $event->getResponse() : $response;
    }

    /**
     * Builds the response object
     */
    private function buildResponse(string $body, string $contentType, int $statusCode = 200): ResponseInterface
    {
        $response = $this->createResponse()
            ->withHeader('Content-Type', $contentType)
            ->withStatus($statusCode);

        $response->getBody()->write($body);

        return $response;
    }

    /**
     * Builds a reponse for a file
     */
    private function buildFileResponse(string $path, bool $isDownload): ResponseInterface
    {
        if (strpos($path, '../') !== false) {
            throw new InvalidArgumentException(sprintf('`%s` is a relative path', $path));
        }

        if (! is_file($path)) {
            throw new InvalidArgumentException(sprintf('`%s` does not exist or is not a file', $path));
        }

        $name = basename($path);

        $response = $this->createResponse()
            ->withStatus(200)
            ->withHeader('Content-Type', mime_content_type($path))
            ->withHeader('Content-Length', (string) filesize($path) ?: 0);

        if ($isDownload) {
            $response = $response->withHeader('Content-Disposition', sprintf('attachment; filename="%s"', $name));
        }

        $response->getBody()->write(file_get_contents($path));

        return $response;
    }

    /**
     * Sets the Request object
     */
    public function setRequest(ServerRequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Gets the Request object
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request ?? null;
    }

    /**
     * Get the Template Renderer
     */
    public function getTemplateRenderer(): TemplateRenderer
    {
        return $this->templateRenderer;
    }

    /**
     * Set the Template Renderer
     */
    public function setTemplateRenderer(TemplateRenderer $templateRenderer): static
    {
        $this->templateRenderer = $templateRenderer;

        return $this;
    }

    /**
     * Factory method
     */
    abstract public function createResponse(): ResponseInterface;
}
