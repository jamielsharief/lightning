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

namespace Lightning\Controller;

use Lightning\View\View;

use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Lightning\Hook\HookTrait;
use Lightning\Hook\HookInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Controller\Event\AfterRenderEvent;
use Lightning\Controller\Event\BeforeRenderEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Lightning\Controller\Event\BeforeRedirectEvent;
use Lightning\Controller\Event\AfterInitializeEvent;

abstract class AbstractController implements HookInterface
{
    use HookTrait;

    protected ResponseInterface $response;
    protected View $view;

    protected ?EventDispatcherInterface $eventDispatcher = null;
    protected ?LoggerInterface $logger = null;
    protected ?ServerRequestInterface $request = null;

    protected ?string $layout = null;

    /**
     * Constructor
     *
     * @param ResponseInterface $response
     * @param View $view
     * @param EventDispatcherInterface|null $eventDispatcher
     * @param LoggerInterface|null $logger
     */
    public function __construct(ResponseInterface $response, View $view, ?EventDispatcherInterface $eventDispatcher = null, ?LoggerInterface $logger = null)
    {
        $this->response = $response;
        $this->view = $view;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;

        $this->initializeController();
    }

    /**
     * Calls hooks and events
     *
     * @return void
     */
    protected function initializeController(): void
    {
        $this->initialize();
        $this->dispatchEvent(new AfterInitializeEvent($this));
        $this->triggerHook('afterInitialize', [], false);
    }

    /**
     * This is a hook that is called when the Controller is created
     *
     * @return void
     */
    protected function initialize(): void
    {
    }

    /**
     * Renders a view using the View package
     *
     * @param string $view e.g. articles/index
     * @param array $data
     * @param integer $statusCode
     * @return ResponseInterface
     */
    protected function render(string $view, array $data = [], int $statusCode = 200): ResponseInterface
    {
        $event = $this->dispatchEvent(new BeforeRenderEvent($this, $this->request));
        if ($event && $response = $event->getResponse()) {
            return $response;
        }

        if (! $this->triggerHook('beforeRender')) {
            return $this->response;
        }

        $this->response = $this->buildResponse(
            $this->view->withLayout($this->layout ?? null)->render($view, $data), 'text/html', $statusCode
        );

        $this->triggerHook('afterRender', [], false);
        $event = $this->dispatchEvent(new AfterRenderEvent($this, $this->request, $this->response));

        return $event ? $event->getResponse() : $this->response;
    }

    /**
     * Renders a JSON response
     *
     * @param mixed $payload
     * @param integer $statusCode
     * @param integer $jsonFlags e.g. JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
     * @return ResponseInterface
     */
    protected function renderJson($payload, int $statusCode = 200, int $jsonFlags = 0): ResponseInterface
    {
        $event = $this->dispatchEvent(new BeforeRenderEvent($this, $this->request));
        if ($event && $response = $event->getResponse()) {
            return $response;
        }

        if (! $this->triggerHook('beforeRender')) {
            return $this->response;
        }

        $this->response = $this->buildResponse(
            json_encode($payload, $jsonFlags), 'application/json', $statusCode
        );

        $this->triggerHook('afterRender', [], false);
        $event = $this->dispatchEvent(new AfterRenderEvent($this, $this->request, $this->response));

        return $event ? $event->getResponse() : $this->response;
    }

    /**
     * Builds the response object
     *
     * @param string $body
     * @param string $contentType
     * @param integer $statusCode
     * @return ResponseInterface
     */
    private function buildResponse(string $body, string $contentType, int $statusCode = 200): ResponseInterface
    {
        $response = $this->response
            ->withHeader('Content-Type', $contentType)
            ->withStatus($statusCode);

        $response->getBody()->write($body);

        return $response;
    }

    /**
     * Sends a file as a Response
     *
     * @param string $path
     * @param array $options
     * @return ResponseInterface
     */
    protected function renderFile(string $path, array $options = []): ResponseInterface
    {
        $name = basename($path);
        $isDownload = $options['download'] ?? true;

        if (strpos($path, '../') !== false) {
            throw new InvalidArgumentException(sprintf('Path `%s` is a relative path', $path));
        }
        $event = $this->dispatchEvent(new BeforeRenderEvent($this, $this->request));
        if ($event && $response = $event->getResponse()) {
            return $response;
        }

        if (! $this->triggerHook('beforeRender')) {
            return $this->response;
        }

        $response = $this->response
            ->withStatus(200)
            ->withHeader('Content-Type', mime_content_type($path))
            ->withHeader('Content-Length', (string) filesize($path) ?: 0);

        if ($isDownload) {
            $response = $response->withHeader('Content-Disposition', sprintf('attachment; filename="%s"', $name));
        }

        $stream = $this->response->getBody();
        $stream->rewind();
        $stream->write(file_get_contents($path));

        $this->response = $response->withBody($stream);

        $this->triggerHook('afterRender', [], false);
        $event = $this->dispatchEvent(new AfterRenderEvent($this, $this->request, $this->response));

        return $event ? $event->getResponse() : $this->response;
    }

    /**
    * Sets the response as a redirect, return this from your Controller action
    *
    * @param string $uri e.g /articles or https://app.test/articles
    * @param integer $status
    * @return ResponseInterface
    */
    protected function redirect(string $uri, int $status = 302): ResponseInterface
    {
        $event = $this->dispatchEvent(new BeforeRedirectEvent($this, $uri, $this->request));
        if ($event && $response = $event->getResponse()) {
            return $response;
        }

        if (! $this->triggerHook('beforeRedirect', [$uri])) {
            return $this->response;
        }

        return $this->response->withHeader('Location', $uri)->withStatus($status);
    }

    /**
     * Logs using a PSR-3 Logger if available
     *
     * @param string $level e.g.  LogLevel::ERROR
     * @param string $message
     * @param array $context
     * @return boolean
     */
    protected function log(string $level, string $message, array $context = []): bool
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }

        return $this->logger !== null;
    }

    /**
     * Dispatches an Event using the PSR-14 Event Dispatcher if available
     *
     * @param object $event
     * @return object|null
     */
    protected function dispatchEvent(object $event): ?object
    {
        return $this->eventDispatcher ? $this->eventDispatcher->dispatch($event) : null;
    }

    /**
     * Sets the current controller response
     *
     * @param ResponseInterface $response
     * @return self;
     */
    public function setResponse(ResponseInterface $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Gets the current controller Response
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Sets the Request object
     *
     * @param ServerRequestInterface $request
     * @return self
     */
    public function setRequest(ServerRequestInterface $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Gets the Request object
     *
     * @return RequestInterface|null
     */
    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }
}
