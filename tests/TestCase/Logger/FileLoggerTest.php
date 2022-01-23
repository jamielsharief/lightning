<?php declare(strict_types=1);

namespace Lightning\Test\Logger;

use Psr\Log\LogLevel;
use PHPUnit\Framework\TestCase;
use Lightning\Logger\FileLogger;

final class FileLoggerTest extends TestCase
{
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
    public function testLevels(string $level)
    {
        $path = sys_get_temp_dir() . '/' . uniqid();
        $logger = new FileLogger($path);

        $logger->$level('This is a test');

        $this->assertFileExists($path);
        $this->assertStringContainsString(strtoupper($level) .  ': This is a test', file_get_contents($path));
    }

    public function testLog()
    {
        $path = sys_get_temp_dir() . '/' . uniqid();
        $logger = new FileLogger($path);

        $logger->error('This is a test');
        $logger->info('This is another {test}', ['test' => 'test with data']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('ERROR: This is a test', file_get_contents($path));
        $this->assertStringContainsString('INFO: This is another test with data', file_get_contents($path));
    }
}
