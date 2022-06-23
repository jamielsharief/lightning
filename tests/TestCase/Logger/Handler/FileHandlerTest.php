<?php declare(strict_types=1);

namespace Lightning\Test\Logger;

use Psr\Log\LogLevel;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Lightning\Logger\LogMessage;
use Lightning\Logger\Handler\FileHandler;

final class FileHandlerTest extends TestCase
{
    public function testLog(): void
    {
        $path = sys_get_temp_dir() . '/' . uniqid();
        $handler = new FileHandler($path);

        $handler->handle(
            new LogMessage('method `{method}` run', ['method' => 'testLog']),
            LogLevel::ERROR,
            'Application',
            new DateTimeImmutable('2022-01-01 14:00:00'), 'Application'
        ) ;

        $this->assertFileExists($path);
        $this->assertStringContainsString("[2022-01-01 14:00:00] Application ERROR: method `testLog` run\n", file_get_contents($path));
    }
}
