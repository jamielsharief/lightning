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

namespace Lightning\ServiceObject;

use Stringable;
use JsonSerializable;

class Result implements JsonSerializable, Stringable
{
    private bool $success;
    private array $data;

    /**
     * Constructor
     *
     * @param boolean $success
     * @param array $data
     */
    public function __construct(bool $success = true, array $data = [])
    {
        $this->success = $success;
        $this->data = $data;
    }

    /**
     * Checks if the result is a success
     *
     * @return boolean
     */
    public function isSuccess(): bool
    {
        return $this->success === true;
    }

    /**
     * Gets the success success
     *
     * @return boolean
     */
    public function getSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Sets the
     *
     * @param boolean $success
     * @return static
     */
    public function setSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    /**
     * Returns a new Result object with sucess set to this
     *
     * @param boolean $success
     * @return static
     */
    public function withSuccess(bool $success): self
    {
        return (clone $this)->setSuccess($success);
    }

    /**
     * Checks if the result is an error
     *
     * @return boolean
     */
    public function isError(): bool
    {
        return $this->success === false;
    }

    /**
     * Checks if the result has data
     *
     * @return boolean
     */
    public function hasData(): bool
    {
        return ! empty($this->data);
    }

    /**
     * Gets the data for this result or data from a specific property
     *
     * @internal it is quite important to get a specific property to reduce code on the other side
     *
     * @param string|null $property
     * @return mixed
     */
    public function getData(string $property = null)
    {
        if ($property === null) {
            return $this->data;
        }

        return $this->data[$property] ?? null;
    }

    /**
     * Sets the Data
     *
     * @param array $data
     * @return static
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Returns a new instance with this data
     *
     * @param array $data
     * @return static
     */
    public function withData(array $data): self
    {
        return (clone $this)->setData($data);
    }

    /**
     * Returns the data to be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * PHP Stringable interface
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Converts this result to JSOn
     *
     * @return string
     */
    public function toString(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Converts this result to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data
        ];
    }
}
