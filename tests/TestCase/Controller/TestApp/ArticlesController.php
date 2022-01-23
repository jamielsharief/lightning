<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\Controller\TestApp;

use Psr\Log\LogLevel;
use Psr\Http\Message\ResponseInterface;
use Lightning\Controller\AbstractController as BaseController;

class ArticlesController extends BaseController
{
    protected array $stopEvents = [];
    protected bool $hookWasCalled = false;

    public function addListener(string $event, callable $callable): self
    {
        $this->eventCallables[$event] = $callable;

        return $this;
    }

    public function index(): ResponseInterface
    {
        $this->log(LogLevel::DEBUG, __method__, ['action' => 'index']);

        return $this->render('articles/index', [
            'title' => 'Articles'
        ]);
    }

    public function status($payload, int $statusCode = 200): ResponseInterface
    {
        $this->log(LogLevel::DEBUG, __method__, $payload);

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

    /**
     * Register this as a method to test stop
     *
     * @return boolean
     */
    protected function stopHook(): bool
    {
        return false;
    }

    protected function logHook(): bool
    {
        return $this->hookWasCalled = true;
    }

    public function hookWasCalled(): bool
    {
        return $this->hookWasCalled;
    }
}
