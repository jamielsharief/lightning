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
     * @param string $level e.g. LogLevel::ERROR
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
            throw new InvalidArgumentException("Unkown log level `{$level}`");
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
     */
    protected function interpolate($message, array $context = [])
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (! is_array($val) && (! is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }

    protected function checkLevel(string $level): void
    {
        if (! in_array($level, $this->logLevels)) {
            throw new InvalidArgumentException("Invalid log level `{$level}`");
        }
    }
}
