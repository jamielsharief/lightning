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

use Stringable;
use ReflectionClass;
use JsonSerializable;
use ReflectionProperty;
use ReflectionException;

/**
 * AbstractEntity
 *
 * @internal this is intentionally abstract despite having no abstract methods to prevent creating an object of this class. Private properties
 * are assumed to be the state, so only those are reflected.
 */
abstract class AbstractEntity implements EntityInterface, JsonSerializable, Stringable
{
    protected bool $isPersisted = false;

    /**
     * Create the Entity using data from an array
     * @internal only private properties are reflected, protected properties are not.
     */
    public static function fromState(array $state): self
    {
        $entity = new static();

        foreach ($state as $property => $value) {
            try {
                $reflectionProperty = new ReflectionProperty($entity, $property);
                if ($reflectionProperty->isPrivate()) {
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($entity, $value);
                }
            } catch (ReflectionException) {
            }
        }

        return $entity;
    }

    /**
     * Converts the entity to its state
     */
    public function toState(): array
    {
        $reflection = new ReflectionClass($this);
        $data = [];
        foreach ($reflection->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $property->setAccessible(true); // From 8.1 this has no effect and is not required
            $value = $property->getValue($this);

            if ($value instanceof EntityInterface) {
                $value = $value->toState();
            } elseif (is_array($value)) {
                $value = array_map(function ($item) {
                    return $item instanceof EntityInterface ? $item->toState() : $item;
                }, $value);
            }

            $data[$property->getName()] = $value;
        }

        return $data;
    }

    /**
     * Gets the entity to an array using the getters
     */
    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);
        $data = [];
        foreach ($reflection->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED) as $property) {
            $name = $property->getName();
            $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

            if (method_exists($this, $method)) {
                $value = $this->$method();

                if ($value instanceof AbstractEntity) {
                    $value = $value->toArray();
                } elseif (is_array($value)) {
                    $value = array_map(function ($item) {
                        return $item instanceof AbstractEntity ? $item->toArray() : $item;
                    }, $value);
                }

                $data[$name] = $value;
            }
        }

        return $data;
    }

    /**
     * Check if this Entity is persisted
     */
    public function isNew(): bool
    {
        return $this->isPersisted === false;
    }

    /**
     * Sets the Persisted status of this Entity
     *
     * @internal Using set clashes between enity and might be confusing. Also I used boolean because when an entity is removed from storage,
     * we need to change the persisted state.
     */
    public function markPersisted(bool $persisted): void
    {
        $this->isPersisted = $persisted;
    }

    /**
     * Returns the data that needs to be serialized when converting to JSON, this is part of the JsonSerializable interface
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Returns a this Entity as a string
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }
    /**
     * Get this object as a string
     *
     * @return string
     */
    public function toString(): string
    {
        return json_encode($this->toArray());
    }
}
