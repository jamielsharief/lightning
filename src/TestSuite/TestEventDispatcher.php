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

namespace Lightning\TestSuite;

use Countable;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * TestEventDispatcher
 *
 * A test PSR-14 Event Dispatcher
 */
class TestEventDispatcher implements Countable, EventDispatcherInterface
{
    protected array $dispatchedEvents = [];

    /**
     * Constructor
     *
     * @param object[] $dispatchedEvents
     */
    public function __construct(array $dispatchedEvents = [])
    {
        $this->dispatchedEvents = $dispatchedEvents;
    }

    /**
     * Dispatches an Event
     *
     * @param object $event
     * @return void
     */
    public function dispatch(object $event)
    {
        $this->dispatchedEvents[get_class($event)] = $event;

        return $event;
    }

    /**
     * Gets the Events that were dispatched
     *
     * @return array
     */
    public function getDispatchedEvents(): array
    {
        return array_keys($this->dispatchedEvents);
    }

    /**
     * Gets the Event objects that were dispatched
     *
     * @return array
     */
    public function getDispatchedEventObjects(): array
    {
        return $this->dispatchedEvents;
    }

    /**
     * Checks if a Event was dispatched
     *
     * @param string $class
     * @return boolean
     */
    public function hasDispatched(string $class): bool
    {
        return isset($this->dispatchedEvents[$class]);
    }

    /**
     * Undocumented function
     *
     * @return integer
     */
    public function count(): int
    {
        return count($this->dispatchedEvents);
    }

    /**
     * Resets the Event dispatcher
     *
     * @return void
     */
    public function reset(): void
    {
        $this->dispatchedEvents = [];
    }
}
