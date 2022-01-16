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

use Countable;
use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\InvalidArgumentException;

/**
 * Test Logger
 *
 * @internal the PSR package does have its own test logger as well
 */
class TestLogger implements Countable, LoggerInterface
{
    use LoggerTrait;

    protected array $logLevels = [
        LogLevel::DEBUG,
        LogLevel::INFO,
        LogLevel::NOTICE,
        LogLevel::WARNING,
        LogLevel::ERROR,
        LogLevel::CRITICAL,
        LogLevel::ALERT,
        LogLevel::EMERGENCY
    ];

    /**
     * All records of logged
     *
     * @var array
     */
    protected array $logged = [];
    protected array $messages = [];
    protected array $interpolated = [];

    /**
    * Logs with an arbitrary level.
    *
    * @param mixed   $level
    * @param string  $message
    * @param mixed[] $context
    * @return void
    * @throws \Psr\Log\InvalidArgumentException
    */
    public function log($level, $message, array $context = [])
    {
        if (! in_array($level, $this->logLevels)) {
            throw new InvalidArgumentException(sprintf('Unkown log level `%s`', $level));
        }

        $interpolated = $this->interpolate($message, $context);

        $this->logged[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'rendered' => $interpolated
        ];

        $this->interpolated[$level][] = $interpolated;
        $this->messages[$level][] = $message;
    }

    /**
     * Checks if a particular message was logged
     *
     * @param string $message
     * @param string|null $level
     * @return boolean
     */
    public function hasLogged(string $message, string $level, bool $interpolated = true): bool
    {
        return $interpolated ?
            in_array($message, $this->interpolated[$level] ?? []) : in_array($message, $this->messages[$level] ?? []);
    }

    /**
     * Check that a log contains a string
     *
     * @param string $string
     * @param string $level
     * @param boolean $interpolated
     * @return boolean
     */
    public function logContains(string $string, string $level, bool $interpolated = true): bool
    {
        foreach ($this->logged as $logged) {
            $haystack = $interpolated ? $logged['rendered'] : $logged['message'];
            if (strpos($haystack, $string) !== false && $logged['level'] === $level) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the logged messages
     *
     * @return array
     */
    public function getLogged(?string $level = null): array
    {
        if ($level) {
            return $this->filter(function (array $message) use ($level) {
                return $message['level'] === $level;
            });
        }

        return $this->logged;
    }

    /**
     * Filters the log messages
     *
     * @param callable $callback
     * @return array
     */
    public function filter(callable $callback): array
    {
        $messages = [];
        foreach ($this->logged as $message) {
            if ($callback($message) === true) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * Resets the test logger, all messages are cleared
     *
     * @return void
     */
    public function reset(): void
    {
        $this->logged = [];
        $this->messages = [];
        $this->interpolated = [];
    }

    /**
     * Counts the items that are logged
     *
     * @return integer
     */
    public function count(): int
    {
        return count($this->logged);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function interpolate(string $message, array $context = [])
    {
        $replace = [];
        foreach ($context as $key => $value) {
            if (! is_array($value) && (! is_object($value) || method_exists($value, '__toString'))) {
                $replace['{' . $key . '}'] = (string) $value;
            }
        }

        return strtr($message, $replace);
    }
}
