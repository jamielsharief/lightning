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
use Lightning\Logger\LogMessage;
use Lightning\Logger\AbstractHandler;

class FileHandler extends AbstractHandler
{
    /**
     * Constructor
     * @internal Log level should be always last and with a default value
     */
    public function __construct(private string $path, string $level = LogLevel::DEBUG)
    {
        parent::__construct($level);
    }

    /**
     * Handle method
     */
    public function handle(string $level, LogMessage $message, DateTimeImmutable $dateTime, string $channel): bool
    {
        $line = sprintf(
            '[%s] %s %s: %s', $dateTime->format('Y-m-d G:i:s'), $channel, strtoupper($level), $message->toString()
        );

        return (bool) file_put_contents($this->path, $line ."\n", FILE_APPEND);
    }
}
