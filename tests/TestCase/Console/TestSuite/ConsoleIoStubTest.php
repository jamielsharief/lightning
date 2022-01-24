<?php declare(strict_types=1);

namespace Lightning\Test\Console;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use Lightning\Console\TestSuite\TestConsoleIo;

final class TestConsoleIoTest extends TestCase
{
    public function testDefaultOutputMode(): void
    {
        $io = new TestConsoleIo();
        $io->out('<yellow>test</yellow>');
        $this->assertEquals("<yellow>test</yellow>\n", $io->getStdout());
    }

    public function testStdoutNone(): void
    {
        $io = new TestConsoleIo();
        $this->assertEquals('', $io->getStdout());
    }

    public function testStderrNone(): void
    {
        $io = new TestConsoleIo();
        $this->assertEquals(
            '', $io->getStderr()
        );
    }

    public function testStdout(): void
    {
        $io = new TestConsoleIo();
        $io->out('test');
        $this->assertEquals(
            "test\n", $io->getStdout()
        );
    }

    public function testStdoutMulti(): void
    {
        $io = new TestConsoleIo();
        $io->out('foo');
        $io->out('bar');
        $this->assertEquals(
            "foo\nbar\n", $io->getStdout()
        );
    }

    public function testStderr(): void
    {
        $io = new TestConsoleIo();
        $io->out('test');
        $this->assertEquals(
            "test\n", $io->getStdout()
        );
    }

    public function testStderrMulti(): void
    {
        $io = new TestConsoleIo();
        $io->err('foo');
        $io->err('bar');
        $this->assertEquals(
            "foo\nbar\n", $io->getStderr()
        );
    }

    public function testInput(): void
    {
        $io = new TestConsoleIo();

        $io->setInput(['data']);
        $this->assertEquals('data', $io->in());
    }

    public function testInputMulti(): void
    {
        $io = new TestConsoleIo();

        $io->setInput(['one','two','three']);
        $this->assertEquals('one', $io->in());
        $this->assertEquals('two', $io->in());
        $this->assertEquals('three', $io->in());
    }

    public function testInputNone(): void
    {
        $io = new TestConsoleIo();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Console input is requesting more input that what was provided');
        $io->in();
    }
}
