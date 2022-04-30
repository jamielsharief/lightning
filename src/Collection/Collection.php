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

namespace Lightning\Collection;

use Closure;
use Countable;
use ArrayAccess;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;

/**
 * Collection
 *
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    protected array $items;

    /**
     * Constructor
     *
     * @param iterable $items
     */
    final public function __construct(iterable $items, bool $preserveKeys = true)
    {
        if (is_array($items)) {
            $this->items = $preserveKeys ? $items : array_values($items);
        } else {
            $this->items = iterator_to_array($items, $preserveKeys);
        }
    }

    /**
     * Iterates through the collection, return false to break.
     *
     * @param callable $callback
     * @return static
     */
    public function each(callable $callback): static
    {
        foreach ($this->items as $key => $value) {
            if ($callback($value, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Iterates through the collection and creates a new collection based upon the results returned.
     *
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback): static
    {
        $result = [];
        foreach ($this->items as $key => $value) {
            $result[$key] = $callback($value, $key);
        }

        return new static($result);
    }

    /**
     * Reduces a list of values into a single a value
     *
     * @param callable $callback
     * @return mixed
     */
    public function reduce(callable $callback): mixed
    {
        $called = false;

        $result = null;
        foreach ($this->items as $key => $value) {
            if ($called) {
                $result = $callback($result, $value, $key);
            } else {
                $result = $value;
                $called = true;
            }
        }

        return $result;
    }

    /**
     * Finds the first item that matches the truth test
     *
     * @param callable $callback
     * @return mixed
     */
    public function find(callable $callback): mixed
    {
        foreach ($this->items as $key => $value) {
            if ($callback($value, $key) === true) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Iterates through the collection and creates a new collection based upon the results returned.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): static
    {
        $result = [];
        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                $result[$key] = $value;
            }
        }

        return new static($result);
    }

    /**
     * This is the inverse of filter
     *
     * $notInStock = $collection->reject(function ($book) {
     *       return $book->in_stock ===  true;
     *   });
     *
     * @param callable $callback
     * @return static
     */
    public function reject(callable $callback): static
    {
        $result = [];
        foreach ($this->items as $key => $value) {
            if ($callback($value, $key) === false) {
                $result[$key] = $value;
            }
        }

        return new static($result);
    }

    /**
     * Run truth tests on every item in the collection
     *
     * $collection->every(function ($book) {
     *    return $book->in_stock > 0;
     * });
     *
     * @param callable $callback
     * @return boolean
     */
    public function every(callable $callback): bool
    {
        foreach ($this->items as $key => $value) {
            if (! $callback($value, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check to see if at least one item matches the truth test
     *
     * $collection->some(function ($book) {
     *    return $book->in_stock > 0;
     * });
     *
     * @param callable $callback
     * @return boolean
     */
    public function some(callable $callback): bool
    {
        foreach ($this->items as $key => $value) {
            if ($callback($value, $key) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks to see if the Collection contains a value
     *
     * @param mixed $value
     * @return boolean
     */
    public function contains($value): bool
    {
        foreach ($this->items as $key => $actual) {
            if ($value === $actual) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extracts values from a single key (aka the pluck) and probably the most common reason to use
     * mapping.
     *
     * @param callable|string $path e.g. authors.name or callable
     * @return static
     */
    public function extract($path): static
    {
        if (is_string($path)) {
            $path = $this->createCallback($path);
        }

        return $this->map($path);
    }

    /**
     * Chunks the Collection
     *
     * @param integer $chunkSize
     * @return static
     */
    public function chunk(int $chunkSize): static
    {
        return new static(array_chunk($this->toArray(), $chunkSize));
    }

    /**
     * Sorts the Collection by the keys
     *
     * @param int $direction
     * @param int $type
     * @return static
     */
    public function sort(int $direction = SORT_ASC, int $type = SORT_REGULAR): static
    {
        $items = $this->toArray();

        $direction === SORT_DESC ? arsort($items, $type) : asort($items, $type);

        return new static($items);
    }

    /**
     * Sorts a Collection by a value or using a callback
     *
     * @param string|callable $path e.g. id, user.postcode or callable
     * @param int $direction e.g. SORT_ASC
     * @param int $type e.g. SORT_NUMERIC
     * @return static
     */
    public function sortBy($path, int $direction = SORT_ASC, int $type = SORT_REGULAR): static
    {
        if (is_string($path)) {
            $path = $this->createCallback($path);
        }

        $result = [];
        foreach ($this->items as $key => $value) {
            $result[$key] = $path($value, $key);
        }

        $direction === SORT_DESC ? arsort($result, $type) : asort($result, $type);

        foreach ($this->items as $key => $value) {
            $result[$key] = $this->items[$key];
        }

        return new static($result);
    }

    /**
     * Groups the collection results
     *
     * $collection->groupBy('category');
     * $collection->groupBy('user.status');
     *
     * // This will group data by even and odd id numbers
     *   $collection->groupBy(function ($book) {
     *      return $book->id % 2 == 0 ? 'even' : 'odd';
     *   })
     *
     * @param callable|string $path e.g. authors.name or callable
     * @return static
     */
    public function groupBy($path): static
    {
        if (is_string($path)) {
            $path = $this->createCallback($path);
        }

        $result = [];
        foreach ($this->items as $value) {
            $result[$path($value)][] = $value;
        }

        return new static($result);
    }

    /**
     * Indexes the collection results when you elements have a single result with a unique value,
     * for example, the id field.
     *
     * $collection->indexBy('id');
     * $collection->indexBy('user.id');
     * @param callable|string $path e.g. authors.name or callable
     * @return static
     */
    public function indexBy($path): static
    {
        if (is_string($path)) {
            $path = $this->createCallback($path);
        }

        $result = [];
        foreach ($this->items as $value) {
            $result[$path($value)] = $value;
        }

        return new static($result);
    }

    /**
    * Gets the first item with the smallest value
    *
    * $collection->min('author.score');
    * $collection->min('id');
    *
    * $collection->min(function ($book) {
    *       return $book->author->score;
    *   });
    *
    * @param string|callable $path
    * @return mixed
    */
    public function min($path): mixed
    {
        return $this->sortBy($path, SORT_ASC)->first();
    }

    /**
    * Gets the last item with the largest value
    *
    * $collection->max('author.score');
    * $collection->max('id');
    *
    * $collection->max(function ($book) {
    *       return $book->author->score;
    *   });
    *
    * @param string|callable $path
    * @return mixed
    */
    public function max($path): mixed
    {
        return $this->sortBy($path, SORT_ASC)->last();
    }

    /**
    * Gets the total of a field or return callback
    *
    * $collection->sumOf('author.score');
    * $collection->sumOf('id');
    *
    * $collection->sumOf(function ($book) {
    *       return $book->author->score;
    *   });
    *
    * @param string|callable $path
    * @return mixed
    */
    public function sumOf($path): mixed
    {
        if (is_string($path)) {
            $path = $this->createCallback($path);
        }
        $sum = 0;
        foreach ($this->items as $key => $value) {
            $sum += $path($value, $key);
        }

        return $sum;
    }

    /**
    * Gets the average of a field or return callback
    *
    * $collection->avg('author.score');
    * $collection->avg('id');
    *
    * $collection->avg(function ($book) {
    *       return $book->author->score;
    *   });
    *
    * @param string|callable $path
    * @return int|float
    */
    public function avg($path)
    {
        $values = $this->extract($path)->toArray();
        $count = count($values);

        return $count > 0 ? array_sum($values) / $count : null;
    }

    /**
    * Gets the median of a field or return callback
    *
    * $collection->median('author.score');
    * $collection->median('id');
    *
    * $collection->median(function ($book) {
    *       return $book->author->score;
    *   });
    *
    * @param string|callable $path
    * @return int|float|null
    */
    public function median($path)
    {
        $values = $this->extract($path)->toArray();
        $count = count($values);

        if ($count > 1) {
            sort($values);
            $middle = (int) ($count / 2);

            if ($count % 2) {
                return $values[$middle];
            }

            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return null;
    }

    /**
     * Counts by a field and groups the results
     *
     * $collection->countBy('author.status');
     *
     * // ['odd'=>2,'even'=>3]
     *  $collection->countBy(function ($book) {
     *      return $book->id % 2 == 0 ? 'even' : 'odd';
     *   })
     *
     * @param string|callable $path
     * @return array
     */
    public function countBy($path): array
    {
        if (is_string($path)) {
            $path = $this->createCallback($path);
        }
        $results = [];
        foreach ($this->items as $key => $value) {
            $result = $path($value, $key);
            if (! isset($results[$result])) {
                $results[$result] = 0;
            }
            $results[$result] = $results[$result] + 1;
        }

        return $results;
    }

    /**
     *  Gets this Collection as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Gets back the Collection as a list array (ie. keys are not preserved)
     *
     * @return array
     */
    public function toList(): array
    {
        return array_values($this->items);
    }

    /**
     * Gets the count of items in the Collection
     *
     * @return integer
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Checks if there are items in the Collection
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Gets the first item in the Collection
     *
     * @return mixed
     */
    public function first(): mixed
    {
        $result = null;
        if ($this->items) {
            $key = array_key_first($this->items);
            $result = $this->items[$key];
        }

        return $result;
    }

    /**
     * Gets the last item in the Collection
     *
     * @return mixed
     */
    public function last(): mixed
    {
        $result = null;
        if ($this->items) {
            $key = array_key_last($this->items);
            $result = $this->items[$key];
        }

        return $result;
    }

    /**
     * JsonSerializable Interface for json_encode($collection). Returns the properties that will be serialized as
     * json
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * IteratorAggregate Interface
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Create a Closure which can resolve a path e.g. auhors.id
     *
     * @param string $from
     * @return Closure
     */
    private function createCallback(string $from): Closure
    {
        return function ($item) use ($from) {
            $result = null;
            foreach (explode('.', $from) as $property) {
                if (is_array($item)) {
                    if (! isset($item[$property])) {
                        return null;
                    }
                    $result = $item[$property];
                } elseif (is_object($item)) {
                    if (! isset($item->$property)) {
                        return null;
                    }
                    $result = $item->$property;
                }
                $item = $result;
            }

            return $result;
        };
    }

    /**
    * ArrayAcces Interface for isset($collection);
    *
    * @param mixed $key
    * @return bool result
    */
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * ArrayAccess Interface for $collection[$key];
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        return $this->items[$key] ?? null;
    }

    /**
     * ArrayAccess Interface for $collection[$key] = $value;
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * ArrayAccess Interface for unset($collection[$key]);
     *
     * @param mixed $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        unset($this->items[$key]);
    }
}
