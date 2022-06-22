<?php declare(strict_types=1);

namespace Lightning\Test\Logger;

use Psr\Log\LogLevel;
use Lightning\Logger\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Lightning\Logger\Handler\FileHandler;

final class LoggerTest extends TestCase
{
    public function testAddHandler(): void
    {
        $handler = new FileHandler(sys_get_temp_dir() . '/' . uniqid());
        $logger = new Logger('app');

        $this->assertCount(0, $logger->getHandlers());
        $this->assertCount(1, $logger->addHandler($handler)->getHandlers());
    }

    public function testRemoveHandler(): void
    {
        $handler = new FileHandler(sys_get_temp_dir() . '/' . uniqid());
        $logger = new Logger('app');

        $this->assertCount(1, $logger->addHandler($handler)->getHandlers());
        $this->assertCount(0, $logger->removeHandler($handler)->getHandlers());
    }

    public function testGetHandlers(): void
    {
        $handler = new FileHandler(sys_get_temp_dir() . '/' . uniqid());
        $logger = new Logger('app');
        $this->assertCount(0, $logger->getHandlers());

        $logger->addHandler($handler);
        $this->assertCount(1, $logger->getHandlers());
    }

    public function testInvalidLogLevel(): void
    {
        $handler = new FileHandler(sys_get_temp_dir() . '/' . uniqid());
        $logger = new Logger('app');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid log level `foo`');

        $logger->log('foo', 'test');
    }

    public function testLog(): void
    {
        $temp = sys_get_temp_dir() . '/' . uniqid();

        $logger = new Logger('app');
        $logger->addHandler(new FileHandler($temp));

        $logger->log(LogLevel::WARNING, '`{class}` has been deprecated', ['class' => 'foo']);

        $this->assertFileExists($temp);
        $this->assertStringContainsString('app WARNING: `foo` has been deprecated', file_get_contents($temp));
    }

    public function testIsHandled(): void
    {
        $temp = sys_get_temp_dir() . '/' . uniqid();

        $logger = new Logger('app');
        $logger->addHandler(new FileHandler($temp, LogLevel::ERROR));

        $logger->log(LogLevel::WARNING, '`{class}` has been deprecated', ['class' => 'foo']);
        $logger->log(LogLevel::ERROR, 'No gold down there');

        $this->assertFileExists($temp);
        $this->assertStringNotContainsString('app WARNING: `foo` has been deprecated', file_get_contents($temp));
        $this->assertStringContainsString('app ERROR: No gold down there', file_get_contents($temp));
    }
}
