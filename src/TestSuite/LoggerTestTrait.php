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

namespace Lightning\TestSuite;

use Exception;

// TODO: add matches

trait LoggerTestTrait
{
    protected ?TestLogger $testLogger = null;

    /**
     * Factory method to create the test version of the Test Logger
     *
     * @return TestLogger
     */
    public function createLogger(): TestLogger
    {
        return new TestLogger();
    }

    /**
     * Sets the Logger
     *
     * @param TestLogger $testLogger
     * @return self
     */
    public function setLogger(TestLogger $testLogger): self
    {
        $this->testLogger = $testLogger;

        return $this;
    }

    /**
     * Gets the Logger
     *
     * @return TestLogger
     */
    public function getLogger(): TestLogger
    {
        if (! isset($this->testLogger)) {
            throw new Exception('Test Logger is not set');
        }

        return $this->testLogger;
    }

    /**
     * Asserts that the log level has a message
     *
     * @param string $string
     * @param string $level
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogHasMessage(string $string, string $level, bool $interpolated = true): void
    {
        $this->assertTrue(
            $this->getLogger()->hasMessage($string, $level, $interpolated),
            sprintf('Log messages for level `%s` does not have `%s`', $level, $string)
        );
    }

    /**
     * Asserts that the log level has a message that contains this string
     *
     * @param string $string
     * @param string $level
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogHasMessageThatContains(string $string, string $level, bool $interpolated = true): void
    {
        $this->assertTrue(
            $this->getLogger()->hasMessageThatContains($string, $level, $interpolated),
            sprintf('Log messages for level `%s` do not contain `%s`', $level, $string)
        );
    }

    /**
     * Asserts that the log level has a message that matches this pattern
     *
     * @param string $pattern
     * @param string $level
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogHasMessageThatMatches(string $pattern, string $level, bool $interpolated = true): void
    {
        $this->assertTrue(
            $this->getLogger()->hasMessageThatMatches($pattern, $level, $interpolated),
            sprintf('Log messages for level `%s` do not match `%s`', $level, $pattern)
        );
    }

    /**
     * Asserts that the log level has a message
     *
     * @param string $string
     * @param string $level
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogDoesNotHaveMessage(string $string, string $level, bool $interpolated = true): void
    {
        $this->assertFalse(
            $this->getLogger()->hasMessage($string, $level, $interpolated),
            sprintf('Log messages for level `%s` has `%s`', $level, $string)
        );
    }

    /**
     * Asserts that the log level has a message that contains this string
     *
     * @param string $string
     * @param string $level
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogDoesNotHaveMessageThatContains(string $string, string $level, bool $interpolated = true): void
    {
        $this->assertFalse(
            $this->getLogger()->hasMessageThatContains($string, $level, $interpolated),
            sprintf('Log messages for level `%s` contain `%s`', $level, $string)
        );
    }

    /**
     * Asserts that the log level has a message that matches this pattern
     *
     * @param string $pattern
     * @param string $level
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogDoesNotHaveMessageThatMatches(string $pattern, string $level, bool $interpolated = true): void
    {
        $this->assertFalse(
            $this->getLogger()->hasMessageThatMatches($pattern, $level, $interpolated),
            sprintf('Log messages for level `%s` match `%s`', $level, $pattern)
        );
    }

    /**
     * Asserts the logged messages count
     *
     * @param integer $count
     * @return void
     */
    public function assertLogMessagesCount(int $count): void
    {
        $this->assertCount($count, $this->getLogger());
    }
}
