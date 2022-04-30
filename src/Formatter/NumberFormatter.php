<?php declare(strict_types=1);
/**
 * LightningPHP
 * Copyright 2021 - 2022 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Lightning\Formatter;

use InvalidArgumentException;

/**
 * Number Formatter
 * Offers a standard way to format numbers
 */
class NumberFormatter
{
    private string $defaultCurrency = 'USD';

    /**
     * Decimal separator symbol
     *
     * @var string
     */
    private string $decimals = '.';

    /**
     * Thousands separator symbol
     *
     * @var string
     */
    private string $thousands = ',';

    private array $currencies = [
        'AUD' => [
            'before' => '$',
            'after' => '',
            'precision' => 2
        ],
        'CAD' => [
            'before' => '$',
            'after' => '',
            'precision' => 2
        ],
        'CHF' => [
            'before' => '',
            'after' => 'Fr',
            'precision' => 2
        ],
        'EUR' => [
            'before' => '€',
            'after' => '',
            'precision' => 2
        ],
        'GBP' => [
            'before' => '£',
            'after' => '',
            'precision' => 2
        ],
        'JPY' => [
            'before' => '¥',
            'after' => '',
            'precision' => 2
        ],
        'USD' => [
            'before' => '$',
            'after' => '',
            'precision' => 2
        ],
    ];

    /**
     * Sets the formatting options for this locale
     *
     * @param string $thousands
     * @param string $decimals
     * @return static
     */
    public function setFormat(string $thousands, string $decimals): static
    {
        $this->thousands = $thousands;
        $this->decimals = $decimals;

        return $this;
    }

    /**
     * Sets the default currency to use
     *
     * @param string $currency
     * @return static
     */
    public function setDefaultCurrency(string $currency): static
    {
        $this->defaultCurrency = $currency;

        return $this;
    }

    /**
     * Adds a currency
     *
     * @param string $currencyCode
     * @param string $before
     * @param string $after
     * @param integer $precision
     * @return static
     */
    public function addCurrency(string $currencyCode, string $before, string $after, int $precision = 2): static
    {
        $this->currencies[$currencyCode] = ['before' => $before,'after' => $after,'precision' => $precision];

        return $this;
    }

    /**
     * Formats a number
     *
     * @param float|int|string $value string without thousands seperator and the decimal symbol must be `.` for strings, regardless
     *      how this will be displayed to user.
     * @param integer $places The number of places a float should be formatted by  e.g. (float) 123 or '123.00'
     * @return string
     */
    public function format($value, int $places = 0): string
    {
        $value = $this->getValue($value);

        if (is_int($value)) {
            $places = 0;
        }

        return number_format($value, $places, $this->decimals, $this->thousands);
    }

    /**
     * Formats a value to a level of precision
     *
     * @internal formatter treats integers
     *
     * @param float|int|string $value
     * @param integer $precision
     * @return string
     */
    public function precision($value, int $precision = 3): string
    {
        $value = sprintf("%01.{$precision}f", $this->getValue($value));

        return number_format((float) $value, $precision, $this->decimals, $this->thousands);
    }

    /**
     * Format the number and converts to percentage
     *
     * @param float|int|string $value
     * @param integer $precision
     * @return string
     */
    public function toPercentage($value, int $precision = 2): string
    {
        $value = $this->getValue($value);

        if (is_int($value)) {
            $precision = 0;
        }

        return $this->precision($value, $precision) . '%';
    }

    /**
     * Converts bytes into a human readable size
     *
     * @param integer $bytes
     * @return string
     */
    public function toReadableSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB','YB'];

        $value = $bytes;
        for ($i = 0; $value > 1024; $i++) {
            $value /= 1024;
        }

        $precision = $i > 1 ? 2 : 0;
        if (is_int($value)) {
            $precision = 0;
        }

        return $this->precision(round($value, $i), $precision) . ' ' . $units[$i];
    }

    /**
     * Formats a number as a curency
     *
     * @param float|int|string $value
     * @param string|null $currencyCode
     * @return string
     */
    public function currency($value, ?string $currencyCode = null): string
    {
        if (! $currencyCode) {
            $currencyCode = $this->defaultCurrency;
        }

        // in the UK english code is before number every other language after
        $currency = $this->currencies[$currencyCode] ?? ['before' => '','after' => ' '  . $currencyCode, 'precision' => 2];

        $before = $currency['before'] ?? null;
        $after = $currency['after'] ?? null;

        $value = $this->getValue($value);

        $places = $currency['precision'];
        if (is_int($value)) {
            $places = 0;
        }

        $abs = abs($value);

        // This industry standard for currency
        if ($value < 0) {
            $before = '('  . $before;
            $after .= ')';
        }

        return $before . $this->format($abs, $places) . $after;
    }

    /**
     * Checks value is valid and convert string values. All decimal string values must use `.` regardless how this is displayed
     * to user. Same how a decimal w
     *
     * @param float|int|string $value
     * @return mixed
     */
    private function getValue($value)
    {
        if (is_float($value) || is_int($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return strpos($value, '.') !== false ? (float) $value : (int) $value;
        }

        throw new InvalidArgumentException('Values must be a float, integer or a string representation of one of those');
    }
}
