<?php declare(strict_types=1);

namespace Lightning\Test\Translator;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Lightning\Translator\Translator;
use Lightning\TestSuite\TestRequestHandler;
use Lightning\Translator\MessageLoader\PhpMessageLoader;
use Lightning\Translator\Middleware\LocaleSetterMiddleware;

final class LocaleSetterMiddlewareTest extends TestCase
{
    public function testSetLocale(): void
    {
        $translator = new Translator(new PhpMessageLoader(__DIR__ . '/locale'), 'en_US');
        $handler = new TestRequestHandler(new LocaleSetterMiddleware($translator), new Response());

        $request = new ServerRequest('GET', '/home');

        $handler->dispatch($request->withAttribute('locale', 'en_GB'));

        $this->assertEquals('en_GB', $translator->getLocale());
    }
}
