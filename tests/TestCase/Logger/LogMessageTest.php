<?php declare(strict_types=1);

namespace Lightning\Test\Logger;

use PHPUnit\Framework\TestCase;
use Lightning\Logger\LogMessage;

final class LogMessageTest extends TestCase
{
    public function testToString(): void
    {
        $this->assertEquals('This is a test.', (string) (new LogMessage('This is a test.')));
        $this->assertEquals('This is a test.', (string) (new LogMessage('This is a {var}.', ['var' => 'test'])));
        $this->assertEquals('This is a test.', (string) (new LogMessage('This is a {var}.', ['var' => 'test','foo' => 'bar'])));
    }
}
