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
 * PSR-14 Dispatcher
 */
class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(protected ListenerProviderInterface $provider)
    {
    }

    /**
     * Dispatches an Event
     */
    public function dispatch(object $event): object
    {
        foreach ($this->provider->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }

            $listener($event);
        }

        return $event;
    }

    /**
     * Get the Listener Provider
     */
    public function getListenerProvider(): ListenerProviderInterface
    {
        return $this->provider;
    }

    /**
     * Set the Listener Provider
     */
    public function setListenerProvider(ListenerProviderInterface $provider): static
    {
        $this->provider = $provider;

        return $this;
    }
}
