<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\Controller\TestApp;

use Psr\Log\LogLevel;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Lightning\Controller\AbstractController as BaseController;
use Lightning\Controller\Event\AbstractControllerStoppableEvent;

class ArticlesController extends BaseController
{
    protected array $stopEvents = [];
    protected bool $hookWasCalled = false;

    public function stopEvent(string $class): self
    {
        $this->stopEvents[] = $class;

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
     * Dispatches an Event using the PSR-14: Event Dispatcher if available
     *
     * @param object $event
     * @return object|null
     */
    protected function dispatchEvent(object $event): ?object
    {
        $result = parent::dispatchEvent($event);

        if ($result instanceof AbstractControllerStoppableEvent && in_array(get_class($event), $this->stopEvents)) {
            $result->stop();
        }

        return $result;
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

    protected function setRedirectResponse()
    {
        $this->response = new Response(302);
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
