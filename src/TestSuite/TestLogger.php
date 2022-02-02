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

use Countable;
use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\InvalidArgumentException;

/**
 * Test Logger
 *
 * @internal The default for interpolated should be true since this is what is being rendered, wether in most cases there might be more
 * complicated and lengthy data, it is irrelevant, disabling for those cases should be done manually.
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
    protected array $messages = [];

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

        $this->messages[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'rendered' => $interpolated
        ];
    }

    /**
     * Checks if a particular message was logged
     *
     * @param string $message
     * @param string $level
     * @param boolean $interpolated
     * @return boolean
     */
    public function hasMessage(string $message, string $level, bool $interpolated = true): bool
    {
        $messages = $this->filter(function (array $logged) use ($message, $level, $interpolated) {
            $loggedMessage = $interpolated ? $logged['rendered'] : $logged['message'];

            return $loggedMessage === $message && $logged['level'] === $level;
        });

        return ! empty($messages);
    }

    /**
     * Check that a log message contains a string
     *
     * @param string $string
     * @param string $level
     * @param boolean $interpolated
     * @return boolean
     */
    public function hasMessageThatContains(string $string, string $level, bool $interpolated = true): bool
    {
        $messages = $this->filter(function (array $logged) use ($string, $level, $interpolated) {
            $haystack = $interpolated ? $logged['rendered'] : $logged['message'];

            return strpos($haystack, $string) !== false && $logged['level'] === $level;
        });

        return ! empty($messages);
    }

    /**
     * Check that a log message matches a pattern
     *
     * @param string $pattern
     * @param string $level
     * @param boolean $interpolated
     * @return boolean
     */
    public function hasMessageThatMatches(string $pattern, string $level, bool $interpolated = true): bool
    {
        $messages = $this->filter(function (array $logged) use ($pattern, $level, $interpolated) {
            $haystack = $interpolated ? $logged['rendered'] : $logged['message'];

            return (bool) preg_match($pattern, $haystack) && $logged['level'] === $level;
        });

        return ! empty($messages);
    }

    /**
     * Gets the logged messages
     *
     * @return array
     */
    public function getMessages(?string $level = null): array
    {
        if ($level) {
            return $this->filter(function (array $message) use ($level) {
                return $message['level'] === $level;
            });
        }

        return $this->messages;
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
        foreach ($this->messages as $message) {
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
        $this->messages = [];
    }

    /**
     * Counts the items that are logged
     *
     * @return integer
     */
    public function count(): int
    {
        return count($this->messages);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message
     * @param array $context
     * @return string
     */
    protected function interpolate(string $message, array $context = []): string
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
