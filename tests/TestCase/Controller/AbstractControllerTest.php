<?php declare(strict_types=1);

namespace Lightning\Test\Controller;

use Psr\Log\LogLevel;

use Lightning\Logger\Logger;
use InvalidArgumentException;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Lightning\TestSuite\TestLogger;

use Lightning\Event\EventDispatcher;
use Lightning\TestSuite\TestEventDispatcher;
use Lightning\Controller\Event\InitializeEvent;
use Lightning\TemplateRenderer\TemplateRenderer;
use Psr\EventDispatcher\EventDispatcherInterface;
use Lightning\Test\TestCase\Controller\TestApp\ArticlesController;

class TestEvent
{
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

    public function testSetEventDispatcher(): void
    {
        $this->assertInstanceOf(
            ArticlesController::class, $this->createController()->setEventDispatcher(new EventDispatcher())
        );

        $this->assertInstanceOf(
            ArticlesController::class, $this->createController()->setEventDispatcher(null)
        );
    }

    public function testGetEventDispatcher(): void
    {
        $controller = $this->createController();
        $eventDispatcher = new EventDispatcher();

        $this->assertNull($controller->getEventDispatcher());

        $controller->setEventDispatcher($eventDispatcher);

        $this->assertEquals(
            $eventDispatcher, $controller->getEventDispatcher()
        );
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

    public function testDispatch(): void
    {
        $controller = $this->createController();
        $eventDispatcher = new TestEventDispatcher();

        $controller->setEventDispatcher(null);
        $event = new TestEvent();

        $this->assertNull($controller->dispatchEvent($event));
        $this->assertFalse($eventDispatcher->hasDispatchedEvent(TestEvent::class));

        $controller->setEventDispatcher($eventDispatcher);
        $this->assertEquals($event, $controller->dispatchEvent($event));

        $this->assertTrue($eventDispatcher->hasDispatchedEvent(TestEvent::class));
    }

    public function testDispatchInitialize(): void
    {
        $request = new ServerRequest('GET', '/articles/index');
        $eventDispatcher = new TestEventDispatcher();

        $controller = $this->createController($eventDispatcher);

        $this->assertTrue($eventDispatcher->hasDispatchedEvent(InitializeEvent::class));
    }

    public function testSetLogger(): void
    {
        $this->assertInstanceOf(
            ArticlesController::class, $this->createController()->setLogger(new Logger())
        );
        $this->assertInstanceOf(
            ArticlesController::class, $this->createController()->setLogger(null)
        );
    }

    public function testGetLogger(): void
    {
        $controller = $this->createController();
        $logger = new Logger();

        $this->assertNull($controller->getLogger());

        $controller->setLogger($logger);

        $this->assertEquals(
            $logger, $controller->getLogger()
        );
    }

    public function testLog(): void
    {
        $controller = $this->createController();
        $logger = new TestLogger();

        $controller->setLogger(null);
        $controller->log(LogLevel::DEBUG, 'this is a test');
        $this->assertFalse($logger->hasMessage('this is a test', LogLevel::DEBUG));

        $controller->setLogger($logger);
        $controller->log(LogLevel::DEBUG, 'this is a test');

        $this->assertTrue($logger->hasMessage('this is a test', LogLevel::DEBUG));
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

    private function createController(?EventDispatcherInterface $eventDispatcher = null): ArticlesController
    {
        $path = __DIR__ .'/TestApp/templates';

        return new ArticlesController(
            new TemplateRenderer($path, sys_get_temp_dir()),
            $eventDispatcher
        );
    }
}
