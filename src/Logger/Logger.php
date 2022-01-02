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

use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;

/**
 * A multi logger I guess
 */
class Logger implements LoggerInterface
{
    use LoggerTrait;

    protected array $loggers = [];

    /**
     * Constructor
     *
     * @param array $loggers
     */
    public function __construct(array $loggers = [])
    {
        $this->loggers = $loggers;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = [])
    {
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }

    /**
     * Pushes a logger onto the logging stack
     *
     * @param AbstractLogger $logger
     * @return self
     */
    public function pushLogger(AbstractLogger $logger): self
    {
        $this->loggers[] = $logger;

        return $this;
    }
}
