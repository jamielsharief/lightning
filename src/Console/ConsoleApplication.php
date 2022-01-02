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

class ConsoleApplication implements CommandInterface
{
    /**
     * Name of this command, when working with sub commands you can use spaces for example
     * `migrate up` this will then show up in the help and allow you to use this properly
     *
     * @var string
     */
    protected string $name = 'unkown';

    /**
     * Description for this command
     *
     * @var string
     */
    protected string $description = '';

    /**
     * Default error code.
     *
     * @var int
     */
    public const ERROR = 1;

    /**
     * Default success code.
     *
     * @var int
     */
    public const SUCCESS = 0;

    /**
     * List of commands with description
     *
     * @var array
     */
    protected array $commands = [];

    /**
     * Undocumented variable
     *
     * @var AbstractCommand[]
     */
    protected array $instances = [];

    protected ConsoleIo $io;

    /**
     * Console IO
     *
     * @param ConsoleIo $io
     */
    public function __construct(ConsoleIo $io)
    {
        $this->io = $io;
    }

    /**
    * Gets the name of this Command
    *
    * @return string
    */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the description for this Command
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Factory method
     *
     * @return ConsoleHelpFormatter
     */
    private function createHelpFormatter(): ConsoleHelpFormatter
    {
        return new ConsoleHelpFormatter();
    }

    /**
     * Sets the name
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets the description
     *
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Adds a command
     *
     * @param CommandInterface $command
     * @return self
     */
    public function add(CommandInterface $command): self
    {
        $names = explode(' ', $command->getName());
        $name = end($names);

        $this->commands[$name] = $command->getDescription();
        $this->instances[$name] = $command;

        return $this;
    }

    /**
     * Runs the Console Application with the arguments
     *
     * @param array $args
     * @return integer
     */
    public function run(array $args): int
    {
        $file = array_shift($args);

        $subCommand = array_shift($args);
        if (! $subCommand || substr($subCommand, 0, 1) === '-') {
            $this->displayHelp();

            return self::SUCCESS;
        }

        if (! isset($this->commands[$subCommand])) {
            $this->io->err(sprintf('`%s` is not a %s command', $subCommand, $this->name));

            return self::ERROR;
        }

        array_unshift($args, $file);

        return $this->instances[$subCommand]->run($args);
    }

    /**
     * Displays the HELP
     *
     * @return void
     */
    private function displayHelp(): void
    {
        $help = $this->createHelpFormatter();
        if (! empty($this->description)) {
            $help->setDescription($this->description);
        }

        $help->setUsage([sprintf('%s <command> [options] [arguments]', $this->name)]);
        $help->setCommands($this->commands);

        $this->io->out($help->generate());
    }
}
