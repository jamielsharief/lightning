<?php declare(strict_types=1);

namespace Lightning\Test\Controller;

use Lightning\View\View;
use Nyholm\Psr7\Response;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Lightning\View\ViewCompiler;
use Lightning\TestSuite\EventTestTrait;
use Lightning\TestSuite\Stubs\LoggerStub;
use Psr\EventDispatcher\EventDispatcherInterface;
use Lightning\TestSuite\Stubs\EventDispatcherStub;
use Lightning\Test\TestCase\Controller\TestApp\ArticlesController;

final class AbstractControllerTest extends TestCase
{
    use EventTestTrait;

    public function testRender(): void
    {
        $eventDispatcherStub = new EventDispatcherStub();
        $loggerStub = new LoggerStub();
        $controller = $this->createController($eventDispatcherStub, $loggerStub);

        $response = $controller->index();

        $this->assertEquals('<h1>Articles</h1>', (string) $response->getBody());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals(
            [
                'Lightning\Controller\Event\AfterInitializeEvent',
                'Lightning\Controller\Event\BeforeRenderEvent',
                'Lightning\Controller\Event\AfterRenderEvent'
            ],
            $eventDispatcherStub->getDispatchedEvents()
        );
    }

    public function testRenderJson(): void
    {
        $eventDispatcherStub = new EventDispatcherStub();
        $loggerStub = new LoggerStub();
        $controller = $this->createController($eventDispatcherStub, $loggerStub);;

        $response = $controller->status(['ok']);

        $this->assertEquals('["ok"]', (string) $response->getBody());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals(
            [
                'Lightning\Controller\Event\AfterInitializeEvent',
                'Lightning\Controller\Event\BeforeRenderEvent',
                'Lightning\Controller\Event\AfterRenderEvent'
            ],
            $eventDispatcherStub->getDispatchedEvents()
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
        $eventDispatcherStub = new EventDispatcherStub();
        $loggerStub = new LoggerStub();
        $controller = $this->createController($eventDispatcherStub, $loggerStub);

        $response = $controller->old('/articles/home');
        $this->assertEquals('/articles/home', $response->getHeaderLine('Location'));

        $this->assertEquals(
            [
                'Lightning\Controller\Event\AfterInitializeEvent',
                'Lightning\Controller\Event\BeforeRedirectEvent'
            ],
            $eventDispatcherStub->getDispatchedEvents()
        );
    }

    public function testSendFile(): void
    {
        $eventDispatcherStub = new EventDispatcherStub();
        $loggerStub = new LoggerStub();
        $controller = $this->createController($eventDispatcherStub, $loggerStub);

        $path = __DIR__ . '/TestApp/downloads/sample.xml';
        $response = $controller->download($path);

        $this->assertEquals(
            file_get_contents($path), (string) $response->getBody()
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/xml', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('74', $response->getHeaderLine('Content-Length'));
        $this->assertEquals('attachment; filename="sample.xml"', $response->getHeaderLine('Content-Disposition'));

        $this->assertEquals(
            [
                'Lightning\Controller\Event\AfterInitializeEvent',
                'Lightning\Controller\Event\BeforeRenderEvent',
                'Lightning\Controller\Event\AfterRenderEvent'
            ],
            $eventDispatcherStub->getDispatchedEvents()
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
        $eventDispatcherStub = new EventDispatcherStub();
        $loggerStub = new LoggerStub();
        $controller = $this->createController($eventDispatcherStub, $loggerStub);

        // invoke
        $controller->index();

        // check
        $logged = $loggerStub->getLogged();
        $this->assertEquals([
            0 => 'debug', // Level
            1 => 'Lightning\Test\TestCase\Controller\TestApp\ArticlesController::index', // Message
            2 => [
                'action' => 'index'  // Context
            ]
        ], $logged[0]);
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
