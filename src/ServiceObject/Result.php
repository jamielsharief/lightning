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

namespace Lightning\ServiceObject;

use Stringable;
use JsonSerializable;

/**
 * Result [immutable]
 */
class Result implements JsonSerializable, Stringable
{
    /**
     * Constructor
     */
    public function __construct(private bool $success = true, private array $data = [])
    {
    }

    /**
     * Is the a Result a success?
     */
    public function isSuccess(): bool
    {
        return $this->success === true;
    }

    /**
     * Checks if the result has data
     */
    public function hasData(): bool
    {
        return ! empty($this->data);
    }

    /**
     * Gets the data for this result
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Gets the data for a specific key
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->data[$name] ?? $default;
    }

    /**
     * Checks if the key exists in the data
     */
    public function has(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Returns a new instance with this data
     */
    public function withData(array $data): static
    {
        $cloned = clone $this;
        $cloned->data = $data;

        return $cloned;
    }

    /**
     * Returns a new instance with success changed
     */
    public function withSuccess(bool $success): static
    {
        $cloned = clone $this;
        $cloned->success = $success;

        return $cloned;
    }

    /**
     * Returns the data to be serialized to JSON
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * PHP Stringable interface
     */
    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Converts this result to JSON
     */
    public function toString(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Converts this result to an array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data
        ];
    }
}
