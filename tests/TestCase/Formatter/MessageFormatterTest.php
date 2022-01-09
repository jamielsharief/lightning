<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use stdClass;
use PHPUnit\Framework\TestCase;
use Lightning\Formatter\MessageFormatter;

final class MessageFormatterTest extends TestCase
{
    /**
     * Test various things
     *
     * @return void
     */
    public function testFormat(): void
    {
        $formatter = new MessageFormatter();

        $this->assertEquals('Hello {name}!', $formatter->format('Hello {name}!', []));
        $this->assertEquals('Hello jim!', $formatter->format('Hello {name}!', ['name' => 'jim']));
        $this->assertEquals('Hello !', $formatter->format('Hello {name}!', ['name' => null]));
        $this->assertEquals('Hello {name}!', $formatter->format('Hello {name}!', ['name' => new stdClass()]));
        $this->assertEquals('a 2  {d}', $formatter->format('{a} {b} {c} {d}', ['a' => 'a','b' => 2,'c' => null]));
    }

    public function testFormatPlural(): void
    {
        $formatter = new MessageFormatter();
        $message = 'There are zero apples|There is one apple|There are {count} apples';
        $this->assertEquals('There are zero apples', $formatter->format($message, ['count' => 0]));
        $this->assertEquals('There is one apple', $formatter->format($message, ['count' => 1]));
        $this->assertEquals('There are 2 apples', $formatter->format($message, ['count' => 2]));
        $this->assertEquals('There are 3 apples', $formatter->format($message, ['count' => 3]));
    }
}
