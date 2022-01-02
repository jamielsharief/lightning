<?php declare(strict_types=1);

namespace Lightning\Test\Logger;

use Lightning\Logger\FileLogger;
use PHPUnit\Framework\TestCase;

final class FileLoggerTest extends TestCase
{
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
