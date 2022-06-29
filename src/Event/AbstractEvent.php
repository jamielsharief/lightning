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

namespace Lightning\Event;

/**
 * AbstractEvent
 *
 * @internal this object reduces the repeated code, namely source but also added timestamp similar to Messages
 */
abstract class AbstractEvent
{
    private int $timestamp;

    /**
     * Constructor
     */
    public function __construct(private object $source)
    {
        $this->timestamp = time();
    }

    /**
     * Gets the object which the Event was triggered on
     */
    public function getSource(): object
    {
        return $this->source;
    }

    /**
     * Gets the timestamp this Event was created
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Gets a string representation of the Event
     */
    public function toString(): string
    {
        return serialize($this);
    }
}
