<?php declare(strict_types=1);

namespace Lightning\Test\Http\Auth\Middleware;

use PDO;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;

use Lightning\Database\PdoFactory;
use Lightning\Fixture\FixtureManager;
use Psr\Http\Message\ResponseInterface;
use Lightning\TestSuite\TestRequestHandler;
use Lightning\Test\Fixture\IdentitiesFixture;
use Lightning\Http\Exception\UnauthorizedException;
use Lightning\Http\Auth\IdentityService\PdoIdentityService;
use Lightning\Http\Auth\Middleware\TokenAuthenticationMiddleware;

final class TokenAuthenticationMiddlewareTest extends TestCase
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

    public function createTokenAuthenticationMiddleware(): TokenAuthenticationMiddleware
    {
        $identityService = (new PdoIdentityService($this->pdo))
            ->setTable('identities')
            ->setIdentifierName('password');

        return new TokenAuthenticationMiddleware($identityService);
    }

    public function testSetGetHeader(): void
    {
        $this->assertEquals('foo', $this->createTokenAuthenticationMiddleware()->setHeader('foo')->getHeader('foo'));
    }

    public function testSetGetQueryParam(): void
    {
        $this->assertEquals('foo', $this->createTokenAuthenticationMiddleware()->setQueryParam('foo')->getQueryParam('foo'));
    }

    public function testDoesNotRequireAuthenticationPublicPaths(): void
    {
        $authenticationMiddleware = $this->createTokenAuthenticationMiddleware()
            ->setQueryParam('api_token')
            ->setPublicPaths(['/user/details']);

        $request = new ServerRequest('GET', '/user/details');

        $response = $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDoesNotRequireAuthenticationPath(): void
    {
        $authenticationMiddleware = $this->createTokenAuthenticationMiddleware()
            ->setQueryParam('api_token')
            ->setPath('/admin');

        $request = new ServerRequest('GET', '/status');

        $response = $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRequiresAuthenticationQueryParam(): void
    {
        $authenticationMiddleware = $this->createTokenAuthenticationMiddleware()->setQueryParam('api_token');
        $request = new ServerRequest('GET', '/user/details');

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Unauthorized');

        $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
    }

    public function testAuthenticatedQueryParam(): void
    {
        $authenticationMiddleware = $this->createTokenAuthenticationMiddleware()->setQueryParam('api_token');
        $requestHandler = new TestRequestHandler($authenticationMiddleware, new Response());

        $request = new ServerRequest('GET', '/user/details');
        $request = $request->withQueryParams(['api_token' => 'acbd18db4cc2f85cedef654fccc4a4d8']);

        $response = $authenticationMiddleware->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($requestHandler->getRequest()->getAttribute('identity'));
    }

    public function testRequiresAuthenticationHeader(): void
    {
        $authenticationMiddleware = $this->createTokenAuthenticationMiddleware()->setHeader('X-TOKEN');
        $request = new ServerRequest('GET', '/user/details');

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Unauthorized');

        $authenticationMiddleware->process($request, new TestRequestHandler($authenticationMiddleware, new Response()));
    }

    public function testAuthenticatedHeader(): void
    {
        $authenticationMiddleware = $this->createTokenAuthenticationMiddleware()->setHeader('X-TOKEN');
        $requestHandler = new TestRequestHandler($authenticationMiddleware, new Response());

        $request = new ServerRequest('GET', '/user/details');
        $request = $request->withHeader('X-TOKEN', 'acbd18db4cc2f85cedef654fccc4a4d8');

        $response = $authenticationMiddleware->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($requestHandler->getRequest()->getAttribute('identity'));
    }
}
