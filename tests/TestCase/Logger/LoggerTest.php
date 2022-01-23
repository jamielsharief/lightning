<?php declare(strict_types=1);

namespace Lightning\Test\Logger;

use Psr\Log\LogLevel;
use Lightning\Logger\Logger;
use PHPUnit\Framework\TestCase;
use Lightning\TestSuite\TestLogger;

final class LoggerTest extends TestCase
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
    public function testLog(string $level)
    {
        $testLogger = new TestLogger();

        $logger = new Logger([$testLogger]);

        $logger->$level('testLog was run');
        $this->assertTrue($testLogger->logContains('testLog was run', $level));
    }

    /**
     * @depends testLog
     */
    public function testPushLog()
    {
        $testLogger = new TestLogger();

        $logger = new Logger();
        $this->assertInstanceOf(Logger::class, $logger->pushLogger($testLogger));

        $logger->log(LogLevel::DEBUG, 'testLog was run');
        $this->assertTrue($testLogger->logContains('testLog was run', LogLevel::DEBUG));
    }
}
