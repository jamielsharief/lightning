<?php declare(strict_types=1);

namespace Lightning\Test\Http\Auth\Middleware;

use PDO;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\Fixture\FixtureManager;
use Nyholm\Psr7\Factory\Psr17Factory;
use Lightning\Http\Session\PhpSession;
use Psr\Http\Message\ResponseInterface;

use Lightning\TestSuite\TestRequestHandler;
use Lightning\Http\Session\SessionInterface;
use Lightning\Test\Fixture\IdentitiesFixture;
use Lightning\Http\Exception\UnauthorizedException;
use Lightning\Http\Auth\IdentityService\PdoIdentityService;
use Lightning\Http\Auth\PasswordHasher\BcryptPasswordHasher;
use Lightning\Http\Auth\Middleware\FormAuthenticationMiddleware;

final class FormAuthenticationMiddlewareTest extends TestCase
{
    private PDO $pdo;
    private SessionInterface $session;

    public function setUp(): void
    {
        $pdoFactory = new PdoFactory(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'),true);
        $this->pdo = $pdoFactory->create();

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([
            IdentitiesFixture::class
        ]);

        $this->session = new PhpSession();
        $this->session->start(uniqid());
    }

    public function tearDown(): void
    {
        $this->session->destroy();
    }

    public function createFormAuthenticationMiddleware(): FormAuthenticationMiddleware
    {
        $identityService = (new PdoIdentityService($this->pdo))
            ->setTable('identities')
            ->setIdentifierName('username')
            ->setCredentialName('password');

        return new FormAuthenticationMiddleware($identityService, new BcryptPasswordHasher(), $this->session, new Psr17Factory());
    }

    public function testGetSetPath(): void
    {
        $authenticationMiddleware = $this->createFormAuthenticationMiddleware();
        $this->assertNull($authenticationMiddleware->getPath());
        $this->assertInstanceOf(FormAuthenticationMiddleware::class, $authenticationMiddleware->setPath('/foo'));
        $this->assertEquals('/foo', $authenticationMiddleware->getPath());
    }

    public function testSetGetSessionKey(): void
    {
        $this->assertEquals('foo', $this->createFormAuthenticationMiddleware()->setSessionKey('foo')->getSessionKey());
    }

    public function testSetGetUsernameField(): void
    {
        $this->assertEquals('foo', $this->createFormAuthenticationMiddleware()->setUsernameField('foo')->getUsernameField());
    }

    public function testSetGetPasswordField(): void
    {
        $this->assertEquals('foo', $this->createFormAuthenticationMiddleware()->setPasswordField('foo')->getPasswordField());
    }

    public function testGetSetPublicPaths(): void
    {
        $authenticationMiddleware = $this->createFormAuthenticationMiddleware();
        $this->assertEquals([], $authenticationMiddleware->getPublicPaths());

        $allowed = ['/users/login','/users/logout'];
        $this->assertInstanceOf(FormAuthenticationMiddleware::class, $authenticationMiddleware->setPublicPaths($allowed));
        $this->assertEquals($allowed, $authenticationMiddleware->getPublicPaths());
    }

    public function testGetSetUnauthenticatedRedirect(): void
    {
        $authenticationMiddleware = $this->createFormAuthenticationMiddleware();

        $this->assertNull($authenticationMiddleware->getUnauthenticatedRedirect());
        $this->assertInstanceOf(FormAuthenticationMiddleware::class, $authenticationMiddleware->setUnauthenticatedRedirect('/login'));

        $this->assertEquals('/login', $authenticationMiddleware->getUnauthenticatedRedirect());
    }

    public function testRequiresAuthentication(): void
    {
        $authenticationMiddleware = $this->createFormAuthenticationMiddleware();
        $request = new ServerRequest('GET', '/user/details');

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Unauthorized');

        $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
    }

    public function testRequiresAuthenticationRedirect(): void
    {
        $authenticationMiddleware = $this->createFormAuthenticationMiddleware();
        $authenticationMiddleware->setUnauthenticatedRedirect('/login');
        $request = new ServerRequest('GET', '/user/details');

        $response = $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function testLoginEmptyPassword(): void
    {
        $authenticationMiddleware = $this->createFormAuthenticationMiddleware()->setUsernameField('username');

        $request = new ServerRequest('GET', 'https://www.example.com', []);
        $request = $request->withParsedBody(['username' => 'user1@example.com', 'password' => '']);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Unauthorized');

        $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
    }

    public function testLoginSession(): void
    {
        $authenticationMiddleware = $this->createFormAuthenticationMiddleware()->setUsernameField('username');

        $request = new ServerRequest('POST', 'https://www.example.com/login', []);
        $request = $request->withParsedBody(['username' => 'user1@example.com', 'password' => '1234']);

        $response = $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
        $this->assertEquals(200, $response->getStatusCode());

        // Send a new request without login details
        $request = new ServerRequest('GET', 'https://www.example.com/dashboard', []);
        $response = $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
        $this->assertEquals(200, $response->getStatusCode());

        $identity = $authenticationMiddleware->getIdentityService()->findByIdentifier('user1@example.com');
        $this->assertEquals($identity->toArray(), $authenticationMiddleware->getSession()->get('identity'));
    }

    /**
     * @depends testRequiresAuthentication
     */
    public function testRequiresAuthorizationPublicPaths(): void
    {
        $authenticationMiddleware = $this->createFormAuthenticationMiddleware();
        $authenticationMiddleware->setPublicPaths(['/users/login']);

        $request = new ServerRequest('GET', '/users/login');
        $response = $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $request = new ServerRequest('GET', '/users/details');
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Unauthorized');

        $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
    }

    /**
     * @depends testRequiresAuthentication
     */
    public function testRequiresAuthenticationPath(): void
    {
        $authenticationMiddleware = $this->createFormAuthenticationMiddleware();
        $authenticationMiddleware->setPath('/admin');

        $request = new ServerRequest('GET', '/blog');
        $response = $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $request = new ServerRequest('GET', '/admin/users');
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Unauthorized');

        $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
    }

    public function testLogin(): void
    {
        $authenticationMiddleware = $this->createFormAuthenticationMiddleware()->setUsernameField('username');

        $request = new ServerRequest('POST', 'https://www.example.com/login', []);
        $request = $request->withParsedBody(['username' => 'user1@example.com', 'password' => '1234']);

        $response = $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));

        $this->assertEquals(200, $response->getStatusCode());
    }
}
