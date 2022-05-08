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

namespace Lightning\Validator;

use DateTime;
use DateTimeInterface;

class ValidationRules
{
    /**
     * Value must only contain alphabetic characters
     */
    public function alpha(mixed $value): bool
    {
        return is_string($value) && ctype_alpha($value);
    }

    /**
     * Value must only contain alphanumeric characters
     */
    public function alphaNumeric(mixed $value): bool
    {
        return is_string($value) && ctype_alnum($value);
    }

    /**
     * Value must be null
     */
    public function null(mixed $value): bool
    {
        return is_null($value);
    }

    /**
     * Value must not be null
     */
    public function notNull(mixed $value): bool
    {
        return ! is_null($value);
    }

    /**
     * Checks that a value is an empty string
     */
    public function empty(mixed $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value) || is_countable($value)) {
            return count($value) === 0;
        }

        return false;
    }

    /**
     * Value must not be null or empty string or empty array
     */
    public function notEmpty(mixed $value): bool
    {
        return ! $this->empty($value);
    }

    /**
     * Value must not be null or empty string
     */
    public function notBlank(mixed $value): bool
    {
        return ! is_null($value) && is_string($value) && trim($value) !== '';
    }

    /**
     * Value must be a valid email address
     */
    public function email(mixed $value, bool $checkDns = false): bool
    {
        $result = (filter_var($value, FILTER_VALIDATE_EMAIL) !== false);

        if ($result && $checkDns) {
            list($account, $domain) = explode('@', $value);

            return getmxrr($domain, $mxhosts);
        }

        return $result;
    }

    /**
     * Value should be in the list
     */
    public function in(mixed $value, array $list, bool $caseInSensitive = false): bool
    {
        if ($caseInSensitive) {
            $list = array_map('mb_strtolower', $list);
            if (! is_null($value)) {
                $value = mb_strtolower($value);
            }
        }

        return in_array($value, $list);
    }

    /**
     * Value should not be in the list
     */
    public function notIn(mixed $value, array $list, bool $caseInSensitive = false): bool
    {
        return ! $this->in($value, $list, $caseInSensitive);
    }

    /**
     * Value must be a string with a length
     */
    public function length(mixed $value, int $length): bool
    {
        return is_string($value) && mb_strlen($value) === $length;
    }

    /**
     * Value must be a string with a length between a min and a max value
     */
    public function lengthBetween(mixed $value, int $min, int $max): bool
    {
        return is_string($value) && mb_strlen($value) >= $min && mb_strlen($value) <= $max;
    }

    /**
     * Value must be a string with a minimum length
     */
    public function minLength(mixed $value, int $length): bool
    {
        return is_string($value) && mb_strlen($value) >= $length;
    }

    /**
     * Value must be a string with a max length
     */
    public function maxLength(mixed $value, int $length): bool
    {
        return is_string($value) && mb_strlen($value) <= $length;
    }

    /**
     * Value must be numeric and lower or equal to the max
     */
    public function lessThanOrEqualTo(mixed $value, int|float $max): bool
    {
        return is_numeric($value) && $value <= $max;
    }

    /**
     * Value must be numeric and less than
     */
    public function lessThan(mixed $value, int|float $max): bool
    {
        return is_numeric($value) && $value < $max;
    }

    /**
     * Value must be numeric and greater than or equal to the min
     */
    public function greaterThanOrEqualTo(mixed $value, int|float $min): bool
    {
        return is_numeric($value) && $value >= $min;
    }

    /**
     * Value must be numeric and greater than
     */
    public function greaterThan(mixed $value, int|float $min): bool
    {
        return is_numeric($value) && $value > $min;
    }

    /**
     * Value should be equal to
     */
    public function equalTo(mixed $value, mixed $what): bool
    {
        return $value === $what;
    }

    /**
     * Value should not equal
     */
    public function notEqualTo(mixed $value, mixed $what): bool
    {
        return $value !== $what;
    }

    /**
     * Value must be numeric between a min and a max value
     */
    public function range(mixed $value, int|float $min, int|float $max): bool
    {
        return is_numeric($value) && $value >= $min && $value <= $max;
    }

    /**
     * Value must be an integer or a string with integers
     */
    public function integer(mixed $value): bool
    {
        return is_int($value) || ctype_digit($value);
    }

    /**
     * Checks that a value is a decimal/float
     */
    public function decimal(mixed $value): bool
    {
        if (is_string($value)) {
            return (bool) filter_var($value, FILTER_VALIDATE_FLOAT) && filter_var($value, FILTER_VALIDATE_INT) === false;
        }

        return is_float($value);
    }

    /**
     * Value must be string
     */
    public function string(mixed $value): bool
    {
        return is_string($value);
    }

    /**
     * Checks that a value is numeric
     */
    public function numeric(mixed $value): bool
    {
        return is_numeric($value);
    }

    /**
     * Value must be a boolean or representation of a bool
     */
    public function boolean(mixed $value): bool
    {
        return in_array($value, [true,false,0,1,'0','1','true','false'], true);
    }

    /**
     * Value is an array
     */
    public function array(mixed $value): bool
    {
        return is_array($value);
    }

    /**
     * Value must be an array or countable and have a specific count
     */
    public function count(mixed $value, int $count): bool
    {
        return (is_array($value) || is_countable($value)) ? count($value) === $count : false;
    }

    /**
     * Value must be a datetime string with format
     */
    public function dateTime(mixed $value, string $format = 'Y-m-d H:i:s'): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $dateTime = DateTime::createFromFormat($format, $value);

        return ($dateTime !== false && $dateTime->format($format) === $value);
    }

    /**
     * Value is a date/time string or DateTime object and the date is before
     */
    public function before(mixed $value, string $when = 'now'): bool
    {
        $timestamp = $this->getTimestamp($value);

        return $timestamp && $timestamp < strtotime($when);
    }

    /**
     * Value is a date/time string or DateTime object and the date is after
     */
    public function after(mixed $value, string $when = 'now'): bool
    {
        $timestamp = $this->getTimestamp($value);

        return $timestamp && $timestamp > strtotime($when);
    }

    /**
     * Value must be a URL
     */
    public function url(mixed $value, bool $protocol = true): bool
    {
        return (filter_var($protocol ? $value : 'https://' . $value, FILTER_VALIDATE_URL) !== false);
    }

    /**
     * A string value must match a pattern
     */
    public function regularExpression(mixed $value, string $pattern): bool
    {
        return is_string($value) && (bool) preg_match($pattern, $value);
    }

    /**
     * A value is passed through a callable
     */
    public function callable(mixed $value, callable $callable): bool
    {
        return $callable($value) === true;
    }

    private function getTimestamp(mixed $value): ?int
    {
        if ($value instanceof DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_string($value)) {
            return strtotime($value) ?: null;
        }

        return null;
    }
}
