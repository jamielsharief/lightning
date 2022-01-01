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

namespace Lightning\Cache;

use Redis;

class RedisCache extends AbstractCache
{
    private Redis $redis;

    /**
     * Constructor
     *
     * @param Redis $redis
     * @param string $prefix
     * @param integer $timeout
     */
    public function __construct(Redis $redis, string $prefix = '', int $timeout = 0)
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
        $this->timeout = $timeout;
    }

    /**
    * Fetches a value from the cache.
    *
    * @param string $key     The unique key of this item in the cache.
    * @param mixed  $default Default value to return if the key does not exist.
    *
    * @return mixed The value of the item from the cache, or $default in case of cache miss.
    *
    * @throws \Psr\SimpleCache\InvalidArgumentException
    *   MUST be thrown if the $key string is not a legal value.
    */
    public function get($key, $default = null)
    {
        $key = $this->createCacheKey($key);
        $result = $this->redis->get($key);
        if ($result === false) {
            $result = $default;
        }

        return $result;
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
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set($key, $value, $ttl = null)
    {
        $key = $this->createCacheKey($key);
        $ttl = $this->getDuration($ttl);

        return $ttl === 0 ? $this->redis->set($key, $value) : $this->redis->setex($key, $ttl, $value);
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
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key)
    {
        $key = $this->createCacheKey($key);

        return $this->redis->get($key) !== false;
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete($key)
    {
        $key = $this->createCacheKey($key);

        return $this->redis->del($key) > 0;
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * TODO: Add prefix support, here will need to remove just those matching prefix
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        $keys = $this->redis->keys($this->prefix . '*');

        $result = true;

        foreach ($keys as $key) {
            $result = $this->redis->del($key) > 0 && $result;
        }

        return $result;
    }

    /**
     * Increments the value of an integer in the cache and updates the expiry time
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
        $key = $this->createCacheKey($key);
        $ttl = $this->getDuration($ttl);

        $result = $this->redis->incrBy($key, $offset);

        if ($ttl) {
            $this->redis->expire($key, $ttl);
        }

        return $result;
    }

    /**
     * Decrements the value of an integer in the cache and updates the expiry time
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
        $key = $this->createCacheKey($key);
        $ttl = $this->getDuration($ttl);

        $result = $this->redis->decrBy($key, $offset);

        if ($ttl) {
            $this->redis->expire($key, $ttl);
        }

        return $result;
    }
}
