<?php declare(strict_types=1);

namespace Lightning\Test\Autoloader;

use PHPUnit\Framework\TestCase;
use Lightning\Autoloader\Autoloader;

class MockAutoloader extends Autoloader
{
    protected array $files = [];

    public function setFiles(array $files): void
    {
        $this->files = $files;
        $this->sortPrefixes();
    }

    protected function requireFile(string $path): bool
    {
        return in_array($path, $this->files);
    }

    public function getPrefixes()
    {
        return $this->prefixes;
    }

    public function unregister(): bool
    {
        return spl_autoload_unregister([$this, 'loadClass']);
    }
}

final class AutoloaderTest extends TestCase
{
    public function testLoadFile(): void
    {
        $autoloader = new MockAutoloader('/some-directory');

        $autoloader->addNamespace('Foo', 'src');

        $autoloader->setFiles([
            '/some-directory/src/SimpleClass.php'
        ]);

        $this->assertTrue($autoloader->loadClass('Foo\SimpleClass'));
    }

    public function testMissingFile(): void
    {
        $autoloader = new MockAutoloader('/some-directory');

        $autoloader->addNamespace('Foo', 'src');

        $autoloader->setFiles([
            '/some-directory/src/SimpleClass.php'
        ]);

        $this->assertFalse($autoloader->loadClass('Foo\Bar'));
    }

    public function testLoadFileDeep(): void
    {
        $autoloader = new MockAutoloader('/some-directory');

        $autoloader->addNamespace('Foo', 'src');

        $autoloader->setFiles([
            '/some-directory/src/Subfolder/SimpleClass.php'
        ]);

        $this->assertTrue($autoloader->loadClass('Foo\Subfolder\SimpleClass'));
    }

    public function testRegisterSortsPrefixes(): void
    {
        $autoloader = new MockAutoloader('/some-directory');

        $autoloader->addNamespaces([
            'Foo' => 'src',
            'Foo\\Test' => 'tests',
        ]);

        $this->assertTrue($autoloader->register());
        $this->assertEquals([
            'Foo\Test\\' => '/some-directory/tests/',
            'Foo\\' => '/some-directory/src/'
        ], $autoloader->getPrefixes());

        $autoloader->unregister();
    }

    public function testLoadingFromMultipleNamespaces(): void
    {
        $autoloader = new MockAutoloader('/some-directory');

        $autoloader->addNamespaces([
            'Foo' => 'src',
            'Foo\\Test' => 'tests',
        ]);

        $autoloader->setFiles([
            '/some-directory/src/SimpleClass.php',
            '/some-directory/src/Controllers/ArticlesController.php',
            '/some-directory/tests/UnitTestClass.php',
        ]);

        $this->assertTrue($autoloader->loadClass('Foo\SimpleClass'));
        $this->assertTrue($autoloader->loadClass('Foo\Controllers\ArticlesController'));
        $this->assertTrue($autoloader->loadClass('Foo\Test\UnitTestClass'));

        $this->assertFalse($autoloader->loadClass('Foo\PostsController'));
        $this->assertFalse($autoloader->loadClass('Bar\SimpleClass'));
        $this->assertFalse($autoloader->loadClass('Foo\Test\TestCase\UnitTestClass'));
    }

    // for code coverage
    public function testRequireFileNotFound(): void
    {
        $autoloader = new Autoloader(getcwd());
        $autoloader->addNamespaces([
            'Lightning' => 'src',
        ]);

        $this->assertFalse($autoloader->loadClass('Lightning\Foo'));
    }
}
