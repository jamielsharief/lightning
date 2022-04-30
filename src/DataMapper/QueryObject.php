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

namespace Lightning\DataMapper;

/**
 * Query Object
 *
 * @see https://www.martinfowler.com/eaaCatalog/queryObject.html
 */
class QueryObject
{
    private array $criteria = [];
    private array $options = [];

    /**
     * Constructor
     *
     * @param array $criteria
     * @param array $options
     */
    public function __construct(array $criteria = [], array $options = [])
    {
        $this->criteria = $criteria;
        $this->options = $options;
    }

    /**
     * Get the value of options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Gets an option
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $key, $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Sets an option value
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function setOption(string $key, $value): static
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Set the value of options
     *
     * @param array $options
     * @return static
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get the value of criteria
     *
     * @return array
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }

    /**
     * Set the value of criteria
     *
     * @param array $criteria
     * @return static
     */
    public function setCriteria(array $criteria): static
    {
        $this->criteria = $criteria;

        return $this;
    }
}
