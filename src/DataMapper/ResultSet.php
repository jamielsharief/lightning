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
    private array $data = [];

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Countable interface
     *
     * @return integer
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Gets the first item
     *
     * @return mixed
     */
    public function first()
    {
        $result = null;
        foreach ($this->data as $result) {
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
        return empty($this->data);
    }

    /**
     * Gets the data for this data
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
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
        return new ArrayIterator($this->data);
    }

    /**
     * Gets the data to be serialized
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    /**
     * ArrayAcces Interface for isset($data);
     *
     * @param mixed $key
     * @return bool result
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * ArrayAccess Interface for $data[$key];
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * ArrayAccess Interface for $data[$key] = $value;
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->data[] = $value;
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * ArrayAccess Interface for unset($data[$key]);
     *
     * @param mixed $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }
}
