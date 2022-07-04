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

use Lightning\MessageQueue\MessageQueueInterface;

use Redis;

/**
 * Redis Queue
 *
 * @internal I have tested concurency using a bash script and seems good.
 */
class RedisMessageQueue implements MessageQueueInterface
{
    private Redis $redis;

    /**
     * Constructor
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Sends a message to the message queue
     * @internal message must be unique
     */
    public function send(string $queue, string $message, int $delay = 0): bool
    {

        if ($delay === 0) {
            return $this->redis->rpush('queued:' . $queue, $message) !== false;
        }

        return $this->redis->zadd('scheduled:' . $queue, time() + $delay, $message) !== false;
    }

    /**
     * Receives the next message from the queue, if any
     */
    public function receive(string $queue): ?string
    {
        $this->migrateScheduledMessages($queue);

        $message = $this->redis->lpop('queued:' . $queue);

        return $message ?: null;
    }

    /**
     * Look for scheduled messages that are due and send those
     */
    private function migrateScheduledMessages(string $queue): void
    {
        $results = $this->redis->zrangebyscore('scheduled:' . $queue, '-inf', (string) time());
        if ($results) {
            foreach ($results as $serialized) {
                $this->redis->rpush('queued:'. $queue, $serialized);
                $this->redis->zrem('scheduled:' . $queue, $serialized);
            }
        }
    }
}
