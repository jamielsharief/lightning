<?php declare(strict_types=1);

namespace Lightning\Test\TestSuite;

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

    # # # Tests that require dispatching

    public function testResponseErrorHandling(): void
    {
        $this->sendRequest('GET', '/foo');
        $this->assertResponseCode(404);
    }
}
