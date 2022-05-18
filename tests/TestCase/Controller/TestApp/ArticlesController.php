<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\Controller\TestApp;

use Lightning\View\View;
use Nyholm\Psr7\Response;
use Lightning\Event\EventDispatcher;
use Psr\Http\Message\ResponseInterface;
use Lightning\Controller\AbstractController as BaseController;

class ArticlesController extends BaseController
{
    public function __construct(View $view, ?EventDispatcher $eventDispatcher = null)
    {
        $this->view = $view;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function addListener(string $event, callable $callable): self
    {
        $this->eventCallables[$event] = $callable;

        return $this;
    }

    public function index(): ResponseInterface
    {
        return $this->render('articles/index', [
            'title' => 'Articles'
        ]);
    }

    public function status($payload, int $statusCode = 200): ResponseInterface
    {
        return $this->renderJson($payload, $statusCode);
    }

    public function old(string $uri): ResponseInterface
    {
        return $this->redirect($uri);
    }

    public function download(string $path, array $options = []): ResponseInterface
    {
        return $this->renderFile($path, $options);
    }

    public function createResponse(): ResponseInterface
    {
        return new Response();
    }
}
