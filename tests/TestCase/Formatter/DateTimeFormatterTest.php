<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use DateTime;
use stdClass;
use Stringable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Lightning\Formatter\DateTimeFormatter;

class MyDate implements Stringable
{
    private string $date;
    public function __construct(string $date)
    {
        $this->date = $date;
    }

    public function __toString(): string
    {
        return $this->date;
    }
}

final class DateTimeFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $datetime = new DateTimeFormatter();
        $this->assertEquals('1999-07-01 22:00:00', $datetime->format('1999-07-01 22:00:00'));
        $this->assertEquals('01/07/1999 22:00', $datetime->format('1999-07-01 22:00:00', 'd/m/Y H:i'));
    }

    public function testFormatWithTimestamp(): void
    {
        $datetime = new DateTimeFormatter();
        $this->assertEquals('01/07/1999 22:00', $datetime->format(strtotime('1999-07-01 22:00:00'), 'd/m/Y H:i'));
    }

    public function testFormatWithStringable(): void
    {
        $datetime = new DateTimeFormatter();
        $this->assertEquals('01/07/1999 22:00', $datetime->format(new MyDate('1999-07-01 22:00:00'), 'd/m/Y H:i'));
    }

    public function testFormatWithDateTime(): void
    {
        $datetime = new DateTimeFormatter();
        $this->assertEquals('01/07/1999 22:00', $datetime->format(new DateTime('1999-07-01 22:00:00'), 'd/m/Y H:i'));
    }

    public function testFormatWithNull(): void
    {
        $datetime = new DateTimeFormatter();
        $this->expectExceptionMessage('Attempting to format a null value');
        $datetime->format(null); #! important we wont accept this
    }

    public function testFormatWithSomethingElse(): void
    {
        $datetime = new DateTimeFormatter();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value `unkown` passed to DateTime formatter');
        $datetime->format(new stdClass(), 'd/m/Y H:i');
    }

    public function testFormatWithTimezone(): void
    {
        $datetime = new DateTimeFormatter();
        $datetime->setTimezone('America/Chicago');
        $this->assertEquals('1999-07-01 17:00:00', $datetime->format('1999-07-01 22:00:00'));
    }

    public function testDate(): void
    {
        $datetime = new DateTimeFormatter();
        $this->assertEquals('1999-07-01', $datetime->date('1999-07-01 22:00:00'));
    }

    public function testDateSetFormat(): void
    {
        $datetime = new DateTimeFormatter();

        $this->assertEquals('01.07.99', $datetime->setDateFormat('d.m.y')->date('1999-07-01 22:00:00'));
    }

    public function testDateTime(): void
    {
        $datetime = new DateTimeFormatter();
        $this->assertEquals('1999-07-01 22:00:00', $datetime->datetime('1999-07-01 22:00:00'));
    }

    public function testDateTimeSetFormat(): void
    {
        $datetime = new DateTimeFormatter();

        $this->assertEquals('Thursday, 01-Jul-1999 22:00:00 UTC', $datetime->setDateTimeFormat(DateTime::COOKIE)->datetime('1999-07-01 22:00:00'));
    }

    public function testTime(): void
    {
        $datetime = new DateTimeFormatter();
        $this->assertEquals('22:00:00', $datetime->time('1999-07-01 22:00:00'));
    }

    public function testTimeSetFormat(): void
    {
        $datetime = new DateTimeFormatter();

        $this->assertEquals('22:00:00 UTC', $datetime->setTimeformat('H:i:s T')->time('1999-07-01 22:00:00'));
    }
}
