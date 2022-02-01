<?php declare(strict_types=1);

namespace Lightning\Test\View;

use Lightning\View\View;
use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Lightning\View\ViewCompiler;
use Lightning\View\ViewHelperInterface;
use Lightning\View\ViewExtensionInterface;
use Lightning\View\Exception\ViewException;

class DateHelper implements ViewHelperInterface
{
    public function getName(): string
    {
        return 'Date';
    }

    public function format(string $date): string
    {
        return date('d/m/Y', strtotime($date));
    }
}

class DateExtension implements ViewExtensionInterface
{
    public function getMethods(): array
    {
        return [
            'format'
        ];
    }

    public function format(string $date): string
    {
        return date('d/m/Y', strtotime($date));
    }

    public function toTime(string $date): int
    {
        return strtotime($date);
    }
}

final class ViewTest extends TestCase
{
    private function createView(): View
    {
        $path = sys_get_temp_dir() . '/' . uniqid();
        mkdir($path);

        $compiler = new ViewCompiler(__DIR__ .'/views', $path);

        return new View($compiler, __DIR__ .'/views');
    }

    public function testAddExtension(): void
    {
        $view = $this->createView();
        $this->assertInstanceOf(View::class, $view->addExtension(new DateExtension()));
        $this->assertEquals('31/01/2022', $view->format('2022-01-31 16:30'));
    }

    public function testAddHelper(): void
    {
        $view = $this->createView();
        $this->assertInstanceOf(View::class, $view->addHelper(new DateHelper()));
        $this->assertInstanceOf(DateHelper::class, $view->Date);
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

    public function testBadCall(): void
    {
        $view = $this->createView();
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Unkown method `foo`');

        $view->foo();
    }
}
