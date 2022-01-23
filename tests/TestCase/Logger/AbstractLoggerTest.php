<?php declare(strict_types=1);

namespace Lightning\Test\Logger;

use Psr\Log\LogLevel;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Lightning\Logger\FileLogger;

final class AbstractLoggerTest extends TestCase
{
    private function generateTempName(): string
    {
        return sys_get_temp_dir() . '/' . uniqid();
    }

    public function logLevelProvider()
    {
        return [
            [LogLevel::DEBUG],
            [LogLevel::INFO],
            [LogLevel::NOTICE],
            [LogLevel::WARNING],
            [LogLevel::ERROR],
            [LogLevel::CRITICAL],
            [LogLevel::ALERT],
            [LogLevel::EMERGENCY]
        ];
    }

    /**
     * @dataProvider logLevelProvider
     */
    public function testLogLevel($level)
    {
        $path = $this->generateTempName();
        $logger = new FileLogger($path);

        $logger->$level('This is a test');

        $this->assertFileContains(strtoupper($level) .  ': This is a test', $path);
    }

    public function testWithLogLevel()
    {
        $path = $this->generateTempName();
        $logger = new FileLogger($path);

        $logger = $logger->withLogLevel(LogLevel::ERROR);

        $logger->alert('Error #1');
        $logger->error('Error #2');
        $logger->warning('Error #3');

        $this->assertFileContains('ALERT: Error #1', $path);
        $this->assertFileContains('ERROR: Error #2', $path);
        $this->assertFileNotContains('WARNING: Error #3', $path);
    }

    public function testWithLogLevelException(): void
    {
        $path = $this->generateTempName();
        $logger = new FileLogger($path);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid log level `foo`');

        $logger->withLogLevel('foo');
    }

    public function testLogInvalidArgument(): void
    {
        $path = $this->generateTempName();
        $logger = new FileLogger($path);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid log level `foo`');

        $logger->log('foo', 'bar');
    }

    public function testWithChannel(): void
    {
        $path = $this->generateTempName();
        $logger = new FileLogger($path);

        $logger = $logger->withChannel('admin');
        $logger->info('Message #1');

        $this->assertFileContains(' admin INFO: Message #1', $path);
    }

    /**
     * @dataProvider logLevelProvider
     */
    public function testLogLevelWithContext($level)
    {
        $path = $this->generateTempName();
        $logger = new FileLogger($path);

        $logger->$level('This is a {action}', ['action' => 'test']);

        $this->assertFileContains(strtoupper($level) .  ': This is a test', $path);
    }

    public function testMinimumLevel()
    {
        $path = $this->generateTempName();
        $logger = (new FileLogger($path))->withLogLevel(LogLevel::ERROR);

        $logger->debug('This is a test');
        $this->assertFileDoesNotExist($path);

        $logger->info('This is a test');
        $this->assertFileDoesNotExist($path);

        $logger->warning('This is a test');
        $this->assertFileDoesNotExist($path);

        $logger->notice('This is a test');
        $this->assertFileDoesNotExist($path);

        $logger->error('This is a test');
        $this->assertFileContains('ERROR: This is a test', $path);
    }

    public function assertFileContains(string $needle, string $path)
    {
        $this->assertStringContainsString($needle, file_get_contents($path));
    }

    public function assertFileNotContains(string $needle, string $path)
    {
        $this->assertStringNotContainsString($needle, file_get_contents($path));
    }
}
