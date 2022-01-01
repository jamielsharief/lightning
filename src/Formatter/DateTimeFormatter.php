<?php declare(strict_types=1);
/**
 * LightningPHP
 * Copyright 2021 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Lightning\Formatter;

use DateTime;
use Stringable;
use DateTimeZone;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * DateTimeFormatter - A formatter object to be passed around an application that will display dates etc in user timezone and format.
 *
 * TODO: union types when PHP 8 ready
 */
class DateTimeFormatter
{
    private string $datetimeFormat = 'Y-m-d H:i:s';
    private string $dateFormat = 'Y-m-d';
    private string $timeFormat = 'H:i:s';
    private string $timezone = 'UTC';

    /**
     * Sets the timezone for this object
     *
     * @param string $timezone
     * @return self
     */
    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Sets the format for datetime string
     *
     * @param string $format
     * @return self
     */
    public function setDateTimeFormat(string $format): self
    {
        $this->datetimeFormat = $format;

        return $this;
    }

    /**
     * Sets the format for date string
     *
     * @param string $format
     * @return self
     */
    public function setDateFormat(string $format): self
    {
        $this->dateFormat = $format;

        return $this;
    }

    /**
     * Sets the format for time string
     *
     * @param string $format
     * @return self
     */
    public function setTimeFormat(string $format): self
    {
        $this->timeFormat = $format;

        return $this;
    }

    /**
     * Formats a datetime using the format provided or the datetime format
     *
     * @param DateTime|string|int $datetime
     * @param string|null $format
     * @return string
     */
    public function format($datetime, string $format = null): string
    {
        return $this->createDateTime($datetime)->format($format ?: $this->datetimeFormat);
    }

    /**
     * Formats a time as a datetime string
     *
     * @param DateTime|string|int $datetime
     * @return string
     */
    public function datetime($datetime): string
    {
        return $this->format($datetime, $this->datetimeFormat);
    }

    /**
     * Formats a time as a date string
     *
     * @param DateTime|string|int $datetime
     * @return string
     */
    public function date($datetime): string
    {
        return $this->format($datetime, $this->dateFormat);
    }

    /**
     * Formats a time as a time string
     *
     * @param DateTime|string|int $datetime
     * @return string
     */
    public function time($datetime): string
    {
        return $this->format($datetime, $this->timeFormat);
    }

    /**
     * Factory method
     *
     * @param DateTimeInterface|string|int
     * @return DateTime
     */
    private function createDateTime($datetime): DateTime
    {
        if (is_null($datetime)) {
            throw new InvalidArgumentException('Attempting to format a null value');
        }

        if (is_int($datetime)) {
            $timestamp = $datetime;
            $datetime = new DateTime();
            $datetime->setTimestamp($timestamp);
        } elseif (is_string($datetime) || $datetime instanceof Stringable) {
            $datetime = new DateTime((string) $datetime);
        }

        if (! $datetime instanceof DateTime) {
            throw new InvalidArgumentException(
                sprintf('Invalid value `%s` passed to DateTime formatter', is_scalar($datetime) ? $datetime : 'unkown')
            );
        }

        if (date_default_timezone_get() !== $this->timezone) {
            $datetime->setTimezone(new DateTimeZone($this->timezone));
        }

        return $datetime;
    }
}
