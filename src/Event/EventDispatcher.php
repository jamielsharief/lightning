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
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * PSR-14 Event Dispatcher
 */
class EventDispatcher implements EventDispatcherInterface, ListenerProviderInterface
{
    public const DEFAULT_PRIORITY = 100;

    private array $listeners = [];
    private array $sorted = [];

    /**
     * Adds a Listener
     */
    public function addListener(string $eventType, callable $callable, int $priority = self::DEFAULT_PRIORITY): static
    {
        $this->listeners[$eventType][$priority][] = $callable;
        unset($this->sorted[$eventType]);

        return $this;
    }

    /**
     * Removes a listener
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
     * Adds a subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): static
    {
        foreach ($subscriber->getSubscribedEvents() as $eventType => $params) {
            $params = (array) $params;
            $this->addListener($eventType, [$subscriber, $params[0]], $params[1] ?? self::DEFAULT_PRIORITY);
        }

        return $this;
    }

    /**
     * Removes a subscriber
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber): static
    {
        foreach ($subscriber->getSubscribedEvents() as $eventType => $params) {
            $params = (array) $params;
            $this->removeListener($eventType, [$subscriber, $params[0]]);
        }

        return $this;
    }

    /**
     * Part of ListenerProviderInterface
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventType = $event instanceof Event ? $event->getName() : $event::class;

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
     * Dispatches an event
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
