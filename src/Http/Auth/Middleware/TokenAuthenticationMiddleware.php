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

namespace Lightning\Http\Auth\Middleware;

use Lightning\Http\Auth\Identity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Http\Auth\IdentityServiceInterface;
use Lightning\Http\Exception\UnauthorizedException;

class TokenAuthenticationMiddleware extends AbstractAuthenticationMiddleware implements MiddlewareInterface
{
    private IdentityServiceInterface $identityService;
    private ?string $queryParam = null;
    private ?string $header = null;

    /**
     * Constructor
     *
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * Process the server request and produce a response
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // check against path and publicPaths paths
        if (! $this->requiresAuthentication($request)) {
            return $handler->handle($request);
        }

        $identity = $this->authenticate($request);
        if ($identity) {
            return $handler->handle($request->withAttribute('identity', $identity));
        }

        throw new UnauthorizedException();
    }

    /**
     * The authentication logic
     *
     * @param ServerRequestInterface $request
     * @return Identity|null
     */
    protected function authenticate(ServerRequestInterface $request): ?Identity
    {
        $token = '';

        // Not both
        if ($this->queryParam) {
            $params = $request->getQueryParams();
            $token = $params[$this->queryParam] ?? '';
        } elseif ($this->header) {
            $token = $request->getHeaderLine($this->header);
        }

        if ($token === '') {
            return null;
        }

        return $this->identityService->findByIdentifier($token);
    }

    /**
     * Set the value of queryParam
     *
     * @param string $queryParam
     * @return static
     */
    public function setQueryParam(string $queryParam): static
    {
        $this->queryParam = $queryParam;

        return $this;
    }

    /**
     * Set the value of header
     *
     * @param string $header
     * @return static
     */
    public function setHeader(string $header): static
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get the value of queryParam
     *
     * @return ?string
     */
    public function getQueryParam(): ?string
    {
        return $this->queryParam;
    }

    /**
     * Get the value of header
     *
     * @return ?string
     */
    public function getHeader(): ?string
    {
        return $this->header;
    }
}
