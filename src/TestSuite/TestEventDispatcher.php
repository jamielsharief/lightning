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
use Lightning\Event\GenericEventInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * TestEventDispatcher
 *
 * A test PSR-14 Event Dispatcher
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
     * @return void
     */
    public function dispatch(object $event)
    {
        $this->dispatchedEvents[] = $event;

        $callable = $this->listeners[get_class($event)] ?? null;

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
     * @return self
     */
    public function on(string $event, callable $callable): self
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
        return $event instanceof GenericEventInterface ? $event->getName() : get_class($event);
    }
}
