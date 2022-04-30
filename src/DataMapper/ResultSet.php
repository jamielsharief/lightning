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

namespace Lightning\DataMapper;

use Countable;
use Stringable;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;

/**
 * ResultSet
 */
class ResultSet implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Stringable
{
    private array $rows = [];

    /**
     * Constructor
     *
     * @param array $rows
     */
    final public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    /**
     * Countable interface
     *
     * @return integer
     */
    public function count(): int
    {
        return count($this->rows);
    }

    /**
     * Gets the first item
     *
     * @return mixed
     */
    public function first(): mixed
    {
        $result = null;
        foreach ($this->rows as $result) {
            break;
        }

        return $result;
    }

    /**
     * Checks if this data is empty
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return empty($this->rows);
    }

    /**
     * Gets the data for this data
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->rows;
    }

    /**
     * Converts this row to JSON
     *
     * @return string
     */
    public function toString(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Stringable
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * IteratorAggregate interface
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->rows);
    }

    /**
     * Gets the data to be serialized
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->rows;
    }

    /**
     * ArrayAcces Interface for isset($data);
     *
     * @param mixed $key
     * @return bool result
     */
    public function offsetExists($key) : bool
    {
        return array_key_exists($key, $this->rows);
    }

    /**
     * ArrayAccess Interface for $data[$key];
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->rows[$key] ?? null;
    }

    /**
     * ArrayAccess Interface for $data[$key] = $value;
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value) : void
    {
        if (is_null($key)) {
            $this->rows[] = $value;
        } else {
            $this->rows[$key] = $value;
        }
    }

    /**
     * ArrayAccess Interface for unset($data[$key]);
     *
     * @param mixed $key
     * @return void
     */
    public function offsetUnset($key) : void
    {
        unset($this->rows[$key]);
    }

    /**
     * Applies the function to each row
     *
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback): static
    {
        $result = [];
        foreach ($this->rows as $key => $value) {
            $result[$key] = $callback($value, $key);
        }

        return new static($result);
    }

    /**
     * Filters the result set
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): static
    {
        $result = [];
        foreach ($this->rows as $key => $value) {
            if ($callback($value, $key)) {
                $result[$key] = $value;
            }
        }

        return new static($result);
    }

    /**
     * Indexes the result
     *
     * @param callable $callback
     * @return static
     */
    public function indexBy(callable $callback): static
    {
        $result = [];
        foreach ($this->rows as $value) {
            $result[$callback($value)] = $value;
        }

        return new static($result);
    }

    /**
     * Groups by
     *
     * @param callable $callback
     * @return static
     */
    public function groupBy(callable $callback): static
    {
        $result = [];
        foreach ($this->rows as $value) {
            $result[$callback($value)][] = $value;
        }

        return new static($result);
    }
}
