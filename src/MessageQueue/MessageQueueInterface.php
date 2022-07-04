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

interface MessageQueueInterface
{
    /**
     * Sends a message to the message queue
     */
    public function send(string $queue, string $message, int $delay = 0): bool;

    /**
     * Receives the next message from the queue, if any
     */
    public function receive(string $queue): ?string;
}
