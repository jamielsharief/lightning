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

namespace Lightning\Worker;

use Throwable;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Lightning\MessageQueue\MessageConsumer;
use Lightning\MessageQueue\MessageProducer;

class MessageListener
{
    protected ?LoggerInterface $logger;

    /**
     * Constructor
     */
    public function __construct(protected MessageProducer $producer, protected MessageConsumer $consumer)
    {
    }

    /**
     * Sets the logger for the Worker
     */
    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Handles the message
     */
    public function handle(object $message): void
    {
        $this->log(LogLevel::DEBUG, sprintf('%s received', $message::class));

        try {
            $message->run();
            $this->log(LogLevel::INFO, sprintf('%s executed', $message::class));
        } catch (Throwable $exception) {
            $this->log(LogLevel::ERROR, sprintf('%s %s', $message::class, $exception->getMessage()));

            $this->onError($message);
        }
    }

    /**
     * Error handler
     */
    protected function onError(object $message): void
    {
        if ($message instanceof RetryableInterface) {
            $message->fail();

            if ($message->attempts() <= $message->maxRetries()) {
                if (! $this->producer->send($this->consumer->getSource(), $message, $message->delay())) {
                    $this->log(LogLevel::ERROR, sprintf('%s could not be sent to retry', $message::class));
                }
            }
        }
    }

    /**
     * Logs the message if available
     */
    public function log(string $level, string $message): void
    {
        if (isset($this->logger)) {
            $this->logger->log($level, $message);
        }
    }

    /**
     * Invokes this object
     */
    public function __invoke(object $message)
    {
        if ($message instanceof RunnableInterface) {
            $this->handle($message);
        } else {
            $this->log(LogLevel::WARNING, sprintf('%s is not runnable', $message::class));
        }
    }
}
