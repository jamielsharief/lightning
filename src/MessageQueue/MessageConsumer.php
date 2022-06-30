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

namespace Lightning\MessageQueue;

class MessageConsumer
{
    /**
     * Constructor
     */
    public function __construct(protected MessageQueueInterface $messageQueue, protected string $source)
    {
    }

    /**
     * Gets the Message Queue for this Consumer
     */
    public function getMessageQueue(): MessageQueueInterface
    {
        return $this->messageQueue;
    }

    /**
     * Sets the Message Queue for this Consumer
     */
    public function setMessageQueue(MessageQueueInterface $messageQueue): static
    {
        $this->messageQueue = $messageQueue;

        return $this;
    }

    /**
     * Receives the next mesage from the Message queue if one is available
     */
    public function receive(): ?Message
    {
        return $this->receiveFrom($this->source);
    }

    /**
     * Receives the next mesage from the Message queue if one is available
     */
    public function receiveFrom(string $queue): ?Message
    {
        $message = $this->messageQueue->receive($queue);
        if ($message) {
            $this->afterReceive($message, $queue);
        }

        return $message;
    }

    /**
     * AfterReceive Callback
     */
    protected function afterReceive(Message $message, string $queue): void
    {
    }
}
