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

use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Controller\Event\InitializeEvent;
use Lightning\TemplateRenderer\TemplateRenderer;
use Psr\EventDispatcher\EventDispatcherInterface;

abstract class AbstractController
{
    protected TemplateRenderer $templateRenderer;
    protected ?EventDispatcherInterface $eventDispatcher;
    protected ?LoggerInterface $logger;
    protected ?ServerRequestInterface $request;

    protected ?string $layout = null;

    /**
     * Default settings for the renderJson method
     */
    protected const JSON_FLAGS = 0;

    /**
     * Constructor
     */
    public function __construct(TemplateRenderer $templateRenderer, ?EventDispatcherInterface $eventDispatcher = null, ?LoggerInterface $logger = null)
    {
        $this->templateRenderer = $templateRenderer;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;

        $this->initialize();
        $this->dispatchEvent(new InitializeEvent($this));
    }

    /**
     * This is called when the Controller is created, so that you don't have to overide the constructor
     */
    protected function initialize(): void
    {
    }

    /**
     * Renders a template using the View package
     *
     * @param string $template e.g. articles/index
     */
    protected function render(string $template, array $data = [], int $statusCode = 200): ResponseInterface
    {
        return $this->buildResponse(
            $this->templateRenderer->withLayout($this->layout ?? null)->render($template, $data), 'text/html', $statusCode
        );
    }

    /**
     * Renders a JSON response
     */
    protected function renderJson($payload, int $statusCode = 200, int $jsonFlags = self::JSON_FLAGS): ResponseInterface
    {
        return $this->buildResponse(
            json_encode($payload, $jsonFlags), 'application/json', $statusCode
        );
    }

    /**
     * Sends a file as a Response
     */
    protected function renderFile(string $path, array $options = []): ResponseInterface
    {
        return $this->buildFileResponse($path, $options['download'] ?? true);
    }

    /*
     * Sets the response as a redirect, return this from your Controller action
     *
     * @param string $uri e.g /articles or https://app.test/articles
     */
    protected function redirect(string $uri, int $status = 302): ResponseInterface
    {
        return $this->createResponse()
            ->withHeader('Location', $uri)
            ->withStatus($status);
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
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Factory method
     */
    abstract protected function createResponse(): ResponseInterface;

    /**
     * Get the value of eventDispatcher
     */
    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * Set the value of eventDispatcher
     */
    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher): static
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * Dispatches an Event if the EventDispatcher is available
     */
    public function dispatchEvent(object $event): ?object
    {
        return $this->eventDispatcher ? $this->eventDispatcher->dispatch($event) : null;
    }

    /**
     * Get the value of logger
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Set the value of logger
     */
    public function setLogger(?LoggerInterface $logger): static
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Logs with an arbitrary level if the Logger is available
     */
    public function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Get the value of template renderer
     */
    public function getTemplateRenderer(): TemplateRenderer
    {
        return $this->templateRenderer;
    }

    /**
     * Set the template renderer
     */
    public function setTemplateRenderer(TemplateRenderer $templateRenderer): static
    {
        $this->templateRenderer = $templateRenderer;

        return $this;
    }
}
