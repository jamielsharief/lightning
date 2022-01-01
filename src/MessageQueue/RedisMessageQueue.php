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

namespace Lightning\MessageQueue;

use Redis;

/**
 * Redis Queue
 *
 * @internal I have tested concurency using a bash script and seems good.
 */
class RedisMessageQueue extends AbstractMessageQueue implements MessageQueueInterface
{
    private Redis $redis;

    /**
     * Constructor
     *
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Sends a message to the message queue
     *
     * @param string $queue
     * @param object $message
     * @param integer $delay
     * @return boolean
     */
    public function send(string $queue, object $message, int $delay = 0): bool
    {
        $payload = $this->serialize($message);

        if ($delay === 0) {
            return $this->redis->rpush('queued:' . $queue, $payload) !== false;
        }

        return $this->redis->zadd('scheduled:' . $queue, time() + $delay, $payload) !== false;
    }

    /**
     * Receives the next message from the queue, if any
     *
     * @param string $queue
     * @return object|null
     */
    public function receive(string $queue): ?object
    {
        $this->migrateScheduledMessages($queue);

        $message = $this->redis->lpop('queued:' . $queue);

        return $message ? $this->unserialize($message) : null;
    }

    /**
     * Look for scheduled messages that are due and send those
     *
     * @param string $queue
     * @return void
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
