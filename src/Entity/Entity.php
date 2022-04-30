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

namespace Lightning\Entity;

use ArrayAccess;
use InvalidArgumentException;

/**
 * Entity
 *
 * @internal Don't use constructor
 *
 * @see https://docs.oracle.com/cd/B10463_01/web.904/b10390/bc_awhatisaneo.htm
 */
class Entity extends AbstractEntity implements EntityInterface, ArrayAccess
{
    /**
     * Holds the fields and values for this Entity
     *
     * @var array
     */
    private array $fields = [];

    protected array $virtualFields = [];
    protected array $hiddenFields = [];

    protected array $errors = [];
    protected array $dirty = [];

    private array $cachedMethods = [];

    /**
     * Constructor, no arguments can be used as this is called by fromState
     */
    final public function __construct()
    {
        $this->initialize();
    }

    protected function initialize(): void
    {
    }

    /**
     * Create the Entity using data from an array
     *
     * @param array $data
     * @return self
     */
    public static function fromState(array $data): self
    {
        $entity = new static();
        foreach ($data as $property => $value) {
            $entity->set($property, $value);
        }
        $entity->clean();

        return $entity;
    }

    /**
     * Sets a value for this Entity
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value): void
    {
        $this->dirty[$key] = true;
        $this->fields[$key] = $this->mutate($key, $value);
    }

    /**
     * Sets a value for this Entity
     *
     * @param string|array $key
     * @param mixed $value
     * @return static
     */
    public function set($key, mixed $value = null): static
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->$k = $v;
            }
        } else {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * Calls a mutator
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    private function mutate(string $key, $value): mixed
    {
        $method = $this->getAccessorMethod($key, 'set');

        return method_exists($this, $method) ? $this->$method($value) : $value;
    }

    /**
     * Calls an accessor
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    private function access(string $key, $value): mixed
    {
        $method = $this->getAccessorMethod($key, 'get');

        return method_exists($this, $method) ? $this->$method($value) : $value;
    }

    /**
     * @param string $field
     * @return string
     */
    private function getAccessorMethod(string $field, string $type): string
    {
        if (isset($this->cachedMethods[$field])) {
            return $type . $this->cachedMethods[$field];
        }
        $this->cachedMethods[$field] = $method = (strpos($field, '_') !== false ? str_replace(' ', '', ucwords(str_replace('_', ' ', $field))) : $field);

        return $type . $method;
    }

    /**
     * Gets a value from this Entity
     *
     * @param string $key
     * @return mixed
     */
    public function &__get(string $key)
    {
        $value = null;

        if (array_key_exists($key, $this->fields)) {
            $value = &$this->fields[$key];
        }

        $value = $this->access($key, $value);

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
     * Checks if this Entity has a property
     *
     * @param string $key
     * @return boolean
     */
    public function __isset(string $key): bool
    {
        return isset($this->fields[$key]);
    }

    /**
     * Checks if this Entity has a property
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return $this->__isset($key);
    }

    /**
     * Unsets a property on this Entity
     *
     * @param string $property
     * @return void
     */
    public function __unset(string $property)
    {
        unset($this->fields[$property],$this->dirty[$property]);
    }

    /**
     * Unsets a property on this Entity
     *
     * @param string $property
     * @return void
     */
    public function unset(string $property): void
    {
        $this->__unset($property);
    }

    /**
     * Converts this Entity and any related objects to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->getVisibleProperties() as $property) {
            $value = $this->$property;

            if (is_array($value)) {
                $result[$property] = [];
                foreach ($value as $k => $v) {
                    $result[$property][$k] = $v instanceof EntityInterface ? $v->toArray() : $v;
                }
            } else {
                $result[$property] = $value instanceof EntityInterface ? $value->toArray() : $value;
            }
        }

        return $result;
    }

    /**
     * Gets the fields to return including virtual fields and exclude hidden fields
     *
     * @return array
     */
    private function getVisibleProperties(): array
    {
        $properties = array_merge(
            array_keys($this->fields), $this->virtualFields
        );

        return array_diff($properties, $this->hiddenFields);
    }

    /**
     * ArrayAcces Interface for isset($collection);
     *
     * @param mixed $key
     * @return bool result
     */
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->fields);
    }

    /**
     * ArrayAccess Interface for $collection[$key];
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->fields[$key] ?? null;
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
            throw new InvalidArgumentException('Field cannot be empty');
        }
        $this->fields[$key] = $value;
    }

    /**
     * ArrayAccess Interface for unset($collection[$key]);
     *
     * @param mixed $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        unset($this->fields[$key]);
    }

    /**
     * Sets an error for a field
     *
     * @param string $field
     * @param string $messages
     * @return static
     */
    public function setError(string $field, $messages): static
    {
        if (is_string($messages)) {
            $messages = [$messages];
        }
        foreach ($messages as $message) {
            $this->errors[$field][] = $message;
        }

        return $this;
    }

    /**
    * Gets the errors for a field
    *
    * @param string $field
    * @return array
    */
    public function getError(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Gets the errors for the Entity or a specific field
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Checks if a field has an error
     *
     * @param string $field
     * @return boolean
     */
    public function hasError(string $field): bool
    {
        return ! empty($this->errors[$field]);
    }

    /**
     * Checks if the Entity has errors
     *
     * @return boolean
     */
    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    /**
     * Marks the Entity as clean and reset the errors
     *
     * @return void
     */
    public function clean(): void
    {
        $this->dirty = [];
        $this->errors = [];
    }

    /**
     * Checks if this Entity or a specific field is dirty
     *
     * @param string|null $field
     * @return boolean
     */
    public function isDirty(?string $field = null): bool
    {
        if ($field) {
            return isset($this->dirty[$field]);
        }

        return ! empty($this->dirty);
    }

    /**
     * Gets the dirty field
     *
     * @return array
     */
    public function getDirty(): array
    {
        return array_keys($this->dirty);
    }
}
