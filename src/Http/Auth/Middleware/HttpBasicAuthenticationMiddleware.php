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
use Lightning\Http\Auth\PasswordHasherInterface;
use Lightning\Http\Auth\IdentityServiceInterface;
use Lightning\Http\Exception\UnauthorizedException;

class HttpBasicAuthenticationMiddleware extends AbstractAuthenticationMiddleware implements MiddlewareInterface
{
    private IdentityServiceInterface $identityService;
    private ResponseInterface $response;
    private ?string $realm = null;
    private bool $challenge = true;
    private PasswordHasherInterface $passwordHasher;

    /**
     * Constructor
     *
     * @param IdentityServiceInterface $identityService
     * @param PasswordHasherInterface $passwordHasher
     * @param ResponseInterface $response
     */
    public function __construct(IdentityServiceInterface $identityService, PasswordHasherInterface $passwordHasher, ResponseInterface $response)
    {
        $this->identityService = $identityService;
        $this->passwordHasher = $passwordHasher;
        $this->response = $response;
    }

    /**
     * Disables the password challenge
     *
     * @return static
     */
    public function disableChallenge(): self
    {
        $this->challenge = false;

        return $this;
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

        if ($this->challenge) {
            $serverParams = $request->getServerParams();

            return $this->response->withAddedHeader(
                'WWW-Authenticate', sprintf('Basic realm="%s"', $this->realm ?: $serverParams['SERVER_NAME'] ?? '')
            )->withStatus(401);
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
        $serverParams = $request->getServerParams();
        $username = $serverParams['PHP_AUTH_USER'] ?? '';
        $password = $serverParams['PHP_AUTH_PW'] ?? '';

        // Pay attention to empty strings
        if ($username === '' || $password === '') {
            return null;
        }

        $identity = $this->identityService->findByIdentifier($username);
        if ($identity && $this->passwordHasher->verify($password, $identity->get('password'))) {
            return $identity;
        }

        return null;
    }

    /**
     * Get the value of realm
     *
     * @return ?string
     */
    public function getRealm(): ?string
    {
        return $this->realm;
    }

    /**
     * Set the value of realm
     *
     * @param string $realm
     * @return static
     */
    public function setRealm(string $realm): self
    {
        $this->realm = $realm;

        return $this;
    }
}
