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

use Closure;
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
 * supped up array object with filter,map, reduce, min and max, as well sort and slicing. Keep bloat to minimum.
 * Rather than a component or seperate package, its something that can be reused and extended.
 * So dont polute the method names either. For example if you need index by or group by, this can be done
 * with reduce.
 *
 * Naming issues:
 * - Keys and Values, its standard in similar objects in other languages without get prefix even if they
 * use get prefix for other things. The array functions are similar as well. So decided to got without
 * the prefix.
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
    public function each(callable $callback): static
    {
        foreach ($this->elements as $key => $value) {
            if ($callback($value, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Applies the callback to the data and returns a new collection
     */
    public function map(callable $callback): static
    {
        return new static(
            array_map($callback, $this->elements)
        );
    }

    /**
     * Filters the collection using a callback and returns a new collection
     */
    public function filter(callable $callback): static
    {
        return new static(
            array_filter($this->elements, $callback, ARRAY_FILTER_USE_BOTH)
        );
    }

    /**
     * Iteratively reduce the collection to a single value using a callback
     */
    public function reduce(closure $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->elements, $callback, $initial);
    }

    /**
     * Extracts a slice of the Collection
     */
    public function slice(int $offset, ?int $length = null): static
    {
        return new static(
            array_slice($this->elements, $offset, $length)
        );
    }

    /**
     * Split the collection into chunks
     */
    public function chunk(int $length, bool $preserveKeys = false): array
    {
        return array_map(function (array $chunk) use ($preserveKeys) {
            return new static($chunk, $preserveKeys);
        }, array_chunk($this->elements, $length, $preserveKeys));
    }

    /**
    * Sorts the Collection in ascending order by the value returned by the callback
    */
    public function sort(?closure $callback = null, int $direction = SORT_ASC, int $flags = SORT_REGULAR): static
    {
        if ($callback) {
            return $this->sortBy($callback, $direction, $flags);
        }

        $function = $direction === SORT_DESC ? 'arsort' : 'asort';
        $function($this->elements, $flags);

        return $this;
    }

    private function sortBy(closure $callback, int $direction = SORT_ASC, int $flags = SORT_REGULAR): static
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

        $this->elements = $result;

        return $this;
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
    public function min(?closure $closure = null): mixed
    {
        return $closure ? $this->sortBy($closure)->get() : min($this->elements);
    }

    /**
     * Gets the element from the collection which matches the max value
     */
    public function max(?closure $closure = null): mixed
    {
        return $closure ? $this->sortBy($closure)->reverse()->get() : max($this->elements);
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
     * array_keys function for Collection object
     */
    public function keys(): array
    {
        return array_keys($this->elements);
    }

    /**
     * array values function for collection object
     */
    public function values(): array
    {
        return array_values($this->elements);
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
