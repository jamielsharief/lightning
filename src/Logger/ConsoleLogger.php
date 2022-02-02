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

namespace Lightning\Logger;

use Psr\Log\LogLevel;
use InvalidArgumentException;

class ConsoleLogger extends AbstractLogger
{
    protected $stream;

    protected array $colorMap = [
        LogLevel::DEBUG => '37',
        LogLevel::INFO => '32',
        LogLevel::NOTICE => '36',
        LogLevel::WARNING => '33',
        LogLevel::ERROR => '31',
        LogLevel::CRITICAL => '91',
        LogLevel::ALERT => '41;37',
        LogLevel::EMERGENCY => '4;41;37'
    ];

    /**
     * @param resource $stream e.g STDOUT
     */
    public function __construct($stream = STDOUT)
    {
        if (! is_resource($stream)) {
            throw new InvalidArgumentException('The stream is not a valid resource');
        }
        $this->stream = $stream;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $this->checkLevel($level);

        if ($this->shouldLog($level)) {
            $message = $this->format($level, $message, $context);
            fwrite($this->stream, $this->colorize($level, $message) . PHP_EOL);
        }
    }

    /**
     * Adds the console color
     *
     * @param string $level
     * @param string $message
     * @return string
     */
    protected function colorize(string $level, string $message): string
    {
        return "\033[0;{$this->colorMap[$level]}m{$message}\033[0m";
    }
}
