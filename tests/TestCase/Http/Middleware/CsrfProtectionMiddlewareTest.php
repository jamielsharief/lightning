<?php declare(strict_types=1);

namespace Lightning\Test\Http\Middleware;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

use Lightning\Http\Session\PhpSession;

use Lightning\TestSuite\TestRequestHandler;
use Lightning\Http\Session\SessionInterface;
use Lightning\Http\Exception\ForbiddenException;
use Lightning\Http\Middleware\CsrfProtectionMiddleware;

final class CsrfProtectionMiddlewareTest extends TestCase
{
    private SessionInterface $session;

    public function setUp(): void
    {
        $this->session = new PhpSession();
        $this->session->start(uniqid());
    }

    public function tearDown(): void
    {
        $this->session->close(); // TODO: Think about memory version for testing
    }

    private function generateToken(int $bytes = 16): string
    {
        return bin2hex(random_bytes($bytes));
    }

    public function testGetSetHeader(): void
    {
        $middleware = new CsrfProtectionMiddleware($this->session);
        $this->assertEquals('foo', $middleware->setHeader('foo')->getHeader());
    }

    public function testGetSetFormField(): void
    {
        $middleware = new CsrfProtectionMiddleware($this->session);
        $this->assertEquals('foo', $middleware->setFormField('foo')->getFormField());
    }

    public function testGetSetMax(): void
    {
        $middleware = new CsrfProtectionMiddleware($this->session);
        $this->assertEquals(99, $middleware->setMaxTokens(99)->getMaxTokens());
    }

    public function testCsrfTokenGeneration(): void
    {
        $handler = new TestRequestHandler(new CsrfProtectionMiddleware($this->session), new Response());
        $response = $handler->dispatch(new ServerRequest('GET', '/articles'));

        $this->assertEquals(200, $response->getStatusCode());

        $token = $handler->getRequest()->getAttribute('csrfToken');
        $this->assertIsString($token);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}+$/', $token);
        $this->assertEquals([$token], $this->session->get('csrfTokens'));
    }

    public function testForbiddenException(): void
    {
        $handler = new TestRequestHandler(new CsrfProtectionMiddleware($this->session), new Response());

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Missing CSRF Token');

        $handler->dispatch(new ServerRequest('POST', '/articles'));
    }

    public function testInvalidTokenFormat(): void
    {
        $handler = new TestRequestHandler(new CsrfProtectionMiddleware($this->session), new Response());

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Invalid CSRF Token');

        $handler->dispatch((new ServerRequest('POST', '/articles'))->withParsedBody(['csrfToken' => $this->generateToken(32)]));
    }

    public function testUnkownCsrfToken(): void
    {
        $handler = new TestRequestHandler(new CsrfProtectionMiddleware($this->session), new Response());

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Invalid CSRF Token');

        $handler->dispatch((new ServerRequest('POST', '/articles'))->withParsedBody(['csrfToken' => $this->generateToken()]));
    }

    public function testCsrfTokenForm(): void
    {
        $token = $this->generateToken();
        $this->session->set('csrfTokens', [$token]);

        $handler = new TestRequestHandler(new CsrfProtectionMiddleware($this->session), new Response());

        $response = $handler->dispatch((new ServerRequest('POST', '/articles'))->withParsedBody(['csrfToken' => $token]));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCsrfTokenHeader(): void
    {
        $token = $this->generateToken();
        $this->session->set('csrfTokens', [$token]);

        $handler = new TestRequestHandler(new CsrfProtectionMiddleware($this->session), new Response());

        $response = $handler->dispatch((new ServerRequest('POST', '/articles'))->withHeader('X-CSRF-Token', $token));
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * We start with 10, the request will add another one, so that will be 11, check that it is removed
     *
     * @return void
     */
    public function testTokenMaxCount(): void
    {
        $token = $this->generateToken();
        $this->session->set('csrfTokens', [1,2,3,4,5,6,7,8,9,$token]);

        $middleware = (new CsrfProtectionMiddleware($this->session))->setMaxTokens(10);

        $handler = new TestRequestHandler($middleware, new Response());
        $handler->dispatch(new ServerRequest('GET', '/articles'));

        $this->assertCount(10, $this->session->get('csrfTokens'));
    }

    public function testTokensAreSingleUse(): void
    {
        $token = $this->generateToken();
        $this->session->set('csrfTokens', [$token]);

        $handler = new TestRequestHandler(new CsrfProtectionMiddleware($this->session), new Response());
        $response = $handler->dispatch((new ServerRequest('POST', '/articles'))->withParsedBody(['csrfToken' => $token]));
        $this->assertEquals(200, $response->getStatusCode());

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Invalid CSRF Token');

        $handler->dispatch((new ServerRequest('POST', '/articles'))->withParsedBody(['csrfToken' => $token]));
    }

    /**
     * @depends testTokensAreSingleUse
     */
    public function testDisableSingleUse(): void
    {
        $token = $this->generateToken();
        $this->session->set('csrfTokens', [$token]);

        $middleware = (new CsrfProtectionMiddleware($this->session))->disableSingleUseTokens();

        $handler = new TestRequestHandler($middleware, new Response());
        $response = $handler->dispatch((new ServerRequest('POST', '/articles'))->withParsedBody(['csrfToken' => $token]));
        $this->assertEquals(200, $response->getStatusCode());

        $response = $handler->dispatch((new ServerRequest('POST', '/articles'))->withParsedBody(['csrfToken' => $token]));
        $this->assertEquals(200, $response->getStatusCode());
    }
}
