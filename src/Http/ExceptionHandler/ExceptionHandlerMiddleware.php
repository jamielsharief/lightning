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

namespace Lightning\Http\ExceptionHandler;

use Exception;
use Throwable;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

use Lightning\Http\Exception\HttpException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * ExceptionHandlerMiddleware
 *
 * This is based upon the recommendation of PSR-15, but does not include an error handler
 *
 * @see https://www.php-fig.org/psr/psr-15/
 */
class ExceptionHandlerMiddleware implements MiddlewareInterface
{
    private string $path;
    private ErrorRenderer $render;
    private ResponseFactoryInterface $responseFactory;
    private ?LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param string $path
     * @param ErrorRenderer $renderer
     * @param ResponseFactoryInterface $responseFactory
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $path, ErrorRenderer $renderer, ResponseFactoryInterface $responseFactory, ?LoggerInterface $logger = null)
    {
        $this->path = rtrim($path, '/') . '/';
        $this->render = $renderer;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    /**
     * Processes an incoming server request in order to produce a response.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $exception) {
            return $this->handleException($exception, $request);
        }
    }

    /**
     * Processes the exception to produce a response
     *
     * @param Throwable $exception
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    private function handleException(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        // Standard error for non HTTP exceptions
        $statusCode = $exception instanceof HttpException ? $exception->getCode() : 500;
        $message = $exception instanceof HttpException ? $exception->getMessage() : 'Internal Server Error'; // TODO: wait for PSR Localization

        // Log if needed
        if ($this->logger) {
            $this->logger->error(
                sprintf('%s Exception in %s:%s', $exception->getMessage(), $exception->getFile(), $exception->getLine())
            );
        }

        // security code and message only
        if ($this->isJson($request)) {
            return $this->createResponse(
                $statusCode, $this->render->json($message, $statusCode), 'application/json'
            );
        }

        if ($this->isXml($request) && ! $this->isHtml($request)) {
            return $this->createResponse(
                $statusCode, $this->render->xml($message, $statusCode), 'application/xml'
            );
        }

        return $this->createResponse(
            $statusCode, $this->render->html($this->template($exception, $statusCode), $message, $statusCode, $request, $exception), 'text/html'
        );
    }

    private function template(Throwable $exception, int  $statusCode): string
    {
        $template = $exception instanceof HttpException && $statusCode < 500 ? '400' : '500';

        return sprintf('%s/error%s.php', $this->path, $template);
    }

    /**
     * Checks if the request is wanting JSON
     *
     * @param ServerRequestInterface $request
     * @return boolean
     */
    private function isJson(ServerRequestInterface $request): bool
    {
        return strpos($request->getHeaderLine('Accept'), 'application/json') !== false;
    }

    /**
     * Checksif the request is wanting XML
     *
     * @param ServerRequestInterface $request
     * @return boolean
     */
    private function isHtml(ServerRequestInterface $request): bool
    {
        return strpos($request->getHeaderLine('Accept'), 'text/html') !== false;
    }

    /**
     * Checksif the request is wanting XML
     *
     * @param ServerRequestInterface $request
     * @return boolean
     */
    private function isXml(ServerRequestInterface $request): bool
    {
        return (bool) preg_match('/text\/xml|application\/xml/', $request->getHeaderLine('Accept'));
    }
    /**
     * Creates the Response Object
     *
     * @param integer $statusCode
     * @param string $body
     * @param string $contentType
     * @return ResponseInterface
     */
    private function createResponse(int $statusCode, string $body, string $contentType): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write($body);

        return $response ->withHeader('Content-Type', $contentType); // 'text/html'
    }
}
