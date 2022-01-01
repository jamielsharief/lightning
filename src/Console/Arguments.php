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

namespace Lightning\Console;

class Arguments
{
    protected array $arguments;
    protected array $options;

    /**
     * Constructor
     *
     * @param array $options
     * @param array $arguments
     */
    public function __construct(array $options = [], array $arguments = [])
    {
        $this->options = $options;
        $this->arguments = $arguments;
    }

    /**
     * Get the value of arguments
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Set the value of arguments
     *
     * @param array $arguments
     * @return self
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
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
     * Set the value of options
     *
     * @param array $options
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Gets an option that was provided to the command line script
     *
     * @param string $option
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $option, $default = null)
    {
        return $this->options[$option] ?? $default;
    }

    /**
     * Gets an argument that was provided to the command line script
     *
     * @param string $argument
     * @param mixed $default
     * @return mixed
     */
    public function getArgument(string $argument, $default = null)
    {
        return $this->arguments[$argument] ?? $default;
    }

    /**
     * Checks if an option is defined and not null
     *
     * @param string $name
     * @return boolean
     */
    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    /**
     * Checks if an argument exists
     *
     * @param string $name
     * @return boolean
     */
    public function hasArgument(string $name): bool
    {
        return isset($this->arguments[$name]);
    }
}
