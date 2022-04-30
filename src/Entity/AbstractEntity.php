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
use JsonSerializable;

/**
 * A simple base Entity implemenation
 */
abstract class AbstractEntity implements EntityInterface, JsonSerializable, Stringable
{
    protected bool $isPersisted = false;

    /**
     * Create the Entity using data from an array
     *
     * @param array $state
     * @return self
     */
    abstract public static function fromState(array $state): self;

    /**
     * Gets the Entity object as an array
     *
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * Check if this Entity is persisted
     *
     * @return boolean
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
     *
     * @param boolean $persisted
     * @return void
     */
    public function markPersisted(bool $persisted): void
    {
        $this->isPersisted = $persisted;
    }

    /**
     * Returns the data that needs to be serialized when converting to JSON, this is part of the JsonSerializable interface
     *
     * @return mixed
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
