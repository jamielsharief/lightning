<?php declare(strict_types=1);

namespace Lightning\Test\Logger;

use Psr\Log\LogLevel;
use Lightning\Log\FileLogger;
use PHPUnit\Framework\TestCase;

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
        var_dump([
            $path => file_get_contents($path)
        ]);
        $this->assertStringNotContainsString($needle, file_get_contents($path));
    }
}
