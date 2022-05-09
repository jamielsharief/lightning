<?php declare(strict_types=1);

namespace Lightning\Test\View;

use PHPUnit\Framework\TestCase;
use Lightning\View\ViewCompiler;

final class ViewCompilerTest extends TestCase
{
    private function createCompiler(): ViewCompiler
    {
        $path = sys_get_temp_dir() . '/' . uniqid();
        mkdir($path);

        return new ViewCompiler($path);
    }

    public function testCompile(): void
    {
        $compiler = $this->createCompiler();

        $this->assertEquals(
            "<h1><?= \$this->escape(\$title) ?></h1>\n<p><?= \$this->escape(\$body) ?></p>",
            file_get_contents($compiler->compile(__DIR__ .'/views/escape.php'))
        );
    }
}
