<?php declare(strict_types=1);

namespace Lightning\Test\Controller\Event;

use Lightning\View\View;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Lightning\View\ViewCompiler;
use Psr\Http\Message\ResponseInterface;
use Lightning\Controller\AbstractController;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Controller\Event\BeforeFilterEvent;
use Lightning\Test\TestCase\Controller\TestApp\ArticlesController;

final class BeforeFilterEventTest extends TestCase
{
    public function testGetRequest(): void
    {
        $this->assertInstanceOf(ServerRequestInterface::class, $this->createEvent()->getRequest());
    }

    public function testGetResponse(): void
    {
        $this->assertInstanceOf(ResponseInterface::class, $this->createEvent()->getResponse());
    }

    private function createEvent(): BeforeFilterEvent
    {
        $request = new ServerRequest('GET', '/not-relevant');
        $response = new Response(302);

        return new BeforeFilterEvent($this->createController(), $request, $response);
    }

    private function createController(): AbstractController
    {
        $path = __DIR__ .'/TestApp/templates';

        return  new ArticlesController(
            new Response(), new View(new ViewCompiler($path, sys_get_temp_dir()), $path)
        );
    }
}
