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

namespace Lightning\TestSuite;

use Countable;
use Lightning\Event\EventWithNameInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * TestEventDispatcher
 *
 * A test PSR-14 Event Dispatcher
 *
 * @todo: needs to be rethought as some sort of adapater, decorator, needs to register events as well.
 */
class TestEventDispatcher implements Countable, EventDispatcherInterface
{
    /**
     * @var array
     */
    protected array $dispatchedEvents = [];
    protected array $listeners = [];

    /**
     * Dispatches an Event
     *
     * @param object $event
     * @return object
     */
    public function dispatch(object $event)
    {
        $this->dispatchedEvents[] = $event;

        $callable = $this->listeners[$event::class] ?? null;

        if ($callable) {
            $callable($event);
        }

        return $event;
    }

    /**
     * Registers a callable that will be called on a particular event
     *
     * @internal this is for testing, only one per event
     *
     * @param string $event
     * @param callable $callable
     * @return static
     */
    public function on(string $event, callable $callable): static
    {
        $this->listeners[$event] = $callable;

        return $this;
    }

    /**
     * Gets the Events that were dispatched
     *
     * @return array
     */
    public function getDispatchedEvents(): array
    {
        $result = [];
        foreach ($this->dispatchedEvents as $event) {
            $result[] = $this->getEventName($event);
        }

        return $result;
    }

    /**
     * Gets an event that was dispached
     *
     * @param string $class
     * @return object|null
     */
    public function getDispatchedEvent(string $class): ?object
    {
        foreach ($this->dispatchedEvents as $event) {
            if ($this->getEventName($event) === $class) {
                return $event;
            }
        }

        return null;
    }

    /**
     * Checks if a Event was dispatched
     *
     * @param string $class
     * @return boolean
     */
    public function hasDispatchedEvent(string $class): bool
    {
        return $this->getDispatchedEvent($class) !== null;
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

    /**
     * Determine the Event name
     *
     * @param object $event
     * @return string
     */
    private function getEventName(object $event): string
    {
        return $event instanceof EventWithNameInterface ? $event->eventName() : get_class($event);
    }
}
