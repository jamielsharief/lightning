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
use Lightning\Http\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Http\Auth\PasswordHasherInterface;
use Lightning\Http\Auth\IdentityServiceInterface;
use Lightning\Http\Exception\UnauthorizedException;

class FormAuthenticationMiddleware extends AbstractAuthenticationMiddleware implements MiddlewareInterface
{
    private IdentityServiceInterface $identityService;
    private SessionInterface $session;
    private ResponseInterface $response;
    private string $usernameField = 'email';
    private string $passwordField = 'password';
    private string $sessionKey = 'identity';
    private string $loginPath = '/login';
    private PasswordHasherInterface $passwordHasher;

    /**
     * An unauthorized exception is thrown unless you set an url where it will be redirected to
     *
     * @var string|null
     */
    protected ?string $unauthenticatedRedirect = null;

    /**
     * Constructor
     *
     * @param IdentityServiceInterface $identityService
     * @param PasswordHasherInterface $passwordHasher
     * @param SessionInterface $session
     * @param ResponseInterface $emptyResponse
     */
    public function __construct(IdentityServiceInterface $identityService, PasswordHasherInterface $passwordHasher, SessionInterface $session, ResponseInterface $emptyResponse)
    {
        $this->identityService = $identityService;
        $this->passwordHasher = $passwordHasher;
        $this->session = $session;
        $this->response = $emptyResponse;
    }

    /**
     * Sets the unauthenticated redirect url
     *
     * @param string $url
     * @return self
     */
    public function setUnauthenticatedRedirect(string $url): self
    {
        $this->unauthenticatedRedirect = $url;

        return $this;
    }

    /**
     * Get the value of unauthenticatedRedirect
     *
     * @return ?string
     */
    public function getUnauthenticatedRedirect(): ?string
    {
        return $this->unauthenticatedRedirect;
    }

    /**
     * Sets the username field, e.g. username, email, user etc
     *
     * @param string $field
     * @return static
     */
    public function setUsernameField(string $field): self
    {
        $this->usernameField = $field;

        return $this;
    }

    /**
     * Set session key
     *
     * @param string $key
     * @return static
     */
    public function setSessionKey(string $key): self
    {
        $this->sessionKey = $key;

        return $this;
    }

    /**
     * Sets the password field if needed
     *
     * @param string $field
     * @return static
     */
    public function setPasswordField(string $field): self
    {
        $this->passwordField = $field;

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

        // Get user from session
        $identity = $this->getLoggedInUser();

        // If not logged in and on login page attempt to authenticate
        if (! $identity && $request->getUri()->getPath() === $this->loginPath) {

            // only allow authentication from POST requests
            if ($request->getMethod() === 'POST') {
                $identity = $this->authenticate($request);
            }

            if (! $identity) {
                return $handler->handle($request); # Render login page ONLY
            }
            $this->session->set($this->sessionKey, $identity->toArray());
        }

        // User is now logged in
        if ($identity) {
            return $handler->handle($request->withAttribute('identity', $identity));
        }

        if ($this->unauthenticatedRedirect) {
            return $this->response = $this->response
                ->withHeader('Location', $this->unauthenticatedRedirect)
                ->withStatus(302);
        }

        throw new UnauthorizedException();
    }

    /**
     * Gets the user from Session
     *
     * @return Identity|null
     */
    protected function getLoggedInUser(): ?Identity
    {
        // Check session
        $auth = $this->session->get($this->sessionKey);

        return  $auth ? new Identity($auth) : null;
    }

    /**
     * The authentication logic
     *
     * @param ServerRequestInterface $request
     * @return Identity|null
     */
    protected function authenticate(ServerRequestInterface $request): ?Identity
    {

        // Get the credentials from the request
        $body = $request->getParsedBody();
        $username = $body[$this->usernameField] ?? '';
        $password = $body[$this->passwordField] ?? '';

        // Pay attention to empty strings
        if ($username === '' || $password === '') {
            return null;
        }

        // User not found
        $identity = $this->identityService->findByIdentifier($username);
        if ($identity && $this->passwordHasher->verify($password, $identity->get($this->passwordField))) {
            return $identity;
        }

        return null;
    }

    /**
     * Get the value of usernameField
     *
     * @return string
     */
    public function getUsernameField(): string
    {
        return $this->usernameField;
    }

    /**
     * Get the value of passwordField
     *
     * @return string
     */
    public function getPasswordField(): string
    {
        return $this->passwordField;
    }

    /**
     * Get the value of sessionKey
     *
     * @return string
     */
    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    /**
     * Get the value of loginPath
     *
     * @return string
     */
    public function getLoginPath(): string
    {
        return $this->loginPath;
    }

    /**
     * Set the value of loginPath
     *
     * @param string $loginPath
     *
     * @return self
     */
    public function setLoginPath(string $loginPath): self
    {
        $this->loginPath = $loginPath;

        return $this;
    }

    /**
     * Get the value of session
     *
     * @return SessionInterface
     */
    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    /**
     * Get the value of identityService
     *
     * @return IdentityServiceInterface
     */
    public function getIdentityService(): IdentityServiceInterface
    {
        return $this->identityService;
    }
}
