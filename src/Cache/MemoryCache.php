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

namespace Lightning\Cache;

use Lightning\Cache\Exception\InvalidArgumentException;

/**
 * MemoryCache
 *
 * @internal TTL is ignored since this is more likely to be used in testing or a use during a single request
 */
class MemoryCache extends AbstractCache
{
    private array $container = [];

    /**
     * Constructory
     *
     * @param array $data Key value array of existing data if needed
     */
    public function __construct(array $data = [])
    {
        $this->container = $data;
    }
    /**
    * Fetches a value from the cache.
    *
    * @param string $key     The unique key of this item in the cache.
    * @param mixed  $default Default value to return if the key does not exist.
    *
    * @return mixed The value of the item from the cache, or $default in case of cache miss.
    *
    * @throws InvalidArgumentException
    *   MUST be thrown if the $key string is not a legal value.
    */
    public function get($key, $default = null)
    {
        $key = $this->createCacheKey($key);

        return $this->container[$key] ?? $default;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                 $key   The key of the item to store.
     * @param mixed                  $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set($key, $value, $ttl = null)
    {
        $key = $this->createCacheKey($key);
        $this->container[$key] = $value;

        return true;
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key)
    {
        $key = $this->createCacheKey($key);

        return array_key_exists($key, $this->container);
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete($key)
    {
        $key = $this->createCacheKey($key);

        $result = array_key_exists($key, $this->container);
        unset($this->container[$key]);

        return $result;
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        $this->container = [];

        return true;
    }

    /**
     * Increments the value of an integer in the cache
     *
     * @param string $key
     * @param integer $offset
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     * @return integer
     */
    public function increment(string $key, $offset = 1, $ttl = null): int
    {
        $this->createCacheKey($key);

        $result = $this->container[$key] ?? 0;
        $result = $result + $offset;

        return $this->container[$key] = $result;
    }

    /**
     * Decrements the value of an integer in the cache
     *
     * @param string $key
     * @param integer $offset
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     * @return integer
     */
    public function decrement(string $key, $offset = 1, $ttl = null): int
    {
        $this->createCacheKey($key);

        $result = $this->container[$key] ?? 0;
        $result = $result - $offset;

        return $this->container[$key] = $result;
    }
}
