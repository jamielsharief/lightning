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

use Lightning\Cache\Exception\InvalidArgumentException;

class FileCache extends AbstractCache
{
    private string $path;

    /**
     * Constructor
     *
     * @param string $path Cache folder
     * @param string $prefix
     * @param integer $timeout
     */
    public function __construct(string $path, string $prefix = '', int $timeout = 0)
    {
        $this->path = $path;
        $this->prefix = $prefix;

        $this->timeout = $timeout;

        if (! is_dir($this->path)) {
            mkdir($this->path, 0775, true);
        }
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
        $result = $default;
        $key = $this->createCacheKey($key);
        $path = $this->getPath($key);

        if (file_exists($path)) {
            $cached = unserialize(file_get_contents($path));

            if ($cached['ttl'] === null || $cached['ttl'] > time()) {
                $result = $cached['data'];
            }
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
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set($key, $value, $ttl = null)
    {
        $key = $this->createCacheKey($key);
        $ttl = $this->getDuration($ttl);

        $data = [
            'data' => $value,
            'ttl' => $ttl > 0 ? time() + $ttl : null
        ];

        return (bool) file_put_contents($this->getPath($key), serialize($data));
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
        $path = $this->getPath($key);

        return is_file($path) && unlink($path);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        $files = array_diff(scandir($this->path), ['..', '.']);

        $result = true;

        foreach ($files as $file) {
            $path = $this->path . '/' . $file;
            if (is_file($path)) {
                $result = $result && unlink($path);
            }
        }

        return $result;
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
        return $this->get($key) !== null;
    }

    private function getPath(string $key): string
    {
        return $this->path . '/' . $this->prefix . $key;
    }
}
