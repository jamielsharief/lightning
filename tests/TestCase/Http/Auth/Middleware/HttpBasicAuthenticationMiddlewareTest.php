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
use Psr\Http\Message\ResponseInterface;
use Lightning\TestSuite\TestRequestHandler;
use Lightning\Test\Fixture\IdentitiesFixture;
use Lightning\Http\Exception\UnauthorizedException;
use Lightning\Http\Auth\IdentityService\PdoIdentityService;
use Lightning\Http\Auth\PasswordHasher\BcryptPasswordHasher;
use Lightning\Http\Auth\Middleware\HttpBasicAuthenticationMiddleware;

final class HttpBasicAuthenticationMiddlewareTest extends TestCase
{
    private PDO $pdo;

    public function setUp(): void
    {
        $pdoFactory = new PdoFactory(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'),true);
        $this->pdo = $pdoFactory->create();

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([
            IdentitiesFixture::class
        ]);
    }

    public function createHttpBasicAuthenticationMiddleware(): HttpBasicAuthenticationMiddleware
    {
        $identityService = (new PdoIdentityService($this->pdo))
            ->setTable('identities')
            ->setIdentifierName('username')
            ->setCredentialName('password');

        return new HttpBasicAuthenticationMiddleware($identityService, new BcryptPasswordHasher(), new Psr17Factory());
    }

    public function testSetGetRealm(): void
    {
        $this->assertEquals('foo', $this->createHttpBasicAuthenticationMiddleware()->setRealm('foo')->getRealm());
    }

    public function testRequiresAuthentication(): void
    {
        $authenticationMiddleware = $this->createHttpBasicAuthenticationMiddleware();

        // test CLI error
        $request = new ServerRequest('GET', '/user/details', [], null, '1.1');
        $response = $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
        $this->assertEquals('Basic realm=""', $response->getHeaderLine('WWW-Authenticate'));

        // test using var
        $request = new ServerRequest('GET', '/user/details', [], null, '1.1', ['SERVER_NAME' => 'localhost']);
        $response = $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
        $this->assertEquals('Basic realm="localhost"', $response->getHeaderLine('WWW-Authenticate'));
    }

    public function testRequiresAuthenticationWithoutChallenge(): void
    {
        $authenticationMiddleware = $this->createHttpBasicAuthenticationMiddleware()->disableChallenge();
        $request = new ServerRequest('GET', '/user/details');

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Unauthorized');

        $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
    }

    public function testDoesNotRequireAuthenticationPublicPaths(): void
    {
        $authenticationMiddleware = $this->createHttpBasicAuthenticationMiddleware()
            ->setPublicPaths(['/user/details']);

        $request = new ServerRequest('GET', '/user/details');

        $response = $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDoesNotRequireAuthenticationPath(): void
    {
        $authenticationMiddleware = $this->createHttpBasicAuthenticationMiddleware()
            ->setPath('/admin');

        $request = new ServerRequest('GET', '/status');

        $response = $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAuthenticate(): void
    {
        $authenticationMiddleware = $this->createHttpBasicAuthenticationMiddleware();
        $requestHandler = new TestRequestHandler($authenticationMiddleware, new Response());

        // test CLI error
        $request = new ServerRequest('GET', '/user/details', [], null, '1.1', ['PHP_AUTH_USER' => 'user1@example.com','PHP_AUTH_PW' => '1234']);
        $response = $authenticationMiddleware->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($requestHandler->getRequest()->getAttribute('identity'));
    }

    public function testAuthenticateInvalidUsername(): void
    {
        $authenticationMiddleware = $this->createHttpBasicAuthenticationMiddleware()->disableChallenge();

        $requestHandler = new TestRequestHandler($authenticationMiddleware, new Response());

        // test CLI error
        $request = new ServerRequest('GET', '/user/details', [], null, '1.1', ['PHP_AUTH_PW' => '1234']);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Unauthorized');

        $authenticationMiddleware->process($request, $requestHandler);
    }

    public function testAuthenticateInvalidPassword(): void
    {
        $authenticationMiddleware = $this->createHttpBasicAuthenticationMiddleware()->disableChallenge();

        $requestHandler = new TestRequestHandler($authenticationMiddleware, new Response());

        // test CLI error
        $request = new ServerRequest('GET', '/user/details', [], null, '1.1', ['PHP_AUTH_USER' => 'user1@example.com']);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Unauthorized');

        $authenticationMiddleware->process($request, $requestHandler);
    }
}
