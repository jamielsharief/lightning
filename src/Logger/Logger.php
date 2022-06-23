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

use Stringable;
use Psr\Log\LogLevel;
use DateTimeImmutable;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\InvalidArgumentException;

class Logger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var HandlerInterface[]
     */
    private array $handlers = [];

    private array $logLevels = [
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
     * Constructor
     */
    public function __construct(private string $name)
    {
    }

    /**
     * Adds a new handler to the logger
     */
    public function addHandler(HandlerInterface $handler): static
    {
        array_push($this->handlers, $handler);

        return $this;
    }

    /**
     * Adds a new handler to the logger
     */
    public function removeHandler(HandlerInterface $handler): static
    {
        foreach ($this->handlers as $index => $value) {
            if ($handler == $value) {
                unset($this->handlers[$index]);
            }
        }

        return $this;
    }

    /**
     * @return HandlerInterface[];
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, string|Stringable $message, array $context = [])
    {
        if (! in_array($level, $this->logLevels)) {
            throw new InvalidArgumentException(sprintf('Invalid log level `%s`', $level));
        }

        $datetime = new DateTimeImmutable();

        foreach ($this->handlers as $handler) {
            if ($handler->isHandling($level)) {
                $handler->handle(new LogMessage((string) $message, $context), $level, $this->name, $datetime);
            }
        }
    }
}
