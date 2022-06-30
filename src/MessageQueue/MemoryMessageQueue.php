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

class MemoryMessageQueue implements MessageQueueInterface
{
    protected array $messages = [];

    /**
     * Sends a message to the message queue
     */
    public function send(string $queue, Message $message, int $delay = 0): bool
    {
        if (! isset($this->messages[$queue])) {
            $this->messages[$queue] = [];
        }

        $this->messages[$queue][] = $message;

        return true;
    }

    /**
     * Receives the next message from the queue, if any
     */
    public function receive(string $queue): ?Message
    {
        return isset($this->messages[$queue]) ? array_shift($this->messages[$queue]) : null;
    }
}
