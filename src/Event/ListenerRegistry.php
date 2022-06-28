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
 * ListenerRegistry
 */
class ListenerRegistry implements ListenerRegistryInterface
{
    protected array $listeners = [];

    /**
     * Registers a Listener for an event type
     */
    public function registerListener(string $eventType, callable $callable): static
    {
        $this->listeners[$eventType][] = $callable;

        return $this;
    }

    /**
     * Deteaches an even handler
     */
    public function unregisterListener(string $eventType, callable $callable): static
    {
        foreach ($this->listeners[$eventType] ?? [] as $index => $handler) {
            if ($handler == $callable) {
                unset($this->listeners[$eventType][$index]);
            }
        }

        return $this;
    }

    /**
     * Gets the Listeners for an Event
     */
    public function getListenersForEvent(object $event): iterable
    {
        return $this->listeners[$event::class] ?? [];
    }
}
