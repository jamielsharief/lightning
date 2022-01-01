<?php declare(strict_types=1);

namespace Lightning\Test\Console;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Lightning\Console\ConsoleArgumentParser;

class MockConsoleArgumentParser extends ConsoleArgumentParser
{
    protected array $commandOptions = []; // Remove default options, help, quiet etc.
}

final class ConsoleArgumentParserTest extends TestCase
{
    public function testUnkownOption()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown option `foo`');

        $parser = new MockConsoleArgumentParser();
        $parser->parse(['--foo'])['options'];
    }

    public function testBool()
    {
        $parser = new MockConsoleArgumentParser();
        $parser->addOption('foo', [
            'type' => 'boolean'
        ]);
        $result = $parser->parse(['--foo']);

        $this->assertEquals(
            ['foo' => true],
           $result->getOptions()
        );
    }

    public function testBoolNotSupplied()
    {
        $parser = new MockConsoleArgumentParser();
        $parser->addOption('foo', [
            'type' => 'boolean'
        ]);
        $result = $parser->parse([]);

        $this->assertEquals(
            ['foo' => false],
            $result->getOptions()
        );
    }

    public function testString()
    {
        $parser = new MockConsoleArgumentParser();
        $parser->addOption('connection', [
            'type' => 'string'
        ]);
        $result = $parser->parse(['--connection=mysql']);

        $this->assertEquals(
            ['connection' => 'mysql'],
            $result->getOptions()
        );
    }

    public function testInteger()
    {
        $parser = new MockConsoleArgumentParser();
        $parser->addOption('port', [
            'type' => 'integer'
        ]);
        $result = $parser->parse(['--port=3306']);

        $this->assertEquals(
            ['port' => 3306],
            $result->getOptions()
        );
    }

    public function testIntegerTwoArgs()
    {
        $parser = new MockConsoleArgumentParser();
        $parser->addOption('port', [
            'type' => 'integer'
        ]);
        $result = $parser->parse(['--port','3306']);

        $this->assertEquals(
            ['port' => 3306],
            $result->getOptions()
        );
    }

    public function testIntegerUseDefault()
    {
        $parser = new MockConsoleArgumentParser();
        $parser->addOption('port', [
            'type' => 'integer',
            'default' => 3306
        ]);
        $result = $parser->parse([]);

        $this->assertEquals(
            ['port' => 3306],
            $result->getOptions()
        );
    }

    public function testShortString()
    {
        $parser = new MockConsoleArgumentParser();
        $parser->addOption('connection', [
            'type' => 'string',
            'short' => 'c'
        ]);
        $result = $parser->parse(['-c=mysql']);

        $this->assertEquals(
            ['connection' => 'mysql'],
            $result->getOptions()
        );
    }

    public function testStringTwoArgs()
    {
        $parser = new MockConsoleArgumentParser();
        $parser->addOption('connection', [
            'type' => 'string'
        ]);
        $result = $parser->parse(['--connection','mysql']);

        $this->assertEquals(
            ['connection' => 'mysql'],
            $result->getOptions()
        );
    }

    public function testStringNoValue()
    {
        $parser = new MockConsoleArgumentParser();
        $parser->addOption('connection', [
            'type' => 'string'
        ]);
        $result = $parser->parse([]);

        $this->assertEquals(
            ['connection' => null],
            $result->getOptions()
        );
    }

    public function testStringUseDefault()
    {
        $parser = new MockConsoleArgumentParser();
        $parser->addOption('connection', [
            'type' => 'string',
            'default' => 'test'
        ]);
        $result = $parser->parse([]);

        $this->assertEquals(
            ['connection' => 'test'],
            $result->getOptions()
        );
    }

    public function testArgumentRequired()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument `connection` is required');

        $parser = new MockConsoleArgumentParser();
        $parser->addArgument('connection', [
            'type' => 'string',
            'required' => true
        ]);
        $parser->parse([]);
    }

    public function testArgumentString()
    {
        $parser = new MockConsoleArgumentParser();
        $parser->addArgument('connection', [
            'type' => 'string',
            'required' => true
        ]);
        $result = $parser->parse(['test']);

        $this->assertEquals(
            ['connection' => 'test'],
            $result->getArguments()
        );
    }

    public function testArgumentInteger()
    {
        $parser = new MockConsoleArgumentParser();
        $parser->addArgument('port', [
            'type' => 'integer'
        ]);
        $result = $parser->parse(['3306']);

        $this->assertEquals(
            ['port' => 3306],
            $result->getArguments()
        );
    }

    public function testArgumentDefault()
    {
        $parser = new MockConsoleArgumentParser();
        $parser->addArgument('port', [
            'type' => 'integer',
            'default' => '3306'
        ]);
        $result = $parser->parse([]);

        $this->assertEquals(
            ['port' => 3306],
            $result->getArguments()
        );
    }
}
