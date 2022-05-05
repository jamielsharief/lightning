<?php declare(strict_types=1);

namespace Lightning\Test\View;

use Lightning\View\View;
use PHPUnit\Framework\TestCase;
use Lightning\View\ViewCompiler;
use Lightning\View\Exception\ViewException;

final class ViewTest extends TestCase
{
    private function createView(): View
    {
        $path = sys_get_temp_dir() . '/' . uniqid();
        mkdir($path);

        $compiler = new ViewCompiler($path);

        return new View($compiler, __DIR__ .'/views');
    }

    public function testRender(): void
    {
        $view = $this->createView();

        $this->assertEquals(
            '<p>ok</p>',
            $view->render('index')
        );
    }

    public function testRenderWithData(): void
    {
        $view = $this->createView();

        $this->assertEquals(
            '{"foo":"bar"}',
            $view->render('index', ['data' => ['foo' => 'bar']])
        );
    }

    public function testRenderWithinRender(): void
    {
        $view = $this->createView();

        $this->assertEquals(
            "<h1>holder</h1>\n<p>ok</p>",
            $view->render('holder')
        );
    }

    public function testRenderWithLayout(): void
    {
        $view = $this->createView();

        $this->assertEquals(
            "<!doctype html>\n<html lang=\"en\">\n  <head>\n    <title>Web Application</title>\n  </head>\n  <body>\n    <p>ok</p>  \n  </body>\n</html>",
            $view->withLayout('default')->render('index')
        );
    }

    public function testRenderJson(): void
    {
        $view = $this->createView();

        $this->assertEquals(
            '{"foo":"bar"}',
            $view->renderJson(['foo' => 'bar'])
        );
    }

    public function testRenderViewNotFound(): void
    {
        $view = $this->createView();
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('File `foo.php` not found');

        $view->render('foo');
    }

    public function testRenderWithViewPath(): void
    {
        $view = $this->createView()->withViewPath('/somewhere');

        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('File `index.php` not found');

        $view->render('index');
    }

    public function testRenderWithLayoutPath(): void
    {
        $view = $this->createView()->withLayoutPath('/Layout')->withLayout('default');

        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('File `Layout/default.php` not found');

        $view->render('index');
    }

    public function testSetGetAttribute(): void
    {
        $view = $this->createView();
        $this->assertNull($view->getAttribute('foo'));
        $this->assertEquals('bar', $view->setAttribute('foo', 'bar')->getAttribute('foo'));
    }

    public function testRenderWithAttribute(): void
    {
        $this->assertEquals(
            '{"foo":"bar"}',
            $this->createView()->setAttribute('data', ['foo' => 'bar'])->render('index')
        );

        // Tests attributes are overwritten if param is set the same
        $this->assertEquals(
            '{"name":"jon"}',
            $this->createView()->setAttribute('data', ['foo' => 'bar'])->render('index', ['data' => ['name' => 'jon']])
        );
    }
}
