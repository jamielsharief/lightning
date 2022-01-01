<?php declare(strict_types=1);

namespace Lightning\Http\Session\Middleware;

use Lightning\Http\Session\Session;
use Lightning\Http\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-7 Friendly Sessions
 */
class SessionMiddleware implements MiddlewareInterface
{
    private SessionInterface $session;
    private string $cookieName = 'id';
    private int $timeout = 900; // 15 minutes
    private string $sameSite = 'lax';
    private string $cookiePath = '/';

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $sessionId = $this->getSessionId($request); // Get value from cookie, session id or perhaps JWT token?

        $this->session->start($sessionId);

        $response = $handler->handle($request->withAttribute('session', $this->session));

        $this->session->close(); // close session if still open, user may have destroyed or closed manualy

        return $this->addCookieToResponse($request, $response);
    }

    /**
     * If the session was destroyed, there will be no id, so delete delete delete. If the session ID was regenerated, then
     * the cookie needs to be updated.
     */
    private function addCookieToResponse(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $wasDestroyed = is_null($this->session->getId());
        $cookieValue = $wasDestroyed ? '' : $this->session->getId();
        $cookieExpires = $wasDestroyed ? time() - 3600 : time() + $this->timeout;

        return $response->withAddedHeader(
             'Set-Cookie', $this->createCookieString($cookieValue, $cookieExpires, $request)
         );
    }

    private function getSessionId(ServerRequestInterface $request): ?string
    {
        $cookies = $request->getCookieParams();

        return $cookies[$this->cookieName] ?? null;
    }

    private function createCookieString(string $sessionId, int $expires, ServerRequestInterface $request): string
    {
        return sprintf(
            '%s=%s; expires=%s; path=%s; samesite=%s;%s httponly',
            $this->cookieName,
            $sessionId,
            gmdate(\DateTime::COOKIE, $expires),
            $this->cookiePath,
            $this->sameSite,
            $request->getUri()->getScheme() === 'https' ? ' secure;' : null
        ) ;
    }
}
