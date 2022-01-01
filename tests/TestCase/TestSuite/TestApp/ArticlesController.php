<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\TestSuite\TestApp;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticlesController
{
    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->response
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);

        $response->getBody()->write('<h1>Articles</h1>');

        return $response;
    }

    public function search(ServerRequestInterface $request): ResponseInterface
    {
        throw new Exception('Not Implemented', 501);
    }

    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->response
            ->withHeader('Location', 'https://localhost/login')
            ->withStatus(302);

        return $response;
    }

    public function fileUploads(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);

        $files = [];
        foreach ($request->getUploadedFiles() as $file) {
            $files[] = $file->getClientFilename();
        }
        $response->getBody()->write(json_encode($files));

        return $response;
    }
}
