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

use Lightning\Console\Exception\StopException;

abstract class AbstractCommand implements CommandInterface
{
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

    protected ConsoleArgumentParser $parser;
    protected ConsoleIo $io;

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
     * Constructor, which sets depdencies and calls the initialize hook. If you overide the constructor with additional
     * dependencies, remember to call the parent.
     *
     * @param ConsoleArgumentParser $parser
     * @param ConsoleIo $io
     */
    public function __construct(ConsoleArgumentParser $parser, ConsoleIo $io)
    {
        $this->parser = $parser;
        $this->io = $io;

        $this->addDefaultOptions();
        $this->initialize();
    }

    /**
     * Adds the default options to the argument parser
     *
     * @return void
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
     * This is a hook that is called when the Command is created
     *
     * @return void
     */
    protected function initialize(): void
    {
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
     * Adds an option for this command
     *
     * @param string $name
     * @param array $options
     * @return static
     */
    public function addOption(string $name, array $options = []): self
    {
        $this->parser->addOption($name, $options);

        return $this;
    }

    /**
     * Adds a argument for this command
     *
     * @param string $name
     * @param array $options
     * @return static
     */
    public function addArgument(string $name, array $options = []): self
    {
        $this->parser->addArgument($name, $options);

        return $this;
    }

    /**
     * Place your command logic here
     *
     * @return never|int
     */
    abstract protected function execute(Arguments $args, ConsoleIo $io);

    /**
     * Causes for the command
     * @throws StopException
     * @return void
     */
    public function exit(): void
    {
        throw new StopException('Command exited', self::SUCCESS);
    }

    /**
     * Aborts this command
     *
     * @param int $code
     * @throws StopException
     * @return void
     */
    public function abort(int $code = self::ERROR): void
    {
        throw new StopException('Command aborted', $code);
    }

    /**
     * Runs the command
     *
     * @param array $args
     * @return integer
     */
    public function run(array $args): int
    {
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
     *
     * @param string|array $message
     * @param int $newLines
     * @return static
     */
    public function out($message = '', int $newLines = 1): self
    {
        $this->io->out($message, $newLines, ConsoleIo::NORMAL);

        return $this;
    }

    /**
     * Outputs a message or array of messages to stderr
     *
     * @param string|array $message
     * @param int $newLines
     * @return static
     */
    public function error($message = '', int $newLines = 1): self
    {
        $this->io->err($message, $newLines);

        return $this;
    }

    /**
     * Outputs a message or array of messages to stdout when verbose option is provided
     *
     * @param string|array $message
     * @param int $newLines
     * @return static
     */
    public function verbose($message = '', int $newLines = 1): self
    {
        $this->io->out($message, $newLines, ConsoleIo::VERBOSE);

        return $this;
    }

    /**
     * Outputs a message or array of messages to stdout even if quiet option is provided
     *
     * @param string|array $message
     * @param int $newLines
     * @return static
     */
    public function quiet($message = '', int $newLines = 1): self
    {
        $this->io->out($message, $newLines, ConsoleIo::QUIET);

        return $this;
    }

    /**
     * Displays the help for this Command
     *
     * @return void
     */
    private function displayHelp(): void
    {
        $help = $this->createHelpFormatter();
        if (! empty($this->description)) {
            $help->setDescription($this->description);
        }

        $help->setUsage([$this->parser->generateUsage($this->name)]);
        $help->setOptions($this->parser->generateOptions());
        $help->setArguments($this->parser->generateArguments());

        $this->out($help->generate());
    }

    /**
     * Displays a formatted error message and stops the execution
     *
     * @param string $title
     * @param string|null $message
     * @param int $code
     * @return void
     */
    public function throwError(string $title, string $message = null, int $code = self::ERROR): void
    {
        $this->io->err("\n<alert> ERROR </alert> <lightYellow>{$title}</lightYellow>\n" . $message);

        throw new StopException($title, $code);
    }

    /**
     * Get the ConsoleIO object
     *
     * @return ConsoleIo
     */
    public function getConsoleIo(): ConsoleIo
    {
        return $this->io;
    }
}
