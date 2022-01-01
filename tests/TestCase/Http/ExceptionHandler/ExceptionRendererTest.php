<?php declare(strict_types=1);

namespace Lightning\Test\Http\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Lightning\Http\ExceptionHandler\ErrorRenderer;

final class ExceptionRendererTest extends TestCase
{
    public function testJson(): void
    {
        $this->assertEquals(
            '{"error":{"code":404,"message":"not found"}}',
            (new ErrorRenderer())->json('not found', 404)
        );
    }

    public function testXml(): void
    {
        $expected = <<< XML
        <?xml version="1.0" encoding="UTF-8"?>
        <error>
           <code>404</code>
           <message>not found</message>
        </error>
        XML;
        $this->assertEquals(
            $expected,
            (new ErrorRenderer())->xml('not found', 404)
        );
    }

    public function testHtml(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->assertEquals(
            '{"error":{"code":404,"message":"not found","hasRequest":true,"hasException":true}}',
            (new ErrorRenderer())->html(__DIR__ .'/template/error400.php', 'not found', 404, $request, new Exception())
        );
    }
}
