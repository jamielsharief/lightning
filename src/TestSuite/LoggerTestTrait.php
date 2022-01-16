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

namespace Lightning\TestSuite;

use Psr\Log\LogLevel;
use RuntimeException;

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
            throw new RuntimeException('TestLogger not set');
        }

        return $this->testLogger;
    }

    /**
     * Asserts that the log level has a message that contains this string
     *
     * @param string $string
     * @param string $level
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogContains(string $string, string $level, bool $interpolated = true): void
    {
        $this->assertTrue(
            $this->getLogger()->logContains($string, $level, $interpolated),
            sprintf('Log messages for level `%s` do not contain `%s`', $level, $string)
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
    public function assertLogNotContains(string $string, string $level, bool $interpolated = true): void
    {
        $this->assertFalse(
            $this->getLogger()->logContains($string, $level, $interpolated),
            sprintf('Log messages for level `%s` contain `%s`', $level, $string)
        );
    }

    /**
     * Asserts that the log level has a message
     *
     * @param string $message
     * @param string $level
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogHas(string $message, string $level, bool $interpolated = true): void
    {
        $this->assertTrue(
            $this->getLogger()->hasLogged($message, $level, $interpolated),
            sprintf('Log messages for level `%s` does not have `%s`', $level, $message)
        );
    }

    /**
    * Asserts that the log level has a message
    *
    * @param string $message
    * @param string $level
    * @param boolean $interpolated
    * @return void
    */
    public function assertLogDoesNotHave(string $message, string $level, bool $interpolated = true): void
    {
        $this->assertTrueFalse(
            $this->getLogger()->hasLogged($message, $level, $interpolated),
            sprintf('Log messages for level `%s` has `%s`', $level, $message)
        );
    }

    /**
     * Asserts the logged messages count
     *
     * @param integer $count
     * @return void
     */
    public function assertLogCount(int $count): void
    {
        $this->assertCount($count, $this->getLogger());
    }

    /**
     * Asserts that the log level has a message that contains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogEmergencyContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogContains($string, LogLevel::EMERGENCY, $interpolated);
    }

    /**
     * Asserts that the log level has a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogEmergencyHas(string $message, bool $interpolated = true): void
    {
        $this->assertLogHas($message, LogLevel::EMERGENCY, $interpolated);
    }

    /**
     * Asserts that the log level has a message that contains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogAlertContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogContains($string, LogLevel::ALERT, $interpolated);
    }

    /**
     * Asserts that the log level has a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogAlertHas(string $message, bool $interpolated = true): void
    {
        $this->assertLogHas($message, LogLevel::ALERT, $interpolated);
    }

    /**
     * Asserts that the log level has a message that contains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogCriticalContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogContains($string, LogLevel::CRITICAL, $interpolated);
    }

    /**
     * Asserts that the log level has a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogCriticalHas(string $message, bool $interpolated = true): void
    {
        $this->assertLogHas($message, LogLevel::CRITICAL, $interpolated);
    }

    /**
     * Asserts that the log level has a message that contains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogErrorContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogContains($string, LogLevel::ERROR, $interpolated);
    }

    /**
     * Asserts that the log level has a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogErrorHas(string $message, bool $interpolated = true): void
    {
        $this->assertLogHas($message, LogLevel::ERROR, $interpolated);
    }

    /**
     * Asserts that the log level has a message that contains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogWarningContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogContains($string, LogLevel::WARNING, $interpolated);
    }

    /**
     * Asserts that the log level has a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogWarningHas(string $message, bool $interpolated = true): void
    {
        $this->assertLogHas($message, LogLevel::WARNING, $interpolated);
    }

    /**
     * Asserts that the log level has a message that contains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogNoticeContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogContains($string, LogLevel::NOTICE, $interpolated);
    }

    /**
     * Asserts that the log level has a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogNoticeHas(string $message, bool $interpolated = true): void
    {
        $this->assertLogHas($message, LogLevel::NOTICE, $interpolated);
    }

    /**
     * Asserts that the log level has a message that contains this string
     *
     * @param string $string
     * @return void
     */
    public function assertLogInfoContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogContains($string, LogLevel::INFO, $interpolated);
    }

    /**
     * Asserts that the log level has a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogInfoHas(string $message, bool $interpolated = true): void
    {
        $this->assertLogHas($message, LogLevel::INFO, $interpolated);
    }

    /**
     * Asserts that the log level has a message that contains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogDebugContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogContains($string, LogLevel::DEBUG, $interpolated);
    }

    /**
     * Asserts that the log level has a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogDebugHas(string $message, bool $interpolated = true): void
    {
        $this->assertLogHas($message, LogLevel::DEBUG, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message that NotContains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogEmergencyNotContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogNotContains($string, LogLevel::EMERGENCY, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogEmergencyDoesNotHave(string $message, bool $interpolated = true): void
    {
        $this->assertLogDoesNotHave($message, LogLevel::EMERGENCY, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message that NotContains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogAlertNotContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogNotContains($string, LogLevel::ALERT, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogAlertDoesNotHave(string $message, bool $interpolated = true): void
    {
        $this->assertLogDoesNotHave($message, LogLevel::ALERT, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message that NotContains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogCriticalNotContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogNotContains($string, LogLevel::CRITICAL, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogCriticalDoesNotHave(string $message, bool $interpolated = true): void
    {
        $this->assertLogDoesNotHave($message, LogLevel::CRITICAL, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message that NotContains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogErrorNotContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogNotContains($string, LogLevel::ERROR, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogErrorDoesNotHave(string $message, bool $interpolated = true): void
    {
        $this->assertLogDoesNotHave($message, LogLevel::ERROR, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message that NotContains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogWarningNotContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogNotContains($string, LogLevel::WARNING, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogWarningDoesNotHave(string $message, bool $interpolated = true): void
    {
        $this->assertLogDoesNotHave($message, LogLevel::WARNING, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message that NotContains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogNoticeNotContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogNotContains($string, LogLevel::NOTICE, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogNoticeDoesNotHave(string $message, bool $interpolated = true): void
    {
        $this->assertLogDoesNotHave($message, LogLevel::NOTICE, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message that NotContains this string
     *
     * @param string $string
     * @return void
     */
    public function assertLogInfoNotContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogNotContains($string, LogLevel::INFO, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogInfoDoesNotHave(string $message, bool $interpolated = true): void
    {
        $this->assertLogDoesNotHave($message, LogLevel::INFO, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message that NotContains this string
     *
     * @param string $string
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogDebugNotContains(string $string, bool $interpolated = true): void
    {
        $this->assertLogNotContains($string, LogLevel::DEBUG, $interpolated);
    }

    /**
     * Asserts that the log level does not have a message
     *
     * @param string $message
     * @param boolean $interpolated
     * @return void
     */
    public function assertLogDebugDoesNotHave(string $message, bool $interpolated = true): void
    {
        $this->assertLogDoesNotHave($message, LogLevel::DEBUG, $interpolated);
    }
}
