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
use Psr\Http\Message\ResponseInterface;
use Lightning\Router\ControllerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Controller\Event\AfterFilterEvent;
use Lightning\Controller\Event\AfterRenderEvent;
use Lightning\Controller\Event\BeforeFilterEvent;
use Lightning\Controller\Event\BeforeRenderEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Lightning\Controller\Event\BeforeRedirectEvent;
use Lightning\Controller\Event\AfterInitializeEvent;

abstract class AbstractController implements HookInterface, ControllerInterface
{
    use HookTrait;

    protected ResponseInterface $response;
    protected View $view;

    protected ?EventDispatcherInterface $eventDispatcher = null;
    protected ?LoggerInterface $logger = null;
    protected ?ServerRequestInterface $request = null;

    protected ?string $layout = null;

    /**
     * Default settings for the renderJson method
     */
    protected const JSON_FLAGS = 0;

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

        $this->registerHooks();

        $this->initialize();

        $this->dispatchEvent(new AfterInitializeEvent($this));
        $this->triggerHook('afterInitialize', [], false);
    }

    /**
     * Registers the controller hooks
     *
     * @return void
     */
    private function registerHooks(): void
    {
        $this->registerHook('beforeFilter', 'beforeFilter');
        $this->registerHook('afterFilter', 'afterFilter');
        $this->registerHook('beforeRender', 'beforeRender');
        $this->registerHook('afterRender', 'afterRender');
        $this->registerHook('beforeRedirect', 'beforeRedirect');
    }

    /**
     * This is called when the Controller is created, so that you don't have to overide the constructor
     *
     * @return void
     */
    protected function initialize(): void
    {
    }

    /**
     * This is hook is called before the action is invoked. This is for setting up the controller or application.
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function beforeFilter(ServerRequestInterface $request): bool
    {
        return true;
    }

    /**
     * This hook is called after the action was invoked and the response was sent back to the middleware for processing.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return void
     */
    protected function afterFilter(ServerRequestInterface $request, ResponseInterface $response): void
    {
    }

    /**
     * This hook is called before the render process starts, if this returns false or is used to set a redirect in
     * response object, then the response object will be returned instead of rendering.
     *
     * @return bool
     */
    protected function beforeRender(): bool
    {
        return true;
    }

    /**
     * This hook is called after the render has taken place
     *
     * @return void
     */
    protected function afterRender(): void
    {
    }

    /**
     * This hook is called before the redirect response is setup
     *
     * @param string $url
     * @return void
     */
    protected function beforeRedirect(string $url): void
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
        if ($response = $this->doBeforeRender()) {
            return $this->response = $response;
        }

        $this->response = $this->buildResponse(
            $this->view->withLayout($this->layout ?? null)->render($view, $data), 'text/html', $statusCode
        );

        return $this->response = $this->doAfterRender();
    }

    /**
     * Renders a JSON response
     *
     * @param mixed $payload
     * @param integer $statusCode
     * @param integer $jsonFlags e.g. JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
     * @return ResponseInterface
     */
    protected function renderJson($payload, int $statusCode = 200, int $jsonFlags = self::JSON_FLAGS): ResponseInterface
    {
        if ($response = $this->doBeforeRender()) {
            return $this->response = $response;
        }

        $this->response = $this->buildResponse(
            json_encode($payload, $jsonFlags), 'application/json', $statusCode
        );

        return $this->response = $this->doAfterRender();
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
        if ($response = $this->doBeforeRender()) {
            return $this->response = $response;
        }

        $this->response = $this->buildFileResponse($path, $options['download'] ?? true);

        return $this->response = $this->doAfterRender();
    }

    /*
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
            return $this->response = $response;
        }

        if (! $this->triggerHook('beforeRedirect', [$uri]) || $this->response->getStatusCode() === 302) {
            return $this->response = $response;
        }

        return $this->response = $this->response
            ->withHeader('Location', $uri)
            ->withStatus($status);
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
     * Builds a reponse for a file
     *
     * @param string $path
     * @param boolean $isDownload
     * @return ResponseInterface
     */
    private function buildFileResponse(string $path, bool $isDownload): ResponseInterface
    {
        if (strpos($path, '../') !== false) {
            throw new InvalidArgumentException(sprintf('`%s` is a relative path', $path));
        }

        if (! file_exists($path)) {
            throw new InvalidArgumentException(sprintf('`%s` does not exist or is not a file', $path));
        }

        $name = basename($path);

        $response = $this->response
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
     * Workhorse for before rendering
     *
     * @return ResponseInterface|null
     */
    protected function doBeforeRender(): ?ResponseInterface
    {
        $event = $this->dispatchEvent(new BeforeRenderEvent($this, $this->request));
        if ($event && $response = $event->getResponse()) {
            return $response;
        }

        if (! $this->triggerHook('beforeRender') || $this->response->getStatusCode() === 302) {
            return $response;
        }

        return null;
    }

    /**
     * Workhorse for after rendering
     *
     * @return ResponseInterface|null
     */
    protected function doAfterRender(): ?ResponseInterface
    {
        $this->triggerHook('afterRender', [], false);
        $event = $this->dispatchEvent(new AfterRenderEvent($this, $this->request, $this->response));

        return $event ? $event->getResponse() : $this->response;
    }

    /**
     * Starts the Controller
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface|null
     */
    public function startup(ServerRequestInterface $request): ?ResponseInterface
    {
        $this->request = $request;

        $event = $this->dispatchEvent(new BeforeFilterEvent($this, $this->request));
        if ($event && $response = $event->getResponse()) {
            return $response;
        }
        if (! $this->triggerHook('beforeFilter', [$request]) || $this->response->getStatusCode() === 302) {
            return $response;
        }

        return null;
    }

    /**
     * Shuts down the Controller
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function shutdown(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;

        $this->triggerHook('afterFilter', [$request,$this->response], false);
        $event = $this->dispatchEvent(new AfterFilterEvent($this, $this->request, $this->response));

        return $event ? $event->getResponse() : $this->response;
    }

    /**
     * Sets the current controller response
     *
     * @param ResponseInterface $response
     * @return static
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
     * @return static
     */
    public function setRequest(ServerRequestInterface $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Gets the Request object
     *
     * @return ServerRequestInterface|null
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }
}
