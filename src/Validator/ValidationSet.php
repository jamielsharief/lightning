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

class ValidationSet
{
    protected array $validationRules = [];

    /**
     * Value must must only contain alphabetic characters
     */
    public function alpha(string $message = 'must only contain alphabetic characters'): static
    {
        return $this->add('alpha', [], $message);
    }

    /**
     * Value must must only contain alpha numeric characters
     */
    public function alphaNumeric(string $message = 'must only contain alpha numeric characters'): static
    {
        return $this->add('alphaNumeric', [], $message);
    }

    /**
     * Value must not be null
     */
    public function notNull(string $message = 'must not be null'): static
    {
        return $this->add('notNull', [], $message);
    }

    /**
     * Value must not be null or empty string or empty array
     */
    public function notEmpty(string $message = 'must not be empty'): static
    {
        return $this->add('notEmpty', [], $message);
    }

    /**
     * Value must not be null or empty string or empty array
     */
    public function notBlank(string $message = 'must not be blank'): static
    {
        return $this->add('notBlank', [], $message);
    }

    /**
     * Value must be a valid email
     */
    public function email(bool $checkDns = false, string $message = 'invalid email address'): static
    {
        return $this->add('email', [$checkDns], $message);
    }

    /**
     * Value must be in list
     */
    public function in(array $list, bool $caseInSensitive = false, string $message = 'invalid value'): static
    {
        return $this->add('in', [$list, $caseInSensitive], $message);
    }

    /**
     * Value must not be in list
     */
    public function notIn(array $list, bool $caseInSensitive = false, string $message = 'invalid value'): static
    {
        return $this->add('notIn', [$list,$caseInSensitive], $message);
    }

    /**
     * Value must be a string with a specific length
     */
    public function length(int $length, string $message = 'must be {length} characters'): static
    {
        return $this->add('length', [$length], str_replace('{length}', (string) $length, $message));
    }

    /**
     * Value must be a string with a length equal to greater than the minimum and less than or equal to the maximum
     */
    public function lengthBetween(int $min, int $max, string $message = 'must be between {min} and {max} characters'): static
    {
        return $this->add('lengthBetween', [$min,$max], str_replace(['{min}','{max}'], [(string) $min, (string) $max], $message));
    }

    /**
     * Value must be a string with a minimum length
     */
    public function minLength(int $length, string $message = 'must be a minimum of {length} characters'): static
    {
        return $this->add('minLength', [$length], str_replace('{length}', (string) $length, $message));
    }

    /**
     * Value must be a string with a maximum length
     */
    public function maxLength(int $length, string $message = 'must be a maximum of {length} characters'): static
    {
        return $this->add('maxLength', [$length], str_replace('{length}', (string) $length, $message));
    }

    /**
     * Value must be numeric and greater or equal to the min
     */
    public function greaterThanOrEqualTo(int $min, string $message = 'invalid value'): static
    {
        return $this->add('greaterThanOrEqualTo', [$min], str_replace('{min}', (string) $min, $message));
    }

    /**
     * Value must be numeric and greater than the min
     */
    public function greaterThan(int $min, string $message = 'invalid value'): static
    {
        return $this->add('greaterThan', [$min], str_replace('{min}', (string) $min, $message));
    }

    /**
     * Value must be numeric and less than or equal to the max
     */
    public function lessThanOrEqualTo(int $max, string $message = 'invalid value'): static
    {
        return $this->add('lessThanOrEqualTo', [$max], str_replace('{max}', (string) $max, $message));
    }

    /**
     * Value must be numeric and less than the max
     */
    public function lessThan(int $max, string $message = 'invalid value'): static
    {
        return $this->add('lessThan', [$max], str_replace('{max}', (string) $max, $message));
    }

    /**
     * Value must be a numeric and equal to greater than the minimum and less than or equal to the maximum
     */
    public function range(int $min, int $max, string $message = 'invalid value'): static
    {
        return $this->add('range', [$min,$max], str_replace(['{min}','{max}'], [(string) $min, (string) $max], $message));
    }

    /**
     * Value must be an integer or a string with only integers
     */
    public function integer(string $message = 'invalid value'): static
    {
        return $this->add('integer', [], $message);
    }

    /**
     * Value must be a string
     */
    public function string(string $message = 'invalid value'): static
    {
        return $this->add('string', [], $message);
    }

    /**
     * Value must be numeric value or string
     */
    public function numeric(string $message = 'invalid value'): static
    {
        return $this->add('numeric', [], $message);
    }

    /**
     * Value must be decmial or a string representing a decimal
     */
    public function decimal(string $message = 'invalid value'): static
    {
        return $this->add('decimal', [], $message);
    }

    /**
     * Value must be a boolean or represent a boolean e.g. '1' or 'true'
     */
    public function boolean(string $message = 'invalid value'): static
    {
        return $this->add('boolean', [], $message);
    }

    /**
     * Value must be an array
     */
    public function array(string $message = 'invalid value'): static
    {
        return $this->add('array', [], $message);
    }

    /**
     * Value must be a string representing a date format
     */
    public function date(string $format = 'Y-m-d', string $message = 'invalid value'): static
    {
        return $this->add('dateTime', [$format], $message);
    }

    /**
     * Value must be a string representing a date time format
     */
    public function datetime(string $format = 'Y-m-d H:i:s', string $message = 'invalid value'): static
    {
        return $this->add('dateTime', [$format], $message);
    }

    /**
     * Value must be a string representing a time format
     */
    public function time(string $format = 'H:i:s', string $message = 'invalid value'): static
    {
        return $this->add('dateTime', [$format], $message);
    }

    /**
     * Validates a date string is before a date (experimental)
     */
    public function before(string $when, string $message = 'invalid date'): static
    {
        return $this->add('before', [$when], $message);
    }

    /**
     * Validates a date string is after a date (experimental)
     */
    public function after(string $when, string $message = 'invalid date'): static
    {
        return $this->add('after', [$when], $message);
    }

    /**
     * The value must be a string and match a particular pattern
     */
    public function regularExpression(string $pattern, string $message = 'invalid value'): static
    {
        return $this->add('regularExpression', [$pattern], $message);
    }

    /**
     * The value must be a URL
     */
    public function url(bool $withProtocol = true, string $message = 'invalid URL'): static
    {
        return $this->add('url', [$withProtocol], $message);
    }

    /**
     * The value is passed to a callable and that must return true
     */
    public function callable(callable $callable, string $message = 'invalid value'): static
    {
        return $this->add('callable', [$callable], $message);
    }

    /**
     * The value is optional, and no further validation takes place if the value is empty
     */
    public function optional(): static
    {
        return $this->add('optional', [], '');
    }

    /**
     * Value must be equal to
     */
    public function equalTo(mixed $what, string $message = 'invalid value'): static
    {
        return $this->add('equalTo', [$what], $message);
    }

    /**
     * Value must not be equal to
     */
    public function notEqualTo(mixed $what, string $message = 'invalid value'): static
    {
        return $this->add('notEqualTo', [$what], $message);
    }

    /**
     * Define a custom method in the validator to call
     */
    public function method(string $method, array $args = [], string $message = 'invalid value'): static
    {
        return $this->add($method, $args, $message);
    }

    /**
     * Get the rules as an array
     */
    public function toArray(): array
    {
        return $this->validationRules;
    }

    /**
     * Enables the stop on failure mechansim
     */
    public function stopIfFailure(): static
    {
        return $this->add('stopIfFailure');
    }

    /**
     * Create the validation rule array
     */
    private function add(string $method, array $args = [], string $message = 'invalid value'): static
    {
        $this->validationRules[] = [
            'rule' => $method,
            'args' => $args,
            'message' => $message
        ];

        return $this;
    }
}
