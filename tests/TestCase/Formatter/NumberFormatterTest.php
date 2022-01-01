<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Lightning\Formatter\NumberFormatter;

final class NumberFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $number = new NumberFormatter();

        $this->assertEquals('0', $number->format(0));
        $this->assertEquals('0', $number->format((float) 0));
        $this->assertEquals('0', $number->format('0'));

        // test int and conversion
        $this->assertEquals('1,234', $number->format(1234));
        $this->assertEquals('1,234', $number->format((float) 1234));
        $this->assertEquals('1,234', $number->format('1234'));

        // test float and conversion
        $this->assertEquals('1,234', $number->format('1234.123'));
        $this->assertEquals('1,234', $number->format(1234.123));

        // test float and conversion
        $this->assertEquals('1,234', $number->format(1234, 2));
        $this->assertEquals('1,234.12', $number->format(1234.123, 2));
        $this->assertEquals('1,234.12', $number->format('1234.123', 2));

        // edge cases important
        $this->assertEquals('1,234.00', $number->format((float) 1234, 2));
        $this->assertEquals('1,234.00', $number->format(1234.00, 2));
    }

    public function testFormatInvalidArgument(): void
    {
        $number = new NumberFormatter();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Values must be a float, integer or a string representation of one of those');

        $number->format('');
    }

    public function testPrecision(): void
    {
        $number = new NumberFormatter();
        $this->assertEquals('1,234.000', $number->precision(1234));
        $this->assertEquals('1,234', $number->precision(1234, 0));
        $this->assertEquals('1,234.123', $number->precision(1234.1234, 3));

        $this->assertEquals('1,234.00', $number->precision(1234, 2)); // 1,234.00
        $this->assertEquals('1,234.12', $number->precision('1234.12', 2));
        $this->assertEquals('1,234.12', $number->precision(1234.1234, 2));
    }

    public function testPercentage(): void
    {
        $number = new NumberFormatter();
        $this->assertEquals('1,234%', $number->toPercentage(1234));
        $this->assertEquals('1,234.00%', $number->toPercentage((float) 1234));
        $this->assertEquals('1,234.123%', $number->toPercentage('1234.1234', 3));
    }

    public function testToReadableSize(): void
    {
        $number = new NumberFormatter();
        $this->assertEquals('0 B', $number->toReadableSize(0));
        $this->assertEquals('1,024 B', $number->toReadableSize(1024));
        $this->assertEquals('1,024 KB', $number->toReadableSize(1024 * 1024));
        $this->assertEquals('1,024 MB', $number->toReadableSize(1024 * 1024 * 1024));
        $this->assertEquals('1,024 GB', $number->toReadableSize(1024 * 1024 * 1024 * 1024));
        $this->assertEquals('1,024 TB', $number->toReadableSize(1024 * 1024 * 1024 * 1024 * 1024));
        $this->assertEquals('1,024 PB', $number->toReadableSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024));

        $this->assertEquals('1,024 MB', $number->toReadableSize(1073741824, 2));
        $this->assertEquals('1,023.81 MB', $number->toReadableSize(1073541824, 2));
    }

    public function testCurrency(): void
    {
        $number = new NumberFormatter();
        $this->assertEquals('$1,234.57', $number->currency('1234.5678', 'USD'));
        $this->assertEquals('$1,234', $number->currency(1234, 'USD'));
        $this->assertEquals('$1,234.00', $number->currency((float) 1234, 'USD'));
        $this->assertEquals('(£1,234.57)', $number->currency(-1234.5678, 'GBP'));
    }

    public function testSetDefaultCurrency(): void
    {
        $number = new NumberFormatter();
        $number->setDefaultCurrency('JPY');
        $this->assertEquals('¥1,234.57', $number->currency('1234.5678'));
    }

    public function testCurrencyUnkown(): void
    {
        $number = new NumberFormatter();

        // # Only the UK would needed to be front, everywhere else in the end. Do not change
        $this->assertEquals('1,234.57 FOO', $number->currency('1234.5678', 'FOO'));
    }

    public function testAddCurrency(): void
    {
        $number = new NumberFormatter();
        $number->addCurrency('BTC', '฿', '');
        $this->assertEquals('฿1,234', $number->currency(1234, 'BTC'));
        $this->assertEquals('฿1,234.00', $number->currency((float) 1234, 'BTC'));

        $number->addCurrency('BTC', '', '฿');
        $this->assertEquals('1,234฿', $number->currency('1234', 'BTC'));

        $number->addCurrency('BTC', '฿', '', 3);
        $this->assertEquals('฿1,234', $number->currency(1234, 'BTC')); // test whole numbers
        $this->assertEquals('฿1,234.568', $number->currency(1234.5678, 'BTC'));
        $this->assertEquals('฿0.123', $number->currency(0.1234567, 'BTC'));
    }

    /**
     * @internal string conversion is important
     *
     * @return void
     */
    public function testSetFormat(): void
    {
        $number = new NumberFormatter();
        $number->setFormat('.', ',');

        $this->assertEquals('123.456.789', $number->format('123456789.123456789')); // string conversion
        $this->assertEquals('123.456.789,12346', $number->format('123456789.123456789', 5)); // string conversion
    }
}
