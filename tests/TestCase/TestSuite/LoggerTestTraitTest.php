<?php declare(strict_types=1);

namespace Lightning\Test\TestSuite;

use Exception;
use Psr\Log\LogLevel;
use PHPUnit\Framework\TestCase;
use Lightning\TestSuite\TestLogger;
use Lightning\TestSuite\LoggerTestTrait;

final class LoggerTestTraitTest extends TestCase
{
    use LoggerTestTrait;

    public function testCreateLogger(): void
    {
        $this->assertInstanceOf(TestLogger::class, $this->createLogger());
    }

    public function testSetLogger(): void
    {
        $this->testLogger = null;
        $this->assertInstanceOf(TestCase::class, $this->setLogger($this->createLogger()));
        $this->assertInstanceOf(TestLogger::class, $this->testLogger);
    }

    public function testGetLogger(): void
    {
        $this->setLogger($this->createLogger());
        $this->assertInstanceOf(TestLogger::class, $this->getLogger());
    }

    public function testGetLoggerException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test Logger is not set');
        $this->assertInstanceOf(TestLogger::class, $this->getLogger());
    }

    public function testAssertLogHasMessage(): void
    {
        $this->setLogger($this->createLogger());
        $this->getLogger()->log(LogLevel::DEBUG, 'This is a test');

        $this->assertLogHasMessage('This is a test', LogLevel::DEBUG);
    }

    public function testAssertLogHasMessageThatContains(): void
    {
        $this->setLogger($this->createLogger());
        $this->getLogger()->log(LogLevel::DEBUG, 'This is a test');

        $this->assertLogHasMessageThatContains('is a test', LogLevel::DEBUG);
    }

    public function testAssertLogHasMessageThatMatches(): void
    {
        $this->setLogger($this->createLogger());
        $this->getLogger()->log(LogLevel::DEBUG, 'This is a test');

        $this->assertLogHasMessageThatMatches('/test/', LogLevel::DEBUG);
    }

    public function testAssertLogDoesNotHaveMessage(): void
    {
        $this->setLogger($this->createLogger());
        $this->getLogger()->log(LogLevel::DEBUG, 'This is a test');

        $this->assertLogDoesNotHaveMessage('foo', LogLevel::DEBUG);
    }

    public function testAssertLogDoesNotHaveMessageThatContains(): void
    {
        $this->setLogger($this->createLogger());
        $this->getLogger()->log(LogLevel::DEBUG, 'This is a test');

        $this->assertLogDoesNotHaveMessageThatContains('foo', LogLevel::DEBUG);
    }

    public function testAssertLogDoesNotHaveMessageThatMatches(): void
    {
        $this->setLogger($this->createLogger());
        $this->getLogger()->log(LogLevel::DEBUG, 'This is a test');

        $this->assertLogDoesNotHaveMessageThatMatches('/foo/', LogLevel::DEBUG);
    }
}
