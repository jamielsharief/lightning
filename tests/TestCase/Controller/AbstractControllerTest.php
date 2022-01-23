<?php declare(strict_types=1);

namespace Lightning\Test\Controller;

use Lightning\View\View;
use Nyholm\Psr7\Response;
use InvalidArgumentException;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Lightning\View\ViewCompiler;
use Lightning\TestSuite\TestLogger;
use Psr\Http\Message\ResponseInterface;
use Lightning\TestSuite\LoggerTestTrait;
use Lightning\TestSuite\TestEventDispatcher;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Controller\Event\BeforeFilterEvent;
use Lightning\Controller\Event\BeforeRenderEvent;
use Lightning\TestSuite\EventDispatcherTestTrait;
use Lightning\Controller\Event\BeforeRedirectEvent;
use Lightning\Test\TestCase\Controller\TestApp\ArticlesController;

final class AbstractControllerTest extends TestCase
{
    use EventDispatcherTestTrait;
    use LoggerTestTrait;

    public function setUp(): void
    {
        $this->setEventDispatcher($this->createEventDispatcher());
        $this->setLogger($this->createLogger());
    }

    public function testRender(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());

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

    public function testRenderStopped(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());
        $controller->registerHook('beforeRender', 'stopHook');
        $response = $controller->index();

        $this->assertStringNotContainsString('<h1>Articles</h1>', (string) $response->getBody());
    }

    public function testRenderStoppedWithEvent(): void
    {
        $eventDispatcher = $this->getEventDispatcher();
        $controller = $this->createController($eventDispatcher, $this->getLogger());
        $eventDispatcher->on(BeforeRenderEvent::class, function (BeforeRenderEvent $event) {
            $event->setResponse(new Response(418));
        });
        $response = $controller->index();

        $this->assertEquals(418, $response->getStatusCode());
    }

    public function testStartup(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());

        $request = new ServerRequest('GET', '/articles/index');
        $controller->registerHook('beforeFilter', 'logHook');

        $this->assertNull($controller->startup($request));
        $controller->index();

        $this->assertEventsDispatched(
            [
                'Lightning\Controller\Event\AfterInitializeEvent',
                'Lightning\Controller\Event\BeforeFilterEvent',
                'Lightning\Controller\Event\BeforeRenderEvent',
                'Lightning\Controller\Event\AfterRenderEvent',
            ]
        );
        $this->assertTrue($controller->hookWasCalled());
    }

    public function testStartupEvent(): void
    {
        $eventDispatcher = $this->getEventDispatcher();
        $controller = $this->createController($eventDispatcher, $this->getLogger());
        $eventDispatcher->on(BeforeFilterEvent::class, function (BeforeFilterEvent $event) {
            $event->setResponse(new Response(418));
        });
        $request = new ServerRequest('GET', '/articles/index');

        $this->assertInstanceOf(ResponseInterface::class, $controller->startup($request));;
    }

    public function testStartupHookStopped(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());
        $controller->setResponse(new Response());
        $controller->registerHook('beforeFilter', 'stopHook');
        $request = new ServerRequest('GET', '/articles/index');

        $this->assertInstanceOf(ResponseInterface::class, $controller->startup($request));;
    }

    // public function testRenderStoppedWithEvent(): void
    // {
    //     $eventDispatcher = $this->getEventDispatcher();
    //     $controller = $this->createController($eventDispatcher, $this->getLogger());
    //     $eventDispatcher->on(BeforeRenderEvent::class, function (BeforeRenderEvent $event) {
    //         $event->setResponse(new Response(418));
    //     });
    //     $response = $controller->index();

    //     $this->assertEquals(418, $response->getStatusCode());
    // }

    public function testShutdown(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());

        $controller->registerHook('afterFilter', 'logHook');
        $request = new ServerRequest('GET', '/articles/index');

        $response = $controller->index();
        $this->assertInstanceOf(ResponseInterface::class, $controller->shutdown($request, $response));

        $this->assertEventsDispatched(
            [
                'Lightning\Controller\Event\AfterInitializeEvent',
                'Lightning\Controller\Event\BeforeRenderEvent',
                'Lightning\Controller\Event\AfterRenderEvent',
                'Lightning\Controller\Event\AfterFilterEvent',
            ]
        );

        $this->assertTrue($controller->hookWasCalled());
    }

    public function testShutdownHookStop(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());

        $controller->setResponse(new Response());
        $controller->registerHook('afterFilter', 'logHook');

        $request = new ServerRequest('GET', '/articles/index');

        $this->assertInstanceOf(ResponseInterface::class, $controller->shutdown($request, new Response()));

        $this->assertTrue($controller->hookWasCalled());
    }

    public function testRenderJson(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());;

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

    public function testRenderJsonStopped(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());
        $controller->registerHook('beforeRender', 'stopHook');
        $response = $controller->status(['ok']);

        $this->assertStringNotContainsString('["ok"]', (string) $response->getBody());
    }

    public function testRedirect(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());

        $response = $controller->old('/articles/new');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/articles/new', $response->getHeaderLine('Location'));
    }

    public function testRedirectEventChangedResponse(): void
    {
        $eventDispatcher = $this->getEventDispatcher();
        $controller = $this->createController($eventDispatcher, $this->getLogger());
        $eventDispatcher->on(BeforeRedirectEvent::class, function (BeforeRedirectEvent $event) {
            $event->setResponse(new Response(418));
        });

        $response = $controller->old('/articles/new');
        $this->assertEquals(418, $response->getStatusCode());
    }
    public function testRedirectStopped(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());
        $controller->registerHook('beforeRedirect', 'stopHook');
        $response = $controller->old('/articles/new');

        $this->assertNotEquals(302, $response->getStatusCode());
    }

    public function testRedirectEvents(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());

        $response = $controller->old('/articles/home');
        $this->assertEquals('/articles/home', $response->getHeaderLine('Location'));

        $this->assertEventsDispatched(
            [
                'Lightning\Controller\Event\AfterInitializeEvent',
                'Lightning\Controller\Event\BeforeRedirectEvent'
            ]
        );
    }

    public function testRenderFile(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());

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

    public function testRenderFileStopped(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());
        $controller->registerHook('beforeRender', 'stopHook');

        $path = __DIR__ . '/TestApp/downloads/sample.xml';
        $response = $controller->download($path);

        $this->assertNotEquals('text/xml', $response->getHeaderLine('Content-Type'));
    }

    public function testSendFileWithRelativePath(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('`/var/www/../file` is a relative path');

        $controller->download('/var/www/../file');
    }

    public function testSendFileDoesNotExist(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('`/somewhere/somefile` does not exist or is not a file');

        $controller->download('/somewhere/somefile');
    }

    public function testSendFileNoDownload(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());

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
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());

        // invoke
        $controller->index();

        // check
        $this->assertLogDebugContains('Lightning\Test\TestCase\Controller\TestApp\ArticlesController::index');
        $this->assertLogCount(1);
    }

    public function testSetGetResponse(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());
        $response = new Response(404, [], 'not found');
        $this->assertInstanceOf(ArticlesController::class, $controller->setResponse($response));
        $this->assertInstanceOf(ResponseInterface::class, $controller->getResponse($response));
    }

    public function testSetGetRequest(): void
    {
        $controller = $this->createController($this->getEventDispatcher(), $this->getLogger());
        $request = new ServerRequest('GET', '/');
        $this->assertInstanceOf(ArticlesController::class, $controller->setRequest($request));
        $this->assertInstanceOf(ServerRequestInterface::class, $controller->getRequest($request));
    }

    private function createController(?TestEventDispatcher $eventDispatcher = null, ?TestLogger $logger = null): ArticlesController
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
