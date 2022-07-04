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

class MessageProducer
{
    /**
     * Constructor
     */
    public function __construct(protected MessageQueueInterface $messageQueue)
    {
    }

    /**
     * Gets the Message Queue for this Producer
     */
    public function getMessageQueue(): MessageQueueInterface
    {
        return $this->messageQueue;
    }

    /**
     * Sets the Message Queue for this Producer
     */
    public function setMessageQueue(MessageQueueInterface $messageQueue): static
    {
        $this->messageQueue = $messageQueue;

        return $this;
    }

    /**
     * Returns a new instance with a specific destination
     */
    public function send(string $destination, object $message, int $delay = 0): bool
    {
        return $this->messageQueue->send(
            $destination, serialize(new Message($message)), $delay
        );
    }
}
