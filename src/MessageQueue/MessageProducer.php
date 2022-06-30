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
    public function __construct(protected MessageQueueInterface $messageQueue, protected string $destination)
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
     * Sends a message to the message queue
     */
    public function send(Message $message, int $delay = 0): bool
    {
        return $this->sendTo($this->destination, $message, $delay);
    }

    /**
     * Returns a new instance with a specific destination
     */
    public function sendTo(string $destination, Message $message, int $delay = 0): bool
    {
        $this->beforeSend($message);

        $result = $this->messageQueue->send($destination, $message, $delay);
        if ($result) {
            $this->afterSend($message);
        }

        return $result ;
    }

    /**
     * BeforeSend Callback
     */
    protected function beforeSend(Message $message): void
    {
    }

    /**
     * AfterSend Callback
     */
    protected function afterSend(Message $message): void
    {
    }
}
