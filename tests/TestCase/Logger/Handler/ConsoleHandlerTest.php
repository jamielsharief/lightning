<?php declare(strict_types=1);

namespace Lightning\Test\Logger;

use Psr\Log\LogLevel;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Lightning\Logger\LogMessage;
use Lightning\Logger\Handler\ConsoleHandler;

final class ConsoleHandlerTest extends TestCase
{
    // public function logLevelProvider()
    // {
    //     return [

    //         [LogLevel::NOTICE],
    //         [LogLevel::WARNING],
    //         [LogLevel::ERROR],
    //         [LogLevel::CRITICAL],
    //         [LogLevel::ALERT],
    //         [LogLevel::EMERGENCY]
    //     ];
    // }

    public function setUp(): void
    {
        $this->stream = fopen('php://temp', 'rw');
    }

    public function tearDown(): void
    {
        fclose($this->stream);
    }

    public function testLogLevelDebug()
    {
        $level = LogLevel::DEBUG;
        $handler = new ConsoleHandler($this->stream);

        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut {foo} et dolore magna aliqua';
        $handler->handle(new LogMessage($message, ['foo' => 'labore']), $level, 'Application', new DateTimeImmutable('2022-01-01 14:00:00')) ;

        $this->assertStreamContains(
            "[0;37m[2022-01-01 14:00:00] Application DEBUG: Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua\033[0m"
        );
    }

    public function testLogLevelInfo()
    {
        $level = LogLevel::INFO;
        $handler = new ConsoleHandler($this->stream);

        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut {foo} et dolore magna aliqua';
        $handler->handle(new LogMessage($message, ['foo' => 'labore']), $level, 'Application', new DateTimeImmutable('2022-01-01 14:00:00')) ;

        $this->assertStreamContains(
            "[0;32m[2022-01-01 14:00:00] Application INFO: Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua\033[0m"
        );
    }

    public function testLogLevelNotice()
    {
        $level = LogLevel::NOTICE;
        $handler = new ConsoleHandler($this->stream);

        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut {foo} et dolore magna aliqua';
        $handler->handle(new LogMessage($message, ['foo' => 'labore']), $level, 'Application', new DateTimeImmutable('2022-01-01 14:00:00')) ;

        $this->assertStreamContains(
            "[0;36m[2022-01-01 14:00:00] Application NOTICE: Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua\033[0m"
        );
    }

    public function testLogLevelWarning()
    {
        $level = LogLevel::WARNING;
        $handler = new ConsoleHandler($this->stream);

        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut {foo} et dolore magna aliqua';
        $handler->handle(new LogMessage($message, ['foo' => 'labore']), $level, 'Application', new DateTimeImmutable('2022-01-01 14:00:00')) ;

        $this->assertStreamContains(
            "[0;33m[2022-01-01 14:00:00] Application WARNING: Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua\033[0m"
        );
    }

    public function testLogLevelError()
    {
        $level = LogLevel::ERROR;
        $handler = new ConsoleHandler($this->stream);

        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut {foo} et dolore magna aliqua';
        $handler->handle(new LogMessage($message, ['foo' => 'labore']), $level, 'Application', new DateTimeImmutable('2022-01-01 14:00:00')) ;

        $this->assertStreamContains(
            "[0;31m[2022-01-01 14:00:00] Application ERROR: Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua\033[0m"
        );
    }

    public function testLogLevelCritical()
    {
        $level = LogLevel::CRITICAL;
        $handler = new ConsoleHandler($this->stream);

        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut {foo} et dolore magna aliqua';
        $handler->handle(new LogMessage($message, ['foo' => 'labore']), $level, 'Application', new DateTimeImmutable('2022-01-01 14:00:00')) ;

        $this->assertStreamContains(
            "[0;91m[2022-01-01 14:00:00] Application CRITICAL: Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua\033[0m"
        );
    }

    public function testLogLevelAlert()
    {
        $level = LogLevel::ALERT;
        $handler = new ConsoleHandler($this->stream);

        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut {foo} et dolore magna aliqua';
        $handler->handle(new LogMessage($message, ['foo' => 'labore']), $level, 'Application', new DateTimeImmutable('2022-01-01 14:00:00')) ;

        $this->assertStreamContains(
            "[0;41;37m[2022-01-01 14:00:00] Application ALERT: Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua\033[0m"
        );
    }

    public function testLogLevelEmergency()
    {
        $level = LogLevel::EMERGENCY;
        $handler = new ConsoleHandler($this->stream);

        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut {foo} et dolore magna aliqua';
        $handler->handle(new LogMessage($message, ['foo' => 'labore']), $level, 'Application', new DateTimeImmutable('2022-01-01 14:00:00')) ;

        $this->assertStreamContains(
            "[0;4;41;37m[2022-01-01 14:00:00] Application EMERGENCY: Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua\033[0m"
        );
    }

    public function assertStreamContains(string $string): void
    {
        rewind($this->stream);

        $this->assertStringContainsString($string, fgets($this->stream));
    }
}
