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

use Lightning\ServiceObject\Exception\UnknownParameterException;

class Params
{
    /**
     * Container data
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Constructor
     *
     * @param array $data data to set
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Checks if a parameter exists
     *
     * @param string $name
     * @return boolean
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * Gets the state
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Gets a param
     *
     * @param string $name
     * @throws \Lightning\ServiceObject\Exception\UnknownParameterException
     * @return mixed
     */
    public function get(string $name): mixed
    {
        if (! isset($this->data[$name])) {
            throw new UnknownParameterException(sprintf('Unkown parameter `%s`', $name));
        }

        return $this->data[$name];
    }

    /**
     * Set a value of a param
     *
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function set(string $name, $value): static
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * Unset a param
     *
     * @param string $name
     * @return static
     */
    public function unset(string $name): static
    {
        unset($this->data[$name]);

        return $this;
    }
}
