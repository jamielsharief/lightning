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
 * @license     https://opentable.org/licenses/mit-license.php MIT License
 */

namespace Lightning\Database;

use Stringable;
use ArrayAccess;
use JsonSerializable;

class Row implements ArrayAccess, JsonSerializable, Stringable
{
    private array $data = [];

    final public function __construct()
    {
    }

    /**
     * Creates the Row object using the row from the database
     *
     * @param array $state
     * @return Row
     */
    public static function fromState(array $state): Row
    {
        $row = new static();
        $row->data = $state;

        return $row;
    }

    /**
     * Sets a value
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Sets a value or all
     *
     * @param string|array $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value = null): self
    {
        if (is_array($key)) {
            $this->data = $key;
        } else {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * Gets a value
     *
     * @param string $key
     * @return mixed
     */
    public function &__get(string $key)
    {
        $value = null;

        if (array_key_exists($key, $this->data)) {
            $value = &$this->data[$key];
        }

        return $value;
    }

    /**
     * Gets a value
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->__get($key);
    }

    /**
     * Checks if a property set
     *
     * @param string $key
     * @return boolean
     */
    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Checks if this object has a property
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return $this->__isset($key);
    }

    /**
     * Unsets a property
     *
     * @param string $property
     * @return void
     */
    public function __unset(string $property)
    {
        unset($this->data[$property],$this->dirty[$property]);
    }

    /**
     * Unsets a property
     *
     * @param string $property
     * @return void
     */
    public function unset(string $property): void
    {
        $this->__unset($property);
    }

    /**
     * JsonSerializable Interface
     *
     * @return void
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Converts this object recrusively to an array (if other rows were later added)
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->data as $property => $value) {
            if (is_array($value)) {
                $result[$property] = [];
                foreach ($value as $k => $v) {
                    $result[$property][$k] = $v instanceof Row ? $v->toArray() : $v;
                }
            } else {
                $result[$property] = $value instanceof Row ? $value->toArray() : $value;
            }
        }

        return $result;
    }

    /**
     * Returns a string representation of this object
     *
     * @return string
     */
    public function toString(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Returns a string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * ArrayAcces Interface for isset($collection);
     *
     * @param mixed $key
     * @return bool result
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * ArrayAccess Interface for $collection[$key];
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * ArrayAccess Interface for $collection[$key] = $value;
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
     * ArrayAccess Interface for unset($collection[$key]);
     *
     * @param mixed $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }
}
