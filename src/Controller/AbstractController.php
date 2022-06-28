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

use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\TemplateRenderer\TemplateRenderer;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Abstract Controller
 *
 * @internal design has been changed with hook methods added rather than hard coding events etc, these can be overridden with a trait to get
 * the behavior that you want, eg. PSR-14 events
 */
abstract class AbstractController
{
    protected TemplateRenderer $templateRenderer;
    protected ?ServerRequestInterface $request;
    protected ?ResponseInterface $response;
    protected EventDispatcherInterface $eventDispatcher;

    protected ?string $layout = null;

    /**
     * Default settings for the renderJson method
     */
    protected const JSON_FLAGS = 0;

    /**
     * Constructor
     */
    public function __construct(TemplateRenderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;

        $this->initialize();
    }

    /**
     * Hook is called when the Controller object is created
     */
    protected function initialize(): void
    {
    }

    /**
     * Before render hook
     */
    protected function beforeRender(): ?ResponseInterface
    {
        return null;
    }

    /**
     * After render hook
     */
    protected function afterRender(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    /**
     * Before Redirect hook
     */
    protected function beforeRedirect(string $url): ?ResponseInterface
    {
        return null;
    }

    /**
     * After Redirect hook
     */
    protected function afterRedirect(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    /**
     * Renders a template using the View package
     *
     * @param string $template e.g. articles/index
     */
    protected function render(string $template, array $data = [], int $statusCode = 200): ResponseInterface
    {
        if ($response = $this->beforeRender()) {
            return $response;
        }

        $response = $this->buildResponse(
            $this->templateRenderer->withLayout($this->layout ?? null)->render($template, $data), 'text/html', $statusCode
        );

        return $this->response = $this->afterRender($response);
    }

    /**
     * Renders a JSON response
     */
    protected function renderJson($payload, int $statusCode = 200, int $jsonFlags = self::JSON_FLAGS): ResponseInterface
    {
        if ($response = $this->beforeRender()) {
            return $response;
        }

        $response = $this->buildResponse(
            json_encode($payload, $jsonFlags), 'application/json', $statusCode
        );

        return $this->response = $this->afterRender($response);
    }

    /**
     * Sends a file as a Response
     */
    protected function renderFile(string $path, array $options = []): ResponseInterface
    {
        if ($response = $this->beforeRender()) {
            return $response;
        }

        $response = $this->buildFileResponse($path, $options['download'] ?? true);

        return $this->response = $this->afterRender($response);
    }

    /*
     * Sets the response as a redirect, return this from your Controller action
     *
     * @param string $uri e.g /articles or https://app.test/articles
     */
    protected function redirect(string $uri, int $status = 302): ResponseInterface
    {
        if ($response = $this->beforeRedirect($uri)) {
            return $response;
        }

        $response = $this->createResponse()
            ->withHeader('Location', $uri)
            ->withStatus($status);

        return $this->response = $this->afterRedirect($response);
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
    abstract public function createResponse(): ResponseInterface;

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
     * Get the Response object if generated
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * Set the Response object to be returned
     */
    public function setResponse(?ResponseInterface $response): static
    {
        $this->response = $response;

        return $this;
    }

    /**
     *
     */
    public function addEventListener(string $eventType, callable $callable): static
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     *
     */
    public function dispatchEvent(object $event): ?object
    {
        return isset($this->eventDispatcher) ? $this->eventDispatcher->dispatch($event) : null;
    }
}
