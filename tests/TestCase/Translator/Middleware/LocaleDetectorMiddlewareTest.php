<?php declare(strict_types=1);

namespace Lightning\Test\Translator;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Lightning\TestSuite\TestRequestHandler;
use Lightning\Translator\Middleware\LocaleDetectorMiddleware;

final class LocaleDetectorMiddlewareTest extends TestCase
{
    public function testSetLocale(): void
    {
        $handler = new TestRequestHandler(new LocaleDetectorMiddleware('en_US'), new Response());

        $request = new ServerRequest('GET', '/home', [
            'Accept-Language' => 'en-GB,en-US;q=0.9,en;q=0.8'
        ]);

        $handler->dispatch($request);

        $request = $handler->getRequest();
        $this->assertEquals('en_GB', $request->getAttribute('locale'));
    }

    public function testSetLocaleAllowed(): void
    {
        $handler = new TestRequestHandler(new LocaleDetectorMiddleware('en_US', ['en_GB']), new Response());

        $request = new ServerRequest('GET', '/home', [
            'Accept-Language' => 'en-GB,en-US;q=0.9,en;q=0.8'
        ]);

        $handler->dispatch($request);

        $request = $handler->getRequest();
        $this->assertEquals('en_GB', $request->getAttribute('locale'));
    }

    public function testSetLocaleNotAllowed(): void
    {
        $handler = new TestRequestHandler(new LocaleDetectorMiddleware('en_US', ['es_AR','es_MX','es_ES']), new Response());

        $request = new ServerRequest('GET', '/home', [
            'Accept-Language' => 'en-GB,en-US;q=0.9,en;q=0.8'
        ]);

        $handler->dispatch($request);

        $request = $handler->getRequest();
        $this->assertEquals('en_US', $request->getAttribute('locale'));
    }
}
