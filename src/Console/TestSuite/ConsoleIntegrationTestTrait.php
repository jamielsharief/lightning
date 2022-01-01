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

namespace Lightning\Console\TestSuite;

use RuntimeException;
use Lightning\Console\AbstractCommand;
use Lightning\Console\ConsoleArgumentParser;

trait ConsoleIntegrationTestTrait
{
    private ?ConsoleIoStub $io = null;
    private ?int $commandExitCode = null;

    /**
     * Gets the Console IO Stub object
     *
     * @return ConsoleIoStub
     */
    protected function getIo(): ConsoleIoStub
    {
        if (! isset($this->io)) {
            throw new RuntimeException('Console IO stub not created');
        }

        return $this->io;
    }

    /**
     * Creats a Console IO stub
     *
     * @return ConsoleIoStub
     */
    protected function createConsoleIoStub(): ConsoleIoStub
    {
        return $this->io = new ConsoleIoStub();
    }

    /**
     * Creates a Command with the ArgumentParser and ConsoleIO stub
     *
     * @param string $class
     * @param object ...$additionalDependencies
     * @return AbstractCommand
     */
    protected function createCommand(string $class, object ...$additionalDependencies): AbstractCommand
    {
        return new $class(new ConsoleArgumentParser(), $this->createConsoleIoStub(), ...$additionalDependencies);
    }

    /**
     * Executes a command
     *
     * @param AbstractCommand $command
     * @param array $args
     * @param array $input
     * @return boolean
     */
    public function execute(AbstractCommand $command, array $args = [], array $input = []): bool
    {
        array_unshift($args, 'bin/console');
        $this->commandExitCode = $command->run($args);

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
        $this->assertStringContainsString($message, $this->getIo()->getStdout());
    }

    /**
    * Assert Output does not contains
    *
    * @param string $message
    * @return void
    */
    public function assertOutputNotContains(string $message): void
    {
        $this->assertStringNotContainsString($message, $this->getIo()->getStdout());
    }

    /**
     * Asserts that the output was empty
     *
     * @return void
     */
    public function assertOutputEmpty(): void
    {
        $this->assertEmpty($this->getIo()->getStdout());
    }

    /**
    * Asserts that the output was empty
    *
    * @return void
    */
    public function assertOutputNotEmpty(): void
    {
        $this->assertNotEmpty($this->getIo()->getStdout());
    }

    /**
     * Asserts that output matches a regualar expression
     *
     * @param string $pattern
     * @return void
     */
    public function assertOutputMatchesRegularExpression(string $pattern): void
    {
        $this->assertMatchesRegularExpression($pattern, $this->getIo()->getStdout());
    }

    /**
     * Asserts that the output does not match a regular expression
     *
     * @param string $pattern
     * @return void
     */
    public function assertOutputDoesNotMatchRegularExpression(string $pattern): void
    {
        $this->assertDoesNotMatchRegularExpression($pattern, $this->getIo()->getStdout());
    }

    /**
     * Assert Error contains
     *
     * @param string $message
     * @return void
     */
    public function assertErrorContains(string $message): void
    {
        $this->assertStringContainsString($message, $this->getIo()->getStderr());
    }

    /**
    * Assert error output does not contains
    *
    * @param string $message
    * @return void
    */
    public function assertErrorNotContains(string $message): void
    {
        $this->assertStringNotContainsString($message, $this->getIo()->getStderr());
    }

    /**
     * Asserts that the error output was empty
     *
     * @return void
     */
    public function assertErrorEmpty(): void
    {
        $this->assertEmpty($this->getIo()->getStderr());
    }

    /**
    * Asserts that the error output was empty
    *
    * @return void
    */
    public function assertErrorNotEmpty(): void
    {
        $this->assertNotEmpty($this->getIo()->getStderr());
    }

    /**
     * Asserts that error output matches a regualar expression
     *
     * @param string $pattern
     * @return void
     */
    public function assertErrorMatchesRegularExpression(string $pattern): void
    {
        $this->assertMatchesRegularExpression($pattern, $this->getIo()->getStderr());
    }

    /**
     * Asserts that the error output does not match a regular expression
     *
     * @param string $pattern
     * @return void
     */
    public function assertErrorDoesNotMatchRegularExpression(string $pattern): void
    {
        $this->assertDoesNotMatchRegularExpression($pattern, $this->getIo()->getStderr());
    }
}
