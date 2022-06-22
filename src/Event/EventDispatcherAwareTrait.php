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

use Psr\EventDispatcher\EventDispatcherInterface;

trait EventDispatcherAwareTrait
{
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * Gets the Event Dispatcher
     */
    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->eventDispatcher ?? null;
    }

    /**
     * Sets the Event Dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): static
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * Dispatches an Event if the Event Dispatcher is available
     */
    public function dispatchEvent(object $event): ?object
    {
        return isset($this->eventDispatcher) ? $this->eventDispatcher->dispatch($event) : null;
    }
}
