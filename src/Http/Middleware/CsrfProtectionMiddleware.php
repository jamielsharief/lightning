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

namespace Lightning\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Lightning\Http\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Http\Exception\ForbiddenException;

/**
 * CsrfProtectionMiddleware using the Synchronizer Token Pattern, each token is generated per session.
 * Ensure that your session cookies samesite is at least set to lax for best results.
 *
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html
 */
class CsrfProtectionMiddleware implements MiddlewareInterface
{
    private SessionInterface $session;

    private string $header = 'X-CSRF-Token';
    private string $formField = 'csrfToken'; // _csrf

    private const TOKEN_BYTES = 16;
    private const SESSION_KEY = 'csrfTokens';

    /**
     * The maximum number of tokens to keep track off
     *
     * @var integer
     */
    private int $maxTokens = 25;

    /**
     * Remove the token from the list once it has been used.
     *
     * @var boolean
     */
    private bool $singleUseToken = true;

    /**
     * Constructor
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Set maximum tokens to keep track off
     *
     * @param integer $tokens
     * @return self
     */
    public function setMaxTokens(int $tokens): self
    {
        $this->maxTokens = $tokens;

        return $this;
    }

    /**
     * Get the max tokens number
     *
     * @return integer
     */
    public function getMaxTokens(): int
    {
        return $this->maxTokens;
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
        // If changing state validate TOKEN first
        if (in_array($request->getMethod(), ['POST','PATCH','PUT','DELETE'], true)) {
            $this->validateRequest($request);
        }

        return $handler->handle($request->withAttribute('csrfToken', $this->generateCsrfToken()));
    }

    /**
     * By default a token can be used once, this may result in usability issues.
     * If so you can remove there.
     *
     * @return static
     */
    public function disableSingleUseTokens(): self
    {
        $this->singleUseToken = false;

        return $this;
    }

    /**
     * Validates the
     *
     * @param ServerRequestInterface $request
     * @return void
     */
    private function validateRequest(ServerRequestInterface $request): void
    {
        // Extract token
        $requestToken = $this->getTokenFromRequest($request);
        if (! $requestToken) {
            throw new ForbiddenException('Missing CSRF Token');
        }

        // Validate input first
        if (! (bool) preg_match('/^[0-9a-f]{' . (self::TOKEN_BYTES * 2) .'}+$/', $requestToken)) {
            throw new ForbiddenException('Invalid CSRF Token');
        }

        $tokens = $this->session->get(self::SESSION_KEY, []);

        /**
         * The == and === are subject to timing attacks, and using a secret as an index in array could expose to
         * a cache timing attack. The array search is key lookup but needs to pass through hash equals to so that
         * each character can be checked even if its wrong to mitigate these types of attacks.
         */
        $key = array_search($requestToken, $tokens);
        if ($key === false || ! hash_equals($tokens[$key], $requestToken)) {
            throw new ForbiddenException('Invalid CSRF Token');
        }

        if ($this->singleUseToken) {
            unset($tokens[$key]);
        }

        $this->session->set(self::SESSION_KEY, $tokens);
    }

    /**
     * Generate the CSRF Token and store in session
     *
     * @return string
     */
    private function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_BYTES));

        $tokens = $this->session->get(self::SESSION_KEY, []);
        $tokens[] = $token;
        if (count($tokens) > $this->maxTokens) {
            array_shift($tokens);
        }
        $this->session->set(self::SESSION_KEY, $tokens);

        return $token;
    }

    /**
     * Gets the CSRF Token from the Request
     * @internal pay attention to empty strings
     *
     * @param ServerRequestInterface $request
     * @return string|null
     */
    private function getTokenFromRequest(ServerRequestInterface $request): ?string
    {
        $body = $request->getParsedBody();
        $token = $body[$this->formField] ?? $request->getHeaderLine($this->header) ?: '';

        return $token !== '' ? $token : null;
    }

    /**
     * Get the value of header
     *
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * Set the value of header
     *
     * @param string $header
     * @return static
     */
    public function setHeader(string $header): self
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get the value of formField
     *
     * @return string
     */
    public function getFormField(): string
    {
        return $this->formField;
    }

    /**
     * Set the value of formField
     *
     * @param string $formField
     * @return static
     */
    public function setFormField(string $formField): self
    {
        $this->formField = $formField;

        return $this;
    }
}
