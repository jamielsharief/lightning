<?php declare(strict_types=1);

namespace Lightning\Test\Translator;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Lightning\Translator\Translator;
use Lightning\TestSuite\TestRequestHandler;
use Lightning\Translator\MessageLoader\PhpMessageLoader;
use Lightning\Translator\Middleware\TranslatorMiddleware;

final class TranslatorMiddlewareTest extends TestCase
{
    public function testSetLocale(): void
    {
        $translator = new Translator(new PhpMessageLoader(__DIR__ . '/locale'), 'en_US');
        $handler = new TestRequestHandler(new TranslatorMiddleware($translator), new Response());

        $request = new ServerRequest('GET', '/home', [
            'Accept-Language' => 'en-GB,en-US;q=0.9,en;q=0.8'
        ]);

        $handler->dispatch($request);

        $this->assertEquals('en_GB', $translator->getLocale());

        $request = $handler->getRequest();
        $this->assertEquals('en_GB', $request->getAttribute('locale'));
        $this->assertEquals('en', $request->getAttribute('language'));
    }

    public function testSetLocaleFromAllowed(): void
    {
        $translator = new Translator(new PhpMessageLoader(__DIR__ . '/locale'), 'en_US');
        $handler = new TestRequestHandler(new TranslatorMiddleware($translator), new Response());

        $request = new ServerRequest('GET', '/home', [
            'Accept-Language' => 'en-GB,en-US;q=0.9,en;q=0.8'
        ]);

        $handler->dispatch($request);

        $request = $handler->getRequest();
        $request = $handler->getRequest();
        $this->assertEquals('en_GB', $request->getAttribute('locale'));
        $this->assertEquals('en', $request->getAttribute('language'));
    }

    public function testSetLocaleFromAttribute(): void
    {
        $translator = new Translator(new PhpMessageLoader(__DIR__ . '/locale'), 'en_US');
        $handler = new TestRequestHandler(new TranslatorMiddleware($translator), new Response());

        $request = new ServerRequest('GET', '/home');

        $handler->dispatch($request->withAttribute('locale', 'en_GB'));
        $request = $handler->getRequest();

        $this->assertEquals('en_GB', $translator->getLocale());
    }
}
