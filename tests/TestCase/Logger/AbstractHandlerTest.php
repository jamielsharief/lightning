<?php declare(strict_types=1);

namespace Lightning\Test\Logger;

use Psr\Log\LogLevel;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Lightning\Logger\Handler\FileHandler;

final class AbstractHandlerTest extends TestCase
{
    public function testGetLevel(): void
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid();
        $handler = new FileHandler($tmp, LogLevel::ERROR);
        $this->assertEquals(LogLevel::ERROR, $handler->getLogLevel());
    }

    public function testSetLevel(): void
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid();
        $handler = new FileHandler($tmp);
        $this->assertEquals(LogLevel::CRITICAL, $handler->setLogLevel(LogLevel::CRITICAL)->getLogLevel());
    }

    public function testWithLevel(): void
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid();
        $handler = new FileHandler($tmp);

        $this->assertEquals(LogLevel::EMERGENCY, $handler->withLogLevel(LogLevel::EMERGENCY)->getLogLevel());
        $this->assertEquals(LogLevel::DEBUG, $handler->getLogLevel());
    }

    public function testInvalidLogLevel(): void
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid();
        $handler = new FileHandler($tmp);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid log level `foo`');

        $handler->setLogLevel('foo');
    }

    public function testIsHandling(): void
    {
        $handler = new FileHandler(sys_get_temp_dir() . '/' . uniqid());
        $this->assertTrue($handler->isHandling(LogLevel::DEBUG));
        $this->assertTrue($handler->isHandling(LogLevel::EMERGENCY));

        $handler->setLogLevel(LogLevel::ERROR);

        $this->assertFalse($handler->isHandling(LogLevel::DEBUG));
        $this->assertTrue($handler->isHandling(LogLevel::ERROR));
    }
}
