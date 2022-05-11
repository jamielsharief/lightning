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

namespace Lightning\Utility;

use Countable;
use Stringable;
use ArrayAccess;
use Traversable;
use Serializable;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;

/**
 * Collection
 *
 * Problem: Using lots of similar objects to replace arrays and I dont want to use full blown Collection library
 *
 * A array based object that will be used instead of array. Only a few basic array based functions are included, e.g. filter, map etc.
 */
class Collection implements ArrayAccess, Countable, JsonSerializable, IteratorAggregate, Serializable, Stringable
{
    protected array $elements = [];

    /**
     * Constructor
     */
    final public function __construct(iterable $items = [], bool $preserveKeys = true)
    {
        if (is_array($items)) {
            $this->elements = $preserveKeys ? $items : array_values($items);
        } else {
            iterator_to_array($items, $preserveKeys);
        }
    }

    /**
     * Adds an element to the Collection
     */
    public function add(mixed $element): static
    {
        $this->elements[] = $element;

        return $this;
    }

    /**
     * Removes an element from the Collection
     */
    public function remove(mixed $element): bool
    {
        $index = $this->indexOf($element);
        if (! is_null($index)) {
            unset($this->elements[$index]);
        }

        return $index !== null;
    }

    /**
     * Sets an item in the collection using the key
     */
    public function set(string|int $key, mixed $element): static
    {
        $this->elements[$key] = $element;

        return $this;
    }

    /**
     * Gets an item from the collection
     */
    public function get(string|int $key, mixed $default = null): mixed
    {
        return $this->elements[$key] ?? $default;
    }

    /**
     * Check if the collection contains the element
     */
    public function contains(mixed $element): bool
    {
        return $this->indexOf($element) !== null;
    }

    /**
     * Finds the element in the collection and returns the index or key
     */
    public function indexOf(mixed $element): mixed
    {
        foreach ($this->elements as $index => $value) {
            if ($value === $element) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Clear collection
     */
    public function clear(): static
    {
        $this->elements = [];

        return $this;
    }

    /**
     * Check if the collection is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * Loop through each item in the collection
     */
    public function forEach(callable $callback): static
    {
        foreach ($this->elements as $key => $value) {
            if ($callback($value, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Modify data through the map, this function must return the value
     */
    public function map(callable $callback): static
    {
        $result = [];
        foreach ($this->elements as $key => $value) {
            $result[$key] = $callback($value, $key);
        }

        return new static($result);
    }

    /**
     * Use a callback to filter the elements in the collection
     */
    public function filter(callable $callback): static
    {
        $result = [];
        foreach ($this->elements as $key => $value) {
            if ($callback($value, $key)) {
                $result[$key] = $value;
            }
        }

        return new static($result);
    }

    /**
     * Checks if an item exists in the collection if the callback returns true
     */
    public function exists(callable $callback): bool
    {
        $result = false;
        foreach ($this->elements as $key => $value) {
            if ($callback($value, $key)) {
                $result = true;

                break;
            }
        }

        return $result;
    }

    /**
     * Get the collection as an array
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     *  CountableInterface
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * IteratorAggregate interface
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * Gets the data to be serialized
     */
    public function jsonSerialize(): mixed
    {
        return $this->elements;
    }

    /**
     * Converts this row to JSON
     */
    public function toString(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Stringable
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * ArrayAcces Interface for isset($data);
     */
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->elements);
    }

    /**
     * ArrayAccess Interface for $data[$key];
     */
    public function offsetGet($key): mixed
    {
        return $this->elements[$key] ?? null;
    }

    /**
     * ArrayAccess Interface for $data[$key] = $value;
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->elements[] = $value;
        } else {
            $this->elements[$key] = $value;
        }
    }

    /**
     * ArrayAccess Interface for unset($data[$key]);
     */
    public function offsetUnset($key): void
    {
        unset($this->elements[$key]);
    }

    public function serialize()
    {
        return serialize($this->elements);
    }

    public function unserialize($data)
    {
        $this->elements = unserialize($data);
    }

    /**
     * Called after clone
     */
    public function __clone()
    {
        foreach ($this->elements as $index => $value) {
            $this->elements[$index] = $this->removeReferences($value);
        }
    }

    /**
     * Deep copy clone
     */
    private function removeReferences(mixed $value): mixed
    {
        if ($value instanceof ArrayAccess || is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->removeReferences($v);
            }

            return $value;
        }

        if (is_object($value)) {
            return clone $value;
        }

        return is_object($value) ? clone $value : $value;
    }

    public function __debugInfo()
    {
        return $this->elements;
    }
}
