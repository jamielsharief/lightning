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

namespace Lightning\Criteria;

use InvalidArgumentException;

/**
 * This will be used for MemoryStorage and MangoDB
 */
class Criteria
{
    private array $criteria = [];

    private array $expressionMap = [
        '=' => 'equals',
        '!=' => 'notEquals',
        '<>' => 'notEquals',
        '<' => 'lessThan',
        '>' => 'greaterThan',
        '<=' => 'lessThanOrEqualTo',
        '>=' => 'greaterThanOrEqualTo',
        'IN' => 'in',
        'NOT IN' => 'notIn',
        'BETWEEN' => 'between',
        'NOT BETWEEN' => 'notBetween',
        'LIKE' => 'like',
        'NOT LIKE' => 'notLike'
    ];

    /**
     * Constructor
     *
     * @param array $criteria
     */
    public function __construct(array $criteria)
    {
        foreach ($criteria as $field => $value) {
            if (is_int($field)) {
                throw new InvalidArgumentException('No key provided');
            }
            if (strpos($field, ' ') === false) {
                $expression = '=';
            } else {
                list($field, $expression) = explode(' ', $field, 2); //['id !=' => 1]
            }

            $this->addComparison($field, $expression, $value);
        }
    }

    /**
     * Adds a Comparision
     *
     * @param string $field
     * @param string $expression
     * @param mixed $value
     * @return void
     */
    private function addComparison(string $field, string $expression, $value): void
    {
        if (! in_array($expression, ['BETWEEN','NOT BETWEEN','=', '!=', '<','>','<=','<>','>=','IN', 'NOT IN','LIKE','NOT LIKE'])) {
            throw new InvalidArgumentException(sprintf('Invalid expression `%s`', $expression));
        }

        switch ($expression) {
            case 'BETWEEN':
            case 'NOT BETWEEN':
                if (! is_array($value) || count($value) !== 2) {
                    throw new InvalidArgumentException(sprintf('Invalid comparison value for `%s`, expected an array with two values.', $field)); //['id BETWEEN' => [1000,2000]]
                }

                break;
            case 'IN':
            case 'NOT IN':
                if (! is_array($value)) {
                    throw new InvalidArgumentException(sprintf('Invalid comparison value for `%s`, expected an array', $field));;
                }

                break;

            case 'LIKE':
            case 'NOT LIKE':
                if (! is_scalar($value) && ! is_null($value)) {
                    throw new InvalidArgumentException(sprintf('Invalid comparison value for `%s`, expected a scalar or null value', $field));
                }

                $value = sprintf('/^%s$/i', str_replace(['%','_'], ['.*','.'], (string) $value));

            break;
            default:
                if (! in_array($expression, ['=','!=','<>']) && is_array($value)) {
                    throw new InvalidArgumentException(sprintf('Invalid comparison value for `%s`, did not expect array', $field));
                }

            break;

        }

        if (is_object($value)) {
            throw new InvalidArgumentException(sprintf('Invalid comparison value for `%s`, object provided', $field));
        }

        $this->criteria[] = [
            'field' => $field,
            'expression' => $expression,
            'expected' => $value
        ];
    }

    /**
     * Checks if a record set matches the criteria
     *
     * @param array $data
     * @return boolean
     */
    public function match(array $data): bool
    {
        foreach ($this->criteria as $condition) {
            if (! array_key_exists($condition['field'], $data)) {
                throw new InvalidArgumentException(sprintf('Data is missing key `%s`', $condition['field']));
            }

            $value = $data[$condition['field']];
            $method = $this->expressionMap[$condition['expression']];

            if (! $this->$method($value, $condition['expected'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed $actual
     * @param mixed $expected
     * @return boolean
     */
    private function equals($actual, $expected): bool
    {
        if (is_array($expected)) {
            return in_array($actual, $expected);
        }

        return $actual === $expected;
    }
    /**
     * @param mixed $actual
     * @param mixed $expected
     * @return boolean
     */
    private function notEquals($actual, $expected): bool
    {
        if (is_array($expected)) {
            return ! in_array($actual, $expected);
        }

        return $actual !== $expected;
    }
    /**
     * @param mixed $actual
     * @param mixed $expected
     * @return boolean
     */
    private function lessThan($actual, $expected): bool
    {
        return $actual < $expected;
    }

    /**
     * @param mixed $actual
     * @param mixed $expected
     * @return boolean
     */
    private function greaterThan($actual, $expected): bool
    {
        return $actual > $expected;
    }

    /**
     * @param mixed $actual
     * @param mixed $expected
     * @return boolean
     */
    private function lessThanOrEqualTo($actual, $expected): bool
    {
        return $actual <= $expected;
    }

    /**
     * @param mixed $actual
     * @param mixed $expected
     * @return boolean
     */
    private function greaterThanOrEqualTo($actual, $expected): bool
    {
        return $actual >= $expected;
    }

    /**
     * @param mixed $actual
     * @param array $expected
     * @return boolean
     */
    private function in($actual, array $expected): bool
    {
        return in_array($actual, $expected);
    }

    /**
     * @param mixed $actual
     * @param array $expected
     * @return boolean
     */
    private function notIn($actual, array $expected): bool
    {
        return ! in_array($actual, $expected);
    }

    /**
     * @internal this should behave similar to MySQL between
     *
     * @param integer $actual
     * @param array $expected
     * @return boolean
     */
    private function between($actual, array $expected): bool
    {
        return $actual >= $expected[0] && $actual <= $expected[1];
    }

    /**
     * @internal this should behave similar to MySQL between
     *
     * @param integer $actual
     * @param array $expected
     * @return boolean
     */
    private function notBetween($actual, array $expected): bool
    {
        return ($actual >= $expected[0] && $actual <= $expected[1]) === false;
    }

    /**
     * @param string|null $actual
     * @param string $pattern
     * @return boolean
     */
    private function like($actual, string $pattern): bool
    {
        return (bool) preg_match($pattern, (string) $actual);
    }

    /**
     * @param string|null $actual
     * @param string $pattern
     * @return boolean
     */
    private function notLike($actual, string $pattern): bool
    {
        return (bool) preg_match($pattern, (string) $actual) === false;
    }
}
