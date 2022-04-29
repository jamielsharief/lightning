<?php declare(strict_types=1);

namespace Lightning\Http\Session\Middleware;

use Lightning\Http\Cookie\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Lightning\Http\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-7 Friendly Sessions
 *
 * @internal The sameSite setting is a security mehtod and also helps protect against CSRF attacks
 */
class SessionMiddleware implements MiddlewareInterface
{
    private SessionInterface $session;
    private string $cookieName;
    private string $cookiePath;
    private string $sameSite;
    private int $maxAge;

    /**
     * Constructor
     *
     * @param SessionInterface $session
     * @param array $cookieOptions
     */
    public function __construct(SessionInterface $session, array $cookieOptions = [])
    {
        $this->session = $session;

        $this->cookieName = $cookieOptions['name'] ?? 'id';
        $this->cookiePath = $cookieOptions['path'] ?? '/';
        $this->sameSite = $cookieOptions['sameSite'] ?? 'Lax';
        $this->maxAge = $cookieOptions['maxAge'] ?? 900;
    }

    /**
     * Process an incoming server request
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cookies = $request->getCookieParams();
        $sessionId = $cookies[$this->cookieName] ?? null;

        $this->session->start($sessionId);

        $response = $handler->handle($request->withAttribute('session', $this->session));

        $this->session->close(); // close session if still open, user may have destroyed or closed manualy

        $sessionId = $this->session->getId();   // get the ID to ensure the session was not destroyed

        /**
         * If the session was destroyed, there will be no id, so delete delete delete. If the session ID was regenerated, then
         * the cookie needs to be updated.
         */
        $cookie = new Cookie($this->cookieName, $sessionId ?: 'deleted');
        $cookie->setPath($this->cookiePath)
            ->setSameSite($this->sameSite)
            ->setHttpOnly(true)
            ->setSecure($request->getUri()->getScheme() === 'https')
            ->setMaxAge($sessionId ? $this->maxAge : -1);

        return $cookie->addToResponse($response);
    }
}
