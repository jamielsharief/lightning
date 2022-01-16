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

namespace Lightning\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventDispatcher implements EventDispatcherInterface, ListenerProviderInterface
{
    public const DEFAULT_PRIORITY = 10;

    private array $listeners = [];
    private array $sorted = [];

    /**
     * Adds a Listener
     *
     * @param string $eventType
     * @param callable $callable
     * @param integer $priority
     * @return self
     */
    public function addListener(string $eventType, callable $callable, int $priority = self::DEFAULT_PRIORITY): self
    {
        $this->listeners[$eventType][$priority][] = $callable;
        unset($this->sorted[$eventType]);

        return $this;
    }

    /**
     * Adds a subscriber
     *
     * @param EventSubscriberInterface $subscriber
     * @return self
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): self
    {
        foreach ($subscriber->getSubscribedEvents() as $eventType => $params) {
            foreach ($this->normalizeEvents($params) as $method) {
                $this->addListener($eventType, [$subscriber,$method[0]], $method[1]);
            }
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string|array $method
     * @return array
     */
    private function normalizeEvents($method): array
    {
        $methods = [];

        if (is_string($method)) {
            $methods[] = [$method,self::DEFAULT_PRIORITY];
        } elseif (is_array($method)) {
            if (is_int($method[1] ?? null)) {
                $methods[] = [$method[0],$method[1] ?? self::DEFAULT_PRIORITY];
            } else {
                foreach ($method as $m) {
                    $methods[] = [$m[0], $m[1] ?? self::DEFAULT_PRIORITY];
                }
            }
        }

        return $methods;
    }

    /**
     * @param object $event
     * @return iterable
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventType = $event instanceof Event ? $event->getName() : get_class($event);

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
     * Removes a listener
     *
     * @param string $eventType
     * @param callable $callable e.g. [$this,'method']
     * @return self
     */
    public function removeListener(string $eventType, callable $callable): self
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
     * Removes a subscriber
     *
     * @param EventSubscriberInterface $subscriber
     * @return self
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber): self
    {
        foreach ($subscriber->getSubscribedEvents() as $eventType => $params) {
            foreach ($this->normalizeEvents($params) as $method) {
                $this->removeListener($eventType, [$subscriber,$method[0]]);
            }
        }

        return $this;
    }

    /**
     * Dispatches an event
     *
     * @param object $event
     * @return object
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
