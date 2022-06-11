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

namespace Lightning\Console;

use Lightning\Console\Exception\StopException;

abstract class AbstractCommand implements CommandInterface
{
    /**
     * Default error code.
     */
    public const ERROR = 1;

    /**
     * Default success code.
     */
    public const SUCCESS = 0;

    /**
     * Name of this command, when working with sub commands you can use spaces for example
     * `migrate up` this will then show up in the help and allow you to use this properly
     */
    protected string $name = 'unkown';

    /**
     * Description for this command
     */
    protected string $description = '';

    /**
     * Constructor
     * @internal changed to more DI friendly
     */
    public function __construct(protected ConsoleArgumentParser $parser, protected ConsoleIo $io)
    {
    }

    /**
     * Adds the default options to the argument parser
     */
    private function addDefaultOptions(): void
    {
        $this->addOption('help', [
            'name' => 'help',
            'short' => 'h',
            'description' => 'Displays this help message',
            'type' => 'boolean',
            'required' => false
        ]);

        $this->addOption('verbose', [
            'name' => 'verbose',
            'short' => 'v',
            'description' => 'Displays additional output (if available)',
            'type' => 'boolean',
            'required' => false
        ]);

        $this->addOption('quiet', ['name' => 'quiet',
            'short' => 'q',
            'description' => 'Does not display output',
            'type' => 'boolean',
            'required' => false
        ]);
    }

    /**
     * This is a hook that is called when the Command is run
     */
    protected function initialize(): void
    {
    }

    /**
    * Gets the name of this Command
    */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the description for this Command
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Factory method
     */
    private function createHelpFormatter(): ConsoleHelpFormatter
    {
        return new ConsoleHelpFormatter();
    }

    /**
     * Adds an option for this command
     */
    public function addOption(string $name, array $options = []): static
    {
        $this->parser->addOption($name, $options);

        return $this;
    }

    /**
     * Adds a argument for this command
     */
    public function addArgument(string $name, array $options = []): static
    {
        $this->parser->addArgument($name, $options);

        return $this;
    }

    /**
     * Place your command logic here
     */
    abstract protected function execute(Arguments $args, ConsoleIo $io);

    /**
     * Exits the command without an error
     *
     * @throws StopException
     */
    public function exit(): void
    {
        throw new StopException('Command exited', self::SUCCESS);
    }

    /**
     * Aborts this command
     */
    public function abort(int $code = self::ERROR): void
    {
        throw new StopException('Command aborted', $code);
    }

    /**
     * Runs the command
     */
    public function run(array $args): int
    {
        $this->addDefaultOptions();
        $this->initialize();

        array_shift($args);

        // Parse arguments
        $arguments = $this->parser->parse($args);

        if ($arguments->getOption('help') === true) {
            $this->displayHelp();

            return self::SUCCESS;
        }

        try {
            return $this->execute($arguments, $this->io) ?: self::SUCCESS;
        } catch (StopException $exception) {
            return $exception->getCode();
        }
    }

    /**
     * Outputs a message or array of messages to stdout
     */
    public function out($message = '', int $newLines = 1): static
    {
        $this->io->out($message, $newLines, ConsoleIo::NORMAL);

        return $this;
    }

    /**
     * Outputs a message or array of messages to stderr
     */
    public function error($message = '', int $newLines = 1): static
    {
        $this->io->err($message, $newLines);

        return $this;
    }

    /**
     * Outputs a message or array of messages to stdout when verbose option is provided
     */
    public function verbose($message = '', int $newLines = 1): static
    {
        $this->io->out($message, $newLines, ConsoleIo::VERBOSE);

        return $this;
    }

    /**
     * Outputs a message or array of messages to stdout even if quiet option is provided
     */
    public function quiet($message = '', int $newLines = 1): static
    {
        $this->io->out($message, $newLines, ConsoleIo::QUIET);

        return $this;
    }

    /**
     * Displays the help for this Command
     */
    private function displayHelp(): void
    {
        $help = $this->createHelpFormatter();
        if (! empty($this->description)) {
            $help->setDescription($this->description);
        }

        $help->setUsage([$this->parser->generateUsage($this->name)])
            ->setOptions($this->parser->generateOptions())
            ->setArguments($this->parser->generateArguments());

        $this->out($help->generate());
    }

    /**
     * Displays a formatted error message and stops the execution
     */
    public function throwError(string $title, string $message = null, int $code = self::ERROR): void
    {
        $this->io->err("\n<alert> ERROR </alert> <lightYellow>{$title}</lightYellow>\n" . $message);

        throw new StopException($title, $code);
    }

    /**
     * Get the ConsoleIO object
     */
    public function getConsoleIo(): ConsoleIo
    {
        return $this->io;
    }
}
