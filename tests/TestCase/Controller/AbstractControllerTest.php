<?php declare(strict_types=1);

namespace Lightning\Test\Controller;

use Lightning\View\View;
use Nyholm\Psr7\Response;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Lightning\View\ViewCompiler;
use Lightning\TestSuite\EventTestTrait;
use Lightning\TestSuite\LoggerTestTrait;
use Lightning\TestSuite\TestEventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Lightning\Test\TestCase\Controller\TestApp\ArticlesController;

final class AbstractControllerTest extends TestCase
{
    use EventTestTrait;
    use LoggerTestTrait;

    public function setUp(): void
    {
        $this->setEventDispatcher($this->createEventDispatcher());
        $this->setLogger($this->createLogger());
    }

    public function testRender(): void
    {
        $eventDispatcherStub = new TestEventDispatcher();

        $controller = $this->createController($eventDispatcherStub);

        $response = $controller->index();

        $this->assertEquals('<h1>Articles</h1>', (string) $response->getBody());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEventsDispatched(
            [
                'Lightning\Controller\Event\AfterInitializeEvent',
                'Lightning\Controller\Event\BeforeRenderEvent',
                'Lightning\Controller\Event\AfterRenderEvent'
            ]
        );
    }

    public function testRenderJson(): void
    {
        $eventDispatcherStub = new TestEventDispatcher();
        $controller = $this->createController($eventDispatcherStub);;

        $response = $controller->status(['ok']);

        $this->assertEquals('["ok"]', (string) $response->getBody());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEventsDispatched(
            [
                'Lightning\Controller\Event\AfterInitializeEvent',
                'Lightning\Controller\Event\BeforeRenderEvent',
                'Lightning\Controller\Event\AfterRenderEvent'
            ]
        );
    }

    public function testRedirect(): void
    {
        $controller = $this->createController();

        $response = $controller->old('/articles/new');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/articles/new', $response->getHeaderLine('Location'));
    }

    public function testRedirectEvents(): void
    {
        $eventDispatcherStub = new TestEventDispatcher();

        $controller = $this->createController($eventDispatcherStub);

        $response = $controller->old('/articles/home');
        $this->assertEquals('/articles/home', $response->getHeaderLine('Location'));

        $this->assertEventsDispatched(
            [
                'Lightning\Controller\Event\AfterInitializeEvent',
                'Lightning\Controller\Event\BeforeRedirectEvent'
            ]
        );
    }

    public function testSendFile(): void
    {
        $eventDispatcherStub = new TestEventDispatcher();
        $controller = $this->createController($eventDispatcherStub);

        $path = __DIR__ . '/TestApp/downloads/sample.xml';
        $response = $controller->download($path);

        $this->assertEquals(
            file_get_contents($path), (string) $response->getBody()
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/xml', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('74', $response->getHeaderLine('Content-Length'));
        $this->assertEquals('attachment; filename="sample.xml"', $response->getHeaderLine('Content-Disposition'));

        $this->assertEventsDispatched(
            [
                'Lightning\Controller\Event\AfterInitializeEvent',
                'Lightning\Controller\Event\BeforeRenderEvent',
                'Lightning\Controller\Event\AfterRenderEvent'
            ]
        );
    }

    public function testSendFileWithRelativePath(): void
    {
        $controller = $this->createController();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Path `/var/www/../file` is a relative path');

        $response = $controller->download('/var/www/../file');
    }

    public function testSendFileNoDownload(): void
    {
        $controller = $this->createController();

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

    public function testLogger(): void
    {
        $eventDispatcherStub = new TestEventDispatcher();

        $controller = $this->createController($eventDispatcherStub, $this->getLogger());

        // invoke
        $controller->index();

        // check
        $this->assertLogDebugContains('Lightning\Test\TestCase\Controller\TestApp\ArticlesController::index');
        $this->assertLogCount(1);
    }

    public function testSetGetResponse(): void
    {
        $controller = $this->createController();
        $response = new Response(404, [], 'not found');
        $controller->setResponse($response);

        $this->assertEquals($response, $controller->getResponse());
    }

    private function createController(?EventDispatcherInterface $eventDispatcher = null, ?LoggerInterface $logger = null): ArticlesController
    {
        $path = __DIR__ .'/TestApp/templates';

        return new ArticlesController(
            new Response(),
            new View(new ViewCompiler($path, sys_get_temp_dir()), $path),
            $eventDispatcher,
            $logger
        );
    }
}
