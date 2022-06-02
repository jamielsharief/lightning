<?php declare(strict_types=1);

namespace Lightning\Test\TemplateRender;

use PHPUnit\Framework\TestCase;
use Lightning\TemplateRenderer\TemplateRenderer;
use Lightning\TemplateRenderer\Exception\TemplateRendererException;

final class TemplateRendererTest extends TestCase
{
    private string $cachedPath;

    public function setUp(): void
    {
        $this->cachedPath = sys_get_temp_dir() . '/' . uniqid();
        mkdir($this->cachedPath);
    }

    public function testRender(): void
    {
        $templateRenderer = new TemplateRenderer(__DIR__ .'/views', $this->cachedPath);

        $this->assertEquals(
            '<p>ok</p>',
            $templateRenderer->render('index')
        );
    }

    public function testRenderWithData(): void
    {
        $templateRenderer = new TemplateRenderer(__DIR__ .'/views', $this->cachedPath);

        $this->assertEquals(
            '{"foo":"bar"}',
            $templateRenderer->render('index', ['data' => ['foo' => 'bar']])
        );
    }

    public function testSetGetPath(): void
    {
        $templateRenderer = new TemplateRenderer(__DIR__ .'/views', $this->cachedPath);

        $this->assertEquals('/tmp', $templateRenderer->setPath('/tmp')->getPath());
    }

    public function testSetGetLayout(): void
    {
        $templateRenderer = new TemplateRenderer(__DIR__ .'/views', $this->cachedPath);

        $this->assertNull($templateRenderer->getLayout());

        $this->assertEquals('layouts/foo', $templateRenderer->setLayout('layouts/foo')->getLayout());
    }

    public function testRenderWithinRender(): void
    {
        $templateRenderer = new TemplateRenderer(__DIR__ .'/views', $this->cachedPath);

        $this->assertEquals(
            "<h1>holder</h1>\n<p>ok</p>",
            $templateRenderer->render('holder')
        );
    }

    public function testRenderWithLayout(): void
    {
        $templateRenderer = new TemplateRenderer(__DIR__ .'/views', $this->cachedPath);

        $this->assertEquals(
            "<!doctype html>\n<html lang=\"en\">\n  <head>\n    <title>Web Application</title>\n  </head>\n  <body>\n    <p>ok</p>  \n  </body>\n</html>",
            $templateRenderer->withLayout('layouts/default')->render('index')
        );
    }

    public function testRenderViewNotFound(): void
    {
        $templateRenderer = new TemplateRenderer(__DIR__ .'/views', $this->cachedPath);

        $this->expectException(TemplateRendererException::class);
        $this->expectExceptionMessage('File `foo.php` not found');

        $templateRenderer->render('foo');
    }

    public function testRenderWithViewPath(): void
    {
        $templateRenderer = new TemplateRenderer('/nowhere', $this->cachedPath);

        $this->expectException(TemplateRendererException::class);
        $this->expectExceptionMessage('File `index.php` not found');

        $templateRenderer->render('index');
    }

    public function testSetGetAttribute(): void
    {
        $templateRenderer = new TemplateRenderer(__DIR__ .'/views', $this->cachedPath);
        $this->assertNull($templateRenderer->getAttribute('foo'));
        $this->assertEquals('bar', $templateRenderer->setAttribute('foo', 'bar')->getAttribute('foo'));
    }

    public function testRenderWithAttribute(): void
    {
        $templateRenderer = new TemplateRenderer(__DIR__ .'/views', $this->cachedPath);

        $this->assertEquals(
            '{"foo":"bar"}',
            $templateRenderer->setAttribute('data', ['foo' => 'bar'])->render('index')
        );

        $templateRenderer = new TemplateRenderer(__DIR__ .'/views', $this->cachedPath);

        // Tests attributes are overwritten if param is set the same
        $this->assertEquals(
            '{"name":"jon"}',
            $templateRenderer->setAttribute('data', ['foo' => 'bar'])->render('index', ['data' => ['name' => 'jon']])
        );
    }

    public function testCompile(): void
    {
        $templateRenderer = new TemplateRenderer(__DIR__ .'/views', $this->cachedPath);

        $templateRenderer->setAttribute('title', 'Article #1')
            ->setAttribute('body', 'This is an article.');

        $compiledFilename = $this->cachedPath . '/' . md5(__DIR__ .'/views/escape.php') . '.php';

        $templateRenderer->render('escape');

        $this->assertEquals(
            "<h1><?= \$this->escape(\$title) ?></h1>\n<p><?= \$this->escape(\$body) ?></p>",
           file_get_contents($compiledFilename)
        );
    }
}
