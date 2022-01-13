<?php declare(strict_types=1);

namespace Lightning\Test\Http\Exception;

use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Lightning\Http\Emitter\ResponseEmitter;

class MockResponseEmitter extends ResponseEmitter
{
    private array $headersSent = [];

    protected function sendHeader(string $header, bool $replace = false): void
    {
        $this->headersSent[] = $header;
    }

    protected function exit(): void
    {
    }

    public function getHeadersSent(): array
    {
        return $this->headersSent;
    }
}

final class ResponseEmitterTest extends TestCase
{
    public function testEmit(): void
    {
        $response = new Response(200, [], 'This is a test');

        $emitter = new MockResponseEmitter();
        ob_start();
        $emitter->emit($response);
        $result = ob_get_clean();

        $this->assertEquals('This is a test', $result);
        $this->assertEquals(['HTTP/1.1 200 OK'], $emitter->getHeadersSent());
    }

    public function testEmitWithHeader(): void
    {
        $response = new Response(200, [], 'This is a test');

        $response = $response->withHeader('Cache-Control', 'no-cache, must-revalidate');

        $emitter = new MockResponseEmitter();
        ob_start();
        $emitter->emit($response);
        $result = ob_get_clean();

        $this->assertEquals('This is a test', $result);
        $this->assertEquals(['HTTP/1.1 200 OK','Cache-Control: no-cache, must-revalidate'], $emitter->getHeadersSent());
    }

    public function testEmitWithCookies(): void
    {
        $response = new Response(200, [], 'This is a test');

        $response = $response->withAddedHeader('Set-Cookie', 'a=b; path=/')
            ->withAddedHeader('Set-Cookie', 'b=c; path=/');

        $emitter = new MockResponseEmitter();
        ob_start();
        $emitter->emit($response);
        $result = ob_get_clean();
        $this->assertEquals('This is a test', $result);
        $this->assertEquals(['HTTP/1.1 200 OK','Set-Cookie: a=b; path=/','Set-Cookie: b=c; path=/'], $emitter->getHeadersSent());
    }
}
