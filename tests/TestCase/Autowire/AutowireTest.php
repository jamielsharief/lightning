<?php declare(strict_types=1);

namespace Lightning\Test\Autowire;

use Lightning\Autowire\Autowire;
use Lightning\Container\Container;
use Lightning\Autowire\Exception\AutowireException;

class ConstructorWithNoType
{
    public function __construct($foo)
    {
    }
}

class ConstructorWithNoDefaultValue
{
    public function __construct(string $foo)
    {
    }
}

class ConstructorWithDefaultValues
{
    public function __construct(string $bar = 'foo', $foo = 'bar', ?LoggerInterface $logger = null)
    {
    }
}

class ConstructorWithClassThatDoesNotExist
{
    public function __construct(FooBar $bar)
    {
    }
}

abstract class AbstractLogger implements LoggerInterface
{
}

interface LoggerInterface
{
    public function log(string $message): string;
}

class Logger implements LoggerInterface
{
    public function log(string $message): string
    {
        return $message;
    }
}

class Database
{
    private ?Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }
}

class Task
{
    private ?Logger $logger;

    public function setLogger(Logger $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }
}

class Buscuit
{
    public function __construct(string $category = 'sweet')
    {
    }
}

class Sugar
{
    public function __construct($purified = true)
    {
    }
}

final class AutowireTest extends \PHPUnit\Framework\TestCase
{
    public function testClass()
    {
        $autowire = new Autowire();
        $this->assertInstanceOf(Logger::class, $autowire->class(Logger::class));
    }

    public function testClassWithDependencies()
    {
        $autowire = new Autowire();
        $this->assertInstanceOf(Database::class, $autowire->class(Database::class));
    }

    public function testClassUsingBuiltIn()
    {
        $autowire = new Autowire();
        $this->assertInstanceOf(Buscuit::class, $autowire->class(Buscuit::class));
    }

    public function testClassWithDefaultvalues()
    {
        $autowire = new Autowire();
        $this->assertInstanceOf(ConstructorWithDefaultValues::class, $autowire->class(ConstructorWithDefaultValues::class));
    }

    public function testClassWithNoType()
    {
        $autowire = new Autowire();
        $this->assertInstanceOf(Sugar::class, $autowire->class(Sugar::class));
    }

    public function testMethod()
    {
        $autowire = new Autowire();
        $task = new Task();

        $this->assertInstanceOf(Task::class, $autowire->method($task, 'setLogger'));
        $this->assertInstanceOf(Logger::class, $task->getLogger());
    }

    public function testFunction()
    {
        $autowire = new Autowire();
        $this->assertInstanceOf(Logger::class, $autowire->function(function (Logger $logger) {
            return $logger;
        }));
    }

    public function testResolveNamedParams()
    {
        $autowire = new Autowire();
        $this->assertTrue($autowire->function(function (string $foo, ?string $date = null) {
            return $foo === 'bar';
        }, ['bar' => 'foo','foo' => 'bar']));
    }

    public function testResolveServiceParams()
    {
        $autowire = new Autowire();
        $this->assertInstanceOf(Logger::class, $autowire->function(function (LoggerInterface $logger) {
            return $logger;
        }, [LoggerInterface::class => new Logger()]));
    }

    public function testSetContainer(): void
    {
        $autowire = new Autowire();
        $this->assertInstanceOf(Autowire::class, $autowire->setContainer(new Container()));
    }

    public function testClassNotFoundError(): void
    {
        $autowire = new Autowire();
        $this->expectException(AutowireException::class);
        $this->expectExceptionMessage('`Lightning\Test\Autowire\Banana` could not be found');
        $autowire->class(Banana::class);
    }

    public function testClassIsNotIstantiableError(): void
    {
        $autowire = new Autowire();
        $this->expectException(AutowireException::class);
        $this->expectExceptionMessage('Lightning\Test\Autowire\AbstractLogger` is not instantiable');
        $autowire->class(AbstractLogger::class);
    }

    public function testMethodDoesNotExistError(): void
    {
        $autowire = new Autowire();
        $this->expectException(AutowireException::class);
        $this->expectExceptionMessage('`Lightning\Test\Autowire\AutowireTest` does not have the `foo` method');
        $autowire->method($this, 'foo');
    }

    public function testConstructorWithNoTypeError(): void
    {
        $autowire = new Autowire();
        $this->expectException(AutowireException::class);
        $this->expectExceptionMessage('constructor parameter `foo` has no type or default value');
        $autowire->class(ConstructorWithNoType::class);
    }

    public function testConstructorWithNoDefaultValue(): void
    {
        $autowire = new Autowire();
        $this->expectException(AutowireException::class);
        $this->expectExceptionMessage('parameter `foo` has no default value');
        $autowire->class(ConstructorWithNoDefaultValue::class);
    }

    public function testConstructorWithClassThatDoesNoExit(): void
    {
        $autowire = new Autowire();
        $this->expectException(AutowireException::class);
        $this->expectExceptionMessage('Class `Lightning\Test\Autowire\FooBar` not found');
        $autowire->class(ConstructorWithClassThatDoesNotExist::class);
    }
}
