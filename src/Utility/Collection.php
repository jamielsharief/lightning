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
 * This is not suppose to be full collection class, e.g. median, average, some etc. This is more a base
 * supped up array object with filter,map, reduce, min and max, as well sort and slicing. No chunking and
 * bloat. Rather than a component or seperate package, its something that can be reused and extended.
 * So dont polute the method names either. For example if you need index by or group by, this can be done
 * with reduce.
 */
class Collection implements ArrayAccess, Countable, JsonSerializable, IteratorAggregate, Stringable, Serializable
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
    public function get(string|int $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            $key = array_key_first($this->elements);
        }

        return $this->elements[$key] ?? $default;
    }

    /**
     * Unsets an element using a key
     */
    public function unset(string|int $key): static
    {
        unset($this->elements[$key]);

        return $this;
    }

    /**
     * Checks if the collection key exists
     */
    public function has(string|int $key): bool
    {
        return array_key_exists($key, $this->elements);
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
     * Check if the collection contains the element
     */
    public function contains(mixed $element): bool
    {
        return in_array($element, $this->elements, true);
    }

    /**
     * Finds the element in the collection and returns the index or key
     */
    public function indexOf(mixed $element): mixed
    {
        $index = array_search($element, $this->elements, true);

        return $index === false ? null : $index;
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
     * Reduces a list of values into a single a value
     */
    public function reduce(callable $callback): mixed
    {
        $called = false;

        $result = null;
        foreach ($this->elements as $key => $value) {
            $result = $called ? $callback($result, $value, $key) : $value;
            $called = true;
        }

        return $result;
    }

    /**
     * Extracts a slice of the Collection
     */
    public function slice(int $offset, ?int $length = null): static
    {
        return new static(array_slice($this->elements, $offset, $length));
    }

    /**
    * Sorts the Collection in ascending order by the value returned by the callback
    */
    public function sort(int $direction = SORT_ASC, int $flags = SORT_REGULAR): static
    {
        $function = $direction === SORT_DESC ? 'arsort' : 'asort';
        $elements = $this->elements;
        $function($elements, $flags);

        return new static($elements);
    }

    public function sortBy(callable $callback, int $direction = SORT_ASC, int $flags = SORT_REGULAR): static
    {
        $function = $direction === SORT_DESC ? 'arsort' : 'asort';

        $result = [];
        foreach ($this->elements as $key => $value) {
            $result[$key] = $callback($value, $key);
        }

        $function($result, $flags);

        foreach ($this->elements as $key => $value) {
            $result[$key] = $this->elements[$key];
        }

        return new static($result);
    }

    /**
     * Gets the collection in reverse order
     */
    public function reverse(): static
    {
        return new static(array_reverse($this->elements));
    }

    /**
     * Gets the element from the collection which matches the min value
     */
    public function min(callable $callback): mixed
    {
        return $this->sortBy($callback)->get();
    }

    /**
     * Gets the element from the collection which matches the max value
     */
    public function max(callable $callback): mixed
    {
        return $this->sortBy($callback)->reverse()->get();
    }

    /**
     * Get the collection as an array
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * Get the values of the collection (without keys)
     */
    public function toList(): array
    {
        return array_values($this->elements);
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

    public function serialize()
    {
        return serialize($this->elements);
    }

    public function unserialize(string $data)
    {
        return unserialize($data);
    }

    public function __serialize(): array
    {
        return $this->elements;
    }

    public function __unserialize(array $data): void
    {
        $this->elements = $data;
    }

    public function __debugInfo()
    {
        return $this->elements;
    }
}
