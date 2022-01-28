<?php declare(strict_types=1);

namespace Lightning\Test\TestSuite;

use Exception;
use Nyholm\Psr7\Response;
use Lightning\Router\Router;
use Nyholm\Psr7\UploadedFile;
use PHPUnit\Framework\TestCase;
use Lightning\Container\Container;
use Lightning\TestSuite\TestSession;
use Lightning\Router\RouteCollection;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\TestSuite\IntegrationTestTrait;
use Lightning\TestSuite\ServerRequestFactory;
use Lightning\Test\TestCase\TestSuite\TestApp\ArticlesController;

class App implements RequestHandlerInterface
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->router->dispatch($request);
    }
}

final class IntegrationTestTraitTest extends TestCase
{
    use IntegrationTestTrait;

    public function setUp(): void
    {
        // Create DI Container
        $definitions = include dirname(__DIR__, 3) . '/config/services.php';
        $container = new Container($definitions);

        $container
            ->enableAutowiring()
            ->enableAutoConfigure();

        // Setup Routes
        $router = (new Router($container));

        $router->group('/articles', function (RouteCollection $routes) {
            $routes->get('/index', [ArticlesController::class,'index']);
            $routes->get('/search', [ArticlesController::class,'search']);
            $routes->get('/logout', [ArticlesController::class,'logout']);
            $routes->post('/uploads', [ArticlesController::class,'fileUploads']);
        });

        $this->setupIntegrationTesting(
            new ServerRequestFactory(new Psr17Factory()), new Psr17Factory(), new App($router), new TestSession()
        );
    }

    public function testGet(): void
    {
        $this->get('/test-get');
        $request = $this->getRequest();

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/test-get', (string) $request->getUri());
    }

    public function testPost(): void
    {
        $this->post('/test-post', ['foo' => 'bar']);
        $request = $this->getRequest();
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/test-post', (string) $request->getUri());
        $this->assertEquals(['foo' => 'bar'], $request->getParsedBody());
    }

    public function testPatch(): void
    {
        $this->patch('/test-patch', ['foo' => 'bar']);
        $request = $this->getRequest();
        $this->assertEquals('PATCH', $request->getMethod());
        $this->assertEquals('/test-patch', (string) $request->getUri());
        $this->assertEquals(['foo' => 'bar'], $request->getParsedBody());
    }

    public function testPut(): void
    {
        $this->put('/test-put', ['foo' => 'bar']);
        $request = $this->getRequest();
        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('/test-put', (string) $request->getUri());
        $this->assertEquals(['foo' => 'bar'], $request->getParsedBody());
    }

    public function testDelete(): void
    {
        $this->delete('/test-delete');
        $request = $this->getRequest();

        $this->assertEquals('DELETE', $request->getMethod());
        $this->assertEquals('/test-delete', (string) $request->getUri());
    }

    public function testHead(): void
    {
        $this->head('/test-head');
        $request = $this->getRequest();

        $this->assertEquals('HEAD', $request->getMethod());
        $this->assertEquals('/test-head', (string) $request->getUri());
    }

    public function testOptions(): void
    {
        $this->options('/test-options');
        $request = $this->getRequest();

        $this->assertEquals('OPTIONS', $request->getMethod());
        $this->assertEquals('/test-options', (string) $request->getUri());
    }

    private function createResponse(string $body, int $statusCode = 200)
    {
        $this->response = (new Response($statusCode))
            ->withHeader('X-TEST', 'foo')
            ->withAddedHeader('Set-Cookie', 'foo=bar; Expires=' . gmdate('D, d M Y H:i:s T', time() + 3600))
            ->withAddedHeader('Set-Cookie', 'bar=foo; Expires=' . gmdate('D, d M Y H:i:s T', 0));

        $this->response->getBody()->write($body);

        $_SESSION['foo'] = 'bar';
    }

    private function createRedirectResponse(string $url, int $statusCode = 302)
    {
        $this->response = (new Response($statusCode))
            ->withHeader('Location', $url)
            ->withStatus($statusCode);
    }

    public function testResponseOk(): void
    {
        $this->createResponse('<h1>Articles</h1>');
        $this->assertResponseCode(200);
    }

    public function testResponseNotFound(): void
    {
        $this->createResponse('Error', 404);
        $this->assertResponseCode(404);
    }

    public function testHeaderSet(): void
    {
        $this->createResponse('ok');
        $this->assertHeaderSet('X-Test');
    }
    public function testHeaderNotSet(): void
    {
        $this->createResponse('ok');
        $this->assertHeaderNotSet('Username');
    }

    public function testHeaderEquals(): void
    {
        $this->createResponse('ok');
        $this->assertHeaderEquals('X-TEST', 'foo');
    }

    public function testHeaderNotEquals(): void
    {
        $this->createResponse('ok');
        $this->assertHeaderNotEquals('X-TEST', 'bar');
    }

    public function testHeaderContains(): void
    {
        $this->createResponse('ok');
        $this->assertHeaderContains('X-TEST', 'f');
    }

    public function testHeaderNotContains(): void
    {
        $this->createResponse('ok');
        $this->assertHeaderNotContains('X-TEST', 'b');
    }

    public function testSessionHas(): void
    {
        $this->createResponse('ok');
        $this->assertSessionHas('foo');
    }

    public function testSessionNotHas(): void
    {
        $this->createResponse('ok');
        $this->assertSessionDoesNotHave('bar');
    }

    public function testSessionEquals(): void
    {
        $this->createResponse('ok');
        $this->assertSessionEquals('foo', 'bar');
    }

    public function testSessionNotEquals(): void
    {
        $this->createResponse('ok');
        $this->assertSessionNotEquals('foo', 'foo');
    }

    public function testRedirect(): void
    {
        $this->createRedirectResponse('https://localhost/login');
        $this->assertRedirect();
    }

    public function testRedirectEquals(): void
    {
        $this->createRedirectResponse('https://localhost/login');
        $this->assertRedirectEquals('https://localhost/login');
    }

    public function testRedirectNotEquals(): void
    {
        $this->createRedirectResponse('https://localhost/login');
        $this->assertRedirectNotEquals('https://localhost/logout');
    }

    public function testRedirectContains(): void
    {
        $this->createRedirectResponse('https://localhost/login');
        $this->assertRedirectContains('localhost/login');
    }

    public function testRedirectNotContains(): void
    {
        $this->createRedirectResponse('https://localhost/login');
        $this->assertRedirectNotContains('foo');
    }

    public function testNoRedirect(): void
    {
        $this->createResponse('<h1>Articles</h1>');
        $this->assertNoRedirect();
    }

    public function testResponseStatusCode(): void
    {
        $this->createResponse('<h1>Articles</h1>');
        $this->assertResponseCode(200);
    }

    public function testResponseEquals(): void
    {
        $this->createResponse('<h1>Articles</h1>');
        $this->assertResponseEquals('<h1>Articles</h1>');
    }

    public function testResponseNotEquals(): void
    {
        $this->createResponse('<h1>Articles</h1>');
        $this->assertResponseNotEquals('<h1>Users</h1>');
    }

    public function testResponseContains(): void
    {
        $this->createResponse('<h1>Articles</h1>');
        $this->assertResponseContains('Articles');
    }

    public function testResponseNotContains(): void
    {
        $this->createResponse('<h1>Articles</h1>');
        $this->assertResponseNotContains('Users');
    }

    public function testResponseEmpty(): void
    {
        $this->createResponse('');
        $this->assertResponseEmpty();
    }

    public function testResponseNotEmpty(): void
    {
        $this->createResponse('foo');
        $this->assertResponseNotEmpty();
    }

    public function testResponseMatchesRegularExpression(): void
    {
        $this->createResponse('foo');
        $this->assertResponseMatchesRegularExpression('/foo/');
    }

    public function testResponseDoesNotMatchRegularExpression(): void
    {
        $this->createResponse('bar');
        $this->assertResponseDoesNotMatchRegularExpression('/foo/');
    }

    public function testCookieSet(): void
    {
        $this->createResponse('ok');
        $this->assertCookieSet('foo');
    }

    public function testCookieNotSet(): void
    {
        $this->createResponse('ok');
        $this->assertCookieNotSet('id');;
    }

    public function testCookieEquals(): void
    {
        $this->createResponse('ok');
        $this->assertCookieEquals('foo', 'bar');
    }

    public function testCookieNotEquals(): void
    {
        $this->createResponse('ok');
        $this->assertCookieNotEquals('foo', 'foo');
    }

    public function testFileUpload(): void
    {
        $tmpFile = sys_get_temp_dir() . '/' . uniqid();
        copy(__DIR__ . '/Files/sample.txt', $tmpFile);

        $sample = new UploadedFile(
            $tmpFile, 445, UPLOAD_ERR_OK, 'sample.txt', 'text/plain'
        );

        // This will add these to the server request, which is what in the controller you will be using to process the files.
        $this->setUploadedFiles([
            'sample' => $sample
        ]);

        // The data here does not do anything other than appear in the $_POST
        $this->sendRequest('POST', '/articles/uploads', [
            'sample' => [
                'name' => 'sample.txt',
                'type' => 'text/plain',
                'tmp_name' => $tmpFile,
                'size' => 445,
                'error' => UPLOAD_ERR_OK
            ]
        ]);

        $this->assertResponseCode(200);
        $this->assertResponseContains('sample.txt');
    }

    public function testFileDownload(): void
    {
        $this->response = (new Response(200))
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Length', 1234)
            ->withHeader('Content-Disposition', 'attachment; filename=foo.zip');

        $this->response->getBody()->write('fooo');

        $this->assertResponseFile('foo.zip');
    }

    public function testSetServerParams(): void
    {
        $this->setServerParams(['key' => 'value']);
        $response = $this->get('/articles/index');
        $this->assertEquals(['key' => 'value'], $this->getRequest()->getServerParams());
    }

    public function testSetEnv(): void
    {
        $this->setEnvironment(['FOO' => 'bar']);
        $this->get('/articles/index');
        $this->assertEquals('bar', $_ENV['FOO']);
    }

    public function testSetSession(): void
    {
        $this->assertArrayNotHasKey('foo', $_SESSION);

        $this->setSession(['foo' => 'bar']);

        $this->get('/articles/index');
        $this->assertEquals('bar', $_SESSION['foo']);
    }

    public function testSetHeader(): void
    {
        $this->setHeaders(['foo' => 'bar']);

        $this->get('/articles/index');

        $this->assertEquals('bar', $this->getRequest()->getHeaderLine('foo'));
    }

    public function testSetCookies(): void
    {
        $this->setCookieParams(['foo' => 'bar']);

        $this->get('/articles/index');

        $this->assertEquals('bar', $this->getRequest()->getCookieParams()['foo']);
    }

    public function testGetRequest(): void
    {
        $this->get('/articles/index');
        $this->assertInstanceOf(ServerRequestInterface::class, $this->getRequest());
    }

    public function testGetRequestException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Server request not set');
        $this->getRequest();
    }

    public function testGetResponse(): void
    {
        $this->get('/articles/index');
        $this->assertInstanceOf(ResponseInterface::class, $this->getResponse());
    }

    public function testGetResponseException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Response not set');
        $this->getResponse();
    }

    public function testGetSession(): void
    {
        $this->get('/articles/index');
        $this->assertInstanceOf(TestSession::class, $this->getSession());
    }

    public function testGetSessionException(): void
    {
        unset($this->testSession); // this is set when callinjg setup regardless if request is sent

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test Session not set');
        $this->getSession();
    }

    # # # Tests that require dispatching

    public function testResponseErrorHandling(): void
    {
        $this->sendRequest('GET', '/foo');
        $this->assertResponseCode(404);
    }
}
