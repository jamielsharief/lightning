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

namespace Lightning\Console\TestSuite;

use RuntimeException;
use Lightning\Console\AbstractCommand;

trait ConsoleIntegrationTestTrait
{
    private ?TestConsoleIo $io = null;
    private ?AbstractCommand $command = null;
    private ?int $commandExitCode = null;

    /**
     * Sets up the integration testing
     *
     * @param AbstractCommand $command
     * @return void
     */
    public function setupIntegrationTesting(AbstractCommand $command): void
    {
        $this->command = $command;
        $this->setConsoleIo($command->getConsoleIo());
    }

    /**
     * Creates a Console IO stub
     *
     * @return TestConsoleIo
     */
    protected function createConsoleIo(): TestConsoleIo
    {
        return new TestConsoleIo();
    }

    /**
     * Gets the Console IO Stub object
     *
     * @return TestConsoleIo
     */
    protected function getConsoleIo(): TestConsoleIo
    {
        if (! isset($this->io)) {
            throw new RuntimeException('Console IO stub not set');
        }

        return $this->io;
    }

    /**
    * Gets the Console IO Stub object
    *
    * @return AbstractCommand
    */
    protected function getCommand(): AbstractCommand
    {
        if (! isset($this->command)) {
            throw new RuntimeException('Command not set');
        }

        return $this->command;
    }

    /**
     * Sets the Console IO object to be used by the assertation methods
     *
     * @param TestConsoleIo $io
     * @return static
     */
    public function setConsoleIo(TestConsoleIo $io): static
    {
        $this->io = $io;

        return $this;
    }

    /**
     * Executes a command
     *
     * @param array $args
     * @param array $input
     * @return boolean
     */
    public function execute(array $args = [], array $input = []): bool
    {
        array_unshift($args, 'bin/console');
        $this->commandExitCode = $this->getCommand()->run($args);

        return $this->commandExitCode === AbstractCommand::SUCCESS;
    }

    /**
     * Asserts the exit code was a success
     *
     * @return void
     */
    public function assertExitSuccess(): void
    {
        $this->assertEquals($this->commandExitCode, AbstractCommand::SUCCESS);
    }

    /**
     * Asserts the exit code was an error
     *
     * @return void
     */
    public function assertExitError(): void
    {
        $this->assertNotEquals($this->commandExitCode, AbstractCommand::SUCCESS);
    }

    /**
     * Asserts an exit code
     *
     * @return void
     */
    public function assertExitCode(int $code): void
    {
        $this->assertEquals($this->commandExitCode, $code);
    }

    /**
     * Assert Output contains
     *
     * @param string $message
     * @return void
     */
    public function assertOutputContains(string $message): void
    {
        $this->assertStringContainsString($message, $this->getConsoleIo()->getStdout());
    }

    /**
    * Assert Output does not contains
    *
    * @param string $message
    * @return void
    */
    public function assertOutputNotContains(string $message): void
    {
        $this->assertStringNotContainsString($message, $this->getConsoleIo()->getStdout());
    }

    /**
     * Asserts that the output was empty
     *
     * @return void
     */
    public function assertOutputEmpty(): void
    {
        $this->assertEmpty($this->getConsoleIo()->getStdout());
    }

    /**
    * Asserts that the output was empty
    *
    * @return void
    */
    public function assertOutputNotEmpty(): void
    {
        $this->assertNotEmpty($this->getConsoleIo()->getStdout());
    }

    /**
     * Asserts that output matches a regualar expression
     *
     * @param string $pattern
     * @return void
     */
    public function assertOutputMatchesRegularExpression(string $pattern): void
    {
        $this->assertMatchesRegularExpression($pattern, $this->getConsoleIo()->getStdout());
    }

    /**
     * Asserts that the output does not match a regular expression
     *
     * @param string $pattern
     * @return void
     */
    public function assertOutputDoesNotMatchRegularExpression(string $pattern): void
    {
        $this->assertDoesNotMatchRegularExpression($pattern, $this->getConsoleIo()->getStdout());
    }

    /**
     * Assert Error contains
     *
     * @param string $message
     * @return void
     */
    public function assertErrorContains(string $message): void
    {
        $this->assertStringContainsString($message, $this->getConsoleIo()->getStderr());
    }

    /**
    * Assert error output does not contains
    *
    * @param string $message
    * @return void
    */
    public function assertErrorNotContains(string $message): void
    {
        $this->assertStringNotContainsString($message, $this->getConsoleIo()->getStderr());
    }

    /**
     * Asserts that the error output was empty
     *
     * @return void
     */
    public function assertErrorEmpty(): void
    {
        $this->assertEmpty($this->getConsoleIo()->getStderr());
    }

    /**
    * Asserts that the error output was empty
    *
    * @return void
    */
    public function assertErrorNotEmpty(): void
    {
        $this->assertNotEmpty($this->getConsoleIo()->getStderr());
    }

    /**
     * Asserts that error output matches a regualar expression
     *
     * @param string $pattern
     * @return void
     */
    public function assertErrorMatchesRegularExpression(string $pattern): void
    {
        $this->assertMatchesRegularExpression($pattern, $this->getConsoleIo()->getStderr());
    }

    /**
     * Asserts that the error output does not match a regular expression
     *
     * @param string $pattern
     * @return void
     */
    public function assertErrorDoesNotMatchRegularExpression(string $pattern): void
    {
        $this->assertDoesNotMatchRegularExpression($pattern, $this->getConsoleIo()->getStderr());
    }
}
