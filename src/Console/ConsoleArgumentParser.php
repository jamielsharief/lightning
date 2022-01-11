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

use Prophecy\Argument;
use InvalidArgumentException;

/**
 * Parses the options and casts to desired types
 */
class ConsoleArgumentParser
{
    protected array $args;

    protected array $commandOptions = [];
    protected array $commandArguments = [];

    private array $parsedOptions = [];
    private array $parsedArguments = [];

    /**
    * Adds an option for this command
    *
    * @param string $name
    * @param array $options
    * @return self
    */
    public function addOption(string $name, array $options = []): self
    {
        $options += [
            'name' => $name,
            'short' => null,
            'default' => null,
            'required' => false,
            'type' => 'string',
            'description' => ''
        ];

        $this->commandOptions[$name] = $options;
        if ($options['short']) {
            $this->commandOptions[$options['short']] = $options;
        }

        return $this;
    }

    /**
     * Adds a argument for this command
     *
     * @param string $name
     * @param array $options
     * @return self
     */
    public function addArgument(string $name, array $options = []): self
    {
        $options += [
            'name' => $name,
            'default' => null,
            'required' => false,
            'type' => 'string',
            'description' => ''
        ];

        $this->commandArguments[$name] = $options;

        return $this;
    }

    /**
     * Parse the args
     *
     * @param array $args
     * @return Arguments
     */
    public function parse(array $args): Arguments
    {
        $this->args = $args;

        $this->parsedOptions = $this->parsedArguments = [];

        $arguments = [];
        while ($arg = array_shift($this->args)) {
            if ($this->isOption($arg)) {
                $this->parseOption($arg);
            } else {
                $arguments[] = $arg;
            }
        }

        foreach ($this->commandOptions as $name => $option) {
            if (isset($this->parsedOptions[$name]) || $name === $option['short']) {
                continue;
            }
            if ($option['required'] === true) {
                throw new InvalidArgumentException("The option `{$name}` is required");
            }

            $this->parsedOptions[$name] = $option['type'] === 'boolean' ? false : $option['default'];
        }

        if (empty($this->parsedOptions['help'])) {
            foreach ($this->commandArguments as $name => $argument) {
                $arg = $arguments ? array_shift($arguments) : $argument['default'];

                if (is_null($arg) && $argument['required'] === true) {
                    throw new InvalidArgumentException("Argument `{$name}` is required");
                }

                $this->parsedArguments[$name] = $argument['type'] === 'integer' ? (int) $arg : $arg;
            }
        }

        return $this->createArguments($this->parsedOptions, $this->parsedArguments);
    }

    /**
     * Factory method
     *
     * @param array $options
     * @param array $arguments
     * @return Arguments
     */
    private function createArguments(array $options = [], array $arguments = []): Arguments
    {
        return new Arguments($options, $arguments);
    }

    /**
     * @param string $arg
     * @return array
     */
    private function getNameAndValue(string $arg): array
    {
        $opt = ltrim($arg, '-');
        $value = null;

        if (strpos($opt, '=') !== false) {
            list($opt, $value) = explode('=', $opt, 2);
        }

        return [$opt, $value];
    }

    /**
     * @param string $arg
     * @return void
     */
    private function parseOption(string $arg): void
    {
        list($opt, $value) = $this->getNameAndValue($arg);

        $config = $this->commandOptions[$opt] ?? null;
        if (! $config) {
            throw new InvalidArgumentException("Unknown option `{$opt}`");
        }

        $opt = $config['name'];

        if ($config['type'] === 'boolean') {
            $this->parsedOptions[$opt] = true;

            return;
        }

        if (is_null($value)) {
            $value = $this->getNextArgumentFor($opt);
        }

        $value = $config['type'] === 'integer' ? (int) $value : $value;

        if (! isset($this->parsedOptions[$opt])) {
            $this->parsedOptions[$opt] = $value ;
        } elseif (is_array($this->parsedOptions[$opt])) {
            $this->parsedOptions[$opt][] = $value;
        } else {
            $previousValue = $this->parsedOptions[$opt];
            $this->parsedOptions[$opt] = [$previousValue,$value];
        }
    }

    /**
     * @param string $opt
     * @return string
     */
    private function getNextArgumentFor(string $opt): string
    {
        $value = array_shift($this->args);

        if (is_null($value) || substr($value, 0, 1) === '-') {
            throw new InvalidArgumentException("{$opt} was expecting a value");
        }

        return $value;
    }

    /**
     * @param string $arg
     * @return boolean
     */
    private function isOption(string $arg): bool
    {
        return substr($arg, 0, 1) === '-' && $arg !== '--';
    }

    /**
     * Generates the usage
     *
     * @param string $command
     * @return string
     */
    public function generateUsage(string $command): string
    {
        $usage = [$command];

        $hasOptionalOptions = false;

        foreach ($this->commandOptions as $option) {
            if ($option['required']) {
                $usage[] = sprintf('--%s', $option['name']);
            }

            if ($option['required'] === false && ! $hasOptionalOptions) {
                $usage[] = '[options]';
                $hasOptionalOptions = true;
            }
        }

        // Process Arguments
        foreach ($this->commandArguments as $argument) {
            $usage[] = $argument['required'] ? $argument['name'] : '[' . $argument['name'] . ']';
        }

        return implode(' ', $usage);
    }

    /**
     * Generates the option info for help
     *
     * @return array
     */
    public function generateOptions(): array
    {
        $options = [];
        foreach ($this->commandOptions as $option) {
            $name = $option['short'] ? sprintf('-%s,--%s', $option['short'], $option['name']) : sprintf('--%s', $option['name']);
            $options[$name] = $option['description'];
        }

        return $options;
    }

    /**
     * Generates the argument info for help
     *
     * @return array
     */
    public function generateArguments(): array
    {
        $args = [];
        foreach ($this->commandArguments as $argument) {
            $args[$argument['name']] = $argument['default'] ? sprintf('%s (default: "%s")', $argument['description'], $argument['default']) : $argument['description'];
        }

        return $args;
    }

    public function getArguments(): array
    {
        return $this->commandArguments;
    }

    public function getOptions(): array
    {
        return $this->commandOptions;
    }
}
