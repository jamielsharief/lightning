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

namespace Lightning\Logger;

use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;

abstract class AbstractLogger implements LoggerInterface
{
    use LoggerTrait;

    private int $minLevel = 0;

    private ?string $channel = null;

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
     * Returns a new version of the logger with a different channel
     *
     * @param string $channel e.g. LogLevel::ERROR
     * @return static
     */
    public function withChannel(string $channel): self
    {
        $clone = clone $this;
        $clone->channel = $channel;

        return $clone;
    }

    /**
     * Returns a new version of the logger with a different minimum log level
     *
     * @param string $level e.g. LogLevel::ERROR
     * @return static
     */
    public function withLogLevel(string $level): self
    {
        if (! in_array($level, $this->logLevels)) {
            throw new InvalidArgumentException(sprintf('Invalid log level `%s`', $level));
        }

        $clone = clone $this;
        $clone ->minLevel = array_search($level, $this->logLevels);

        return $clone;
    }

    /**
     * Checks if the minimum level is reached
     *
     * @param string $level
     * @return boolean
     */
    protected function shouldLog(string $level): bool
    {
        return  array_search($level, $this->logLevels) >= $this->minLevel;
    }

    /**
     * Formats the log message
     *
     * @param string $level e.g. LogLevel::DEBUG
     * @param string $message
     * @param array $context
     * @return string
     */
    protected function format($level, string $message, array $context): string
    {
        return sprintf('[%s] %s%s: %s', date('Y-m-d G:i:s'), $this->channel ? $this->channel . ' ' : null, strtoupper($level), $this->interpolate($message, $context));
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

    protected function checkLevel(string $level): void
    {
        if (! in_array($level, $this->logLevels)) {
            throw new InvalidArgumentException(sprintf('Invalid log level `%s`', $level));
        }
    }
}
