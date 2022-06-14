<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\Controller\TestApp;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Lightning\Controller\AbstractController as BaseController;

class ArticlesController extends BaseController
{
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

    protected function createResponse(): ResponseInterface
    {
        return new Response();
    }

    /**
     * Before render hook
     */
    protected function beforeRender(): ?ResponseInterface
    {
        return null;
    }

    /**
     * After render hook
     */
    protected function afterRender(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    /**
     * Before Redirect hook
     */
    protected function beforeRedirect(string $url): ?ResponseInterface
    {
        return null;
    }

    /**
     * After Direct hook
     */
    protected function afterRedirect(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
}
