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

use DateInterval;
use Psr\SimpleCache\CacheInterface;
use Lightning\Cache\Exception\InvalidArgumentException;

abstract class AbstractCache implements CacheInterface
{
    protected string $prefix = '';
    protected int $timeout = 0;

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
    {
        $result = ! empty($values);

        foreach ($values as $key => $value) {
            $result = $result && $this->set($key, $value, $ttl);
        }

        return $result;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
    {
        $result = ! empty($keys);

        foreach ($keys as $key) {
            $result = $result && $this->delete($key);
        }

        return $result;
    }

    /**
     * Checks the cache key and adds the prefix
     *
     * @param mixed $key
     * @return string
     */
    protected function createCacheKey($key): string
    {
        if (! is_string($key) || empty($key) || ! preg_match('/^[a-z0-9_-]+$/i', $key)) {
            throw new InvalidArgumentException(sprintf('Invalid key `%s`', $key));
        }

        return $this->prefix . $key;
    }

    /**
     * Gets the duration from TTL
     *
     * @param int|DateInterval|null $ttl
     * @return integer
     */
    protected function getDuration($ttl): int
    {
        if (is_null($ttl)) {
            return $this->timeout;
        } elseif (is_int($ttl)) {
            return $ttl;
        } elseif ($ttl instanceof DateInterval) {
            return (int) $ttl->format('%s');
        }

        throw new InvalidArgumentException('Invalid ttl value must be an int, null or a DateInterval instance');
    }
}
