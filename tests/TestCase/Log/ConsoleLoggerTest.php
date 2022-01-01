<?php declare(strict_types=1);

namespace Lightning\Test\Logger;

use Psr\Log\LogLevel;
use PHPUnit\Framework\TestCase;
use Lightning\Log\ConsoleLogger;

final class ConsoleLoggerTest extends TestCase
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
    public function testLogLevel($level)
    {
        $temp = fopen('php://temp', 'rw');
        $logger = new ConsoleLogger($temp);

        $loremipsum = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua';
        $logger->$level($loremipsum) ;

        rewind($temp);
        $buffer = fgets($temp);

        $this->assertStringStartsWith("\033", $buffer);
        $this->assertStringContainsString($loremipsum ."\033[0", $buffer);
    }
}
