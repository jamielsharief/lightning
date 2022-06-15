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

use Psr\EventDispatcher\StoppableEventInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * EventDispatcher
 */
class EventDispatcher implements EventManagerInterface, ListenerProviderInterface
{
    private array $listeners = [];
    private array $sorted = [];

    /**
     * Adds an event listener
     */
    public function addListener(string $eventType, callable $callable, int $priority = 10): static
    {
        $this->listeners[$eventType][$priority][] = $callable;
        unset($this->sorted[$eventType]);

        return $this;
    }

    /**
     * Adds an event Subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber, int $defaultPriority = 10): static
    {
        foreach ($subscriber->subscribedEvents() as $eventType => $params) {
            $params = (array) $params;
            $this->addListener($eventType, [$subscriber, $params[0]], $params[1] ?? $defaultPriority);
        }

        return $this;
    }

    /**
     * Removes an event listener
     */
    public function removeListener(string $eventType, callable $callable): static
    {
        foreach ($this->listeners[$eventType] ?? [] as $priority => $queue) {
            foreach ($queue as $index => $handler) {
                if ($handler == $callable) {
                    unset($this->listeners[$eventType][$priority][$index]);
                }
            }
        }

        return $this;
    }

    /**
     * Removes an event Subscriber
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber): static
    {
        foreach ($subscriber->subscribedEvents() as $eventType => $params) {
            $params = (array) $params;
            $this->removeListener($eventType, [$subscriber, $params[0]]);
        }

        return $this;
    }

    /**
     * Gets the Listeners for an Event
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventType = $event instanceof EventWithNameInterface ? $event->eventName() : $event::class;
        if (empty($this->listeners[$eventType])) {
            return [];
        }

        if (! isset($this->sorted[$eventType])) {
            ksort($this->listeners[$eventType]);
            $this->sorted[$eventType] = true;
        }

        return array_merge(...$this->listeners[$eventType]);
    }

    /**
     * Dispatches an Event
     */
    public function dispatch(object $event): object
    {
        foreach ($this->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }

            $listener($event);
        }

        return $event;
    }
}
