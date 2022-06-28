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

use Psr\EventDispatcher\StoppableEventInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * PSR-14 Event Dispatcher
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * Constructor
     */
    public function __construct(protected ListenerRegistryInterface $listenerRegistry)
    {
    }

    /**
     * Get the Listener Registry
     */
    public function getListenerRegistry(): ListenerRegistryInterface
    {
        return $this->listenerRegistry;
    }

    /**
     * Sets the ListenerRegistry
     */
    public function setListenerRegistry(ListenerRegistryInterface $listenerRegistry): static
    {
        $this->listenerRegistry = $listenerRegistry;

        return $this;
    }

    /**
     * Dispatches an Event
     */
    public function dispatch(object $event): object
    {
        foreach ($this->listenerRegistry->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }

            $listener($event);
        }

        return $event;
    }
}
