<?php declare(strict_types=1);

namespace Lightning\Test\Controller;

use Nyholm\Psr7\Response;
use InvalidArgumentException;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

use Psr\Http\Message\ResponseInterface;
use Lightning\Controller\AbstractController;
use Lightning\TemplateRenderer\TemplateRenderer;
use Lightning\Test\TestCase\Controller\TestApp\ArticlesController;

class ApiController extends AbstractController
{
    private array $called = [];

    public function index(): ResponseInterface
    {
        return $this->render('articles/index', [
            'title' => 'Articles'
        ]);
    }

    public function indexJson(): ResponseInterface
    {
        return $this->renderJson(['status' => 'ok']);
    }

    public function old(): ResponseInterface
    {
        return $this->redirect('/new');
    }

    public function download(): ResponseInterface
    {
        return $this->renderFile(__DIR__ . '/TestApp/downloads/sample.xml');
    }

    public function createResponse(): ResponseInterface
    {
        return new Response();
    }

    protected function initialize(): void
    {
        $this->wasCalled('initialize');
    }

    protected function beforeRender(): ?ResponseInterface
    {
        $this->wasCalled('beforeRender');

        return null;
    }

    protected function afterRender(ResponseInterface $response): ResponseInterface
    {
        $this->wasCalled('afterRender');

        return $response;
    }

    protected function beforeRedirect(string $url): ?ResponseInterface
    {
        $this->wasCalled('beforeRedirect');

        return null;
    }

    protected function afterRedirect(ResponseInterface $response): ResponseInterface
    {
        $this->wasCalled('afterRedirect');

        return $response;
    }

    private function wasCalled(string $method): void
    {
        $this->called[] = $method;
    }

    public function getCalled(): array
    {
        return $this->called;
    }
}

final class AbstractControllerTest extends TestCase
{
    public function testSetRequest(): void
    {
        $controller = $this->createController();
        $this->assertInstanceOf(
            ArticlesController::class,
            $controller->setRequest(new ServerRequest('GET', '/'))
        );
    }

    public function testGetRequest(): void
    {
        $controller = $this->createController();
        $request = new ServerRequest('GET', '/');
        $controller->setRequest($request);
        $this->assertEquals($request, $controller->getRequest());
    }

    public function testGetTemplateRenderer(): void
    {
        $controller = $this->createController();

        $this->assertInstanceOf(TemplateRenderer::class, $this->createController()->getTemplateRenderer());
    }

    public function testSetTemplateRenderer(): void
    {
        $controller = $this->createController();
        $templateRender = $this->createController()->getTemplateRenderer()->withLayout('layouts/foo');

        $this->assertEquals($templateRender, $controller->setTemplateRenderer($templateRender)->getTemplateRenderer());
    }

    public function testRender(): void
    {
        $request = new ServerRequest('GET', '/articles/index');
        $controller = $this->createController()->setRequest($request);

        $response = $controller->index();

        $this->assertEquals('<h1>Articles</h1>', (string) $response->getBody());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderJson(): void
    {
        $request = new ServerRequest('GET', '/articles/index');
        $controller = $this->createController()->setRequest($request);

        $response = $controller->status(['ok']);

        $this->assertEquals('["ok"]', (string) $response->getBody());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRedirect(): void
    {
        $request = new ServerRequest('GET', '/articles/index');
        $controller = $this->createController()->setRequest($request);

        $response = $controller->old('/articles/new');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/articles/new', $response->getHeaderLine('Location'));
    }

    public function testRenderFile(): void
    {
        $request = new ServerRequest('GET', '/articles/index');
        $controller = $this->createController()->setRequest($request);

        $path = __DIR__ . '/TestApp/downloads/sample.xml';
        $response = $controller->download($path);

        $this->assertEquals(
            file_get_contents($path), (string) $response->getBody()
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/xml', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('74', $response->getHeaderLine('Content-Length'));
        $this->assertEquals('attachment; filename="sample.xml"', $response->getHeaderLine('Content-Disposition'));
    }

    public function testSendFileWithRelativePath(): void
    {
        $request = new ServerRequest('GET', '/articles/index');
        $controller = $this->createController()->setRequest($request);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('`/var/www/../file` is a relative path');

        $controller->download('/var/www/../file');
    }

    public function testSendFileDoesNotExist(): void
    {
        $request = new ServerRequest('GET', '/articles/index');
        $controller = $this->createController()->setRequest($request);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('`/somewhere/somefile` does not exist or is not a file');

        $controller->download('/somewhere/somefile');
    }

    public function testSendFileNoDownload(): void
    {
        $request = new ServerRequest('GET', '/articles/index');
        $controller = $this->createController()->setRequest($request);

        $path = __DIR__ . '/TestApp/downloads/sample.xml';
        $response = $controller->download($path, ['download' => false]);

        $this->assertEquals(
            file_get_contents($path), (string) $response->getBody()
        );

        $this->assertEquals('text/xml', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('74', $response->getHeaderLine('Content-Length'));
        $this->assertEmpty($response->getHeaderLine('Content-Disposition'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderHooks(): void
    {
        $controller = new ApiController(new TemplateRenderer(__DIR__ .'/TestApp/templates'));

        $controller->index();

        $this->assertEquals([
            'initialize','beforeRender','afterRender'
        ], $controller->getCalled());
    }

    public function testRenderHooksJson(): void
    {
        $controller = new ApiController(new TemplateRenderer(__DIR__ .'/TestApp/templates'));

        $controller->indexJson();

        $this->assertEquals([
            'initialize','beforeRender','afterRender'
        ], $controller->getCalled());
    }

    public function testRenderHooksFile(): void
    {
        $controller = new ApiController(new TemplateRenderer(__DIR__ .'/TestApp/templates'));

        $controller->download();

        $this->assertEquals([
            'initialize','beforeRender','afterRender'
        ], $controller->getCalled());
    }

    public function testRedirectHooks(): void
    {
        $controller = new ApiController(new TemplateRenderer(__DIR__ .'/TestApp/templates'));

        $controller->old();

        $this->assertEquals([
            'initialize','beforeRedirect','afterRedirect'
        ], $controller->getCalled());
    }

    private function createController(): ArticlesController
    {
        $path = __DIR__ .'/TestApp/templates';

        return new ArticlesController(new TemplateRenderer($path, sys_get_temp_dir()));
    }
}
