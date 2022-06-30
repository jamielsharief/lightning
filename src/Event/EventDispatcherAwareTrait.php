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
 * EventDispatcherAwareTrait
 *
 * This covers our implemenation
 */
trait EventDispatcherAwareTrait
{
    protected EventDispatcher $eventDispatcher;

    /**
     * Gets the Event Dispatcher
     */
    public function getEventDispatcher(): ?EventDispatcher
    {
        return $this->eventDispatcher ?? null;
    }

    /**
     * Sets the Event Dispatcher
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher): static
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }
}
