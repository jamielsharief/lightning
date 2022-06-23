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

namespace Lightning\Logger\Handler;

use Psr\Log\LogLevel;
use DateTimeImmutable;
use InvalidArgumentException;
use Lightning\Logger\LogMessage;
use Lightning\Logger\AbstractHandler;

class ConsoleHandler extends AbstractHandler
{
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
     * Constructor
     * @internal Log level should be always last and with a default value
     */
    public function __construct(protected $stream = STDOUT, string $level = LogLevel::DEBUG)
    {
        parent::__construct($level);

        if (! is_resource($stream)) {
            throw new InvalidArgumentException('The stream is not a valid resource');
        }
    }

    /**
     * Handle method
     */
    public function handle(LogMessage $message, string $level, string $channel, DateTimeImmutable $dateTime): bool
    {
        $line = sprintf(
            '[%s] %s %s: %s', $dateTime->format('Y-m-d G:i:s'), $channel, strtoupper($level), $message->toString()
        );

        return fwrite($this->stream, "\033[0;{$this->colorMap[$level]}m{$line}\033[0m" . PHP_EOL) !== false;
    }
}
