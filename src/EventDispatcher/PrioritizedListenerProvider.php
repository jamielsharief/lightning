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

namespace Lightning\EventDispatcher;

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * PSR-14 Listener Provider
 */
class PrioritizedListenerProvider implements ListenerProviderInterface
{
    protected array $listeners = [];
    private array $sorted = [];

    /**
     * Attaches an event handler
     */
    public function addListener(string $eventType, callable $callable, int $priority = 100): static
    {
        $this->listeners[$eventType][$priority][] = $callable;
        unset($this->sorted[$eventType]);

        return $this;
    }

    /**
     * Deteaches an even handler
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
     * Gets the Listeners for an Event
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventType = $event::class;
        if (empty($this->listeners[$eventType])) {
            return [];
        }

        if (! isset($this->sorted[$eventType])) {
            ksort($this->listeners[$eventType]);
            $this->sorted[$eventType] = true;
        }

        return array_merge(...$this->listeners[$eventType]);
    }
}
