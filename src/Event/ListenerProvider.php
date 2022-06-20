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

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * PSR-14 Listener Provider
 */
class ListenerProvider implements ListenerProviderInterface
{
    protected array $listeners = [];

    /**
     * Attaches an event handler
     */
    public function addListener(string $eventType, callable $callable): static
    {
        $this->listeners[$eventType][] = $callable;

        return $this;
    }

    /**
     * Deteaches an even handler
     */
    public function removeListener(string $eventType, callable $callable): static
    {
        foreach ($this->listeners[$eventType] ?? [] as $index => $handler) {
            if ($handler == $callable) {
                unset($this->listeners[$eventType][$index]);
            }
        }

        return $this;
    }

    /**
     * Adds an event Subscriber
     */
    public function addSubscriber(SubscriberInterface $subscriber): static
    {
        foreach ($subscriber->subscribedEvents() as $eventType => $method) {
            $this->addListener($eventType, [$subscriber, $method]);
        }

        return $this;
    }

    /**
     * Removes an event Subscriber
     */
    public function removeSubscriber(SubscriberInterface $subscriber): static
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
        $eventType = $event instanceof Event ? $event->getType() : $event::class;

        return $this->listeners[$eventType] ?? [];
    }
}
