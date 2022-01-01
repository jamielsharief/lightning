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

namespace Lightning\TestSuite\Stubs;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * A Stub for PSR-14 Event Dispatcher
 */
class EventDispatcherStub implements EventDispatcherInterface
{
    protected array $dispatchedEvents = [];

    public function dispatch(object $event)
    {
        $this->dispatchedEvents[get_class($event)] = $event;

        return $event;
    }

    /**
     * Gets the Events that were dispatched
     *
     * @return array
     */
    public function getDispatchedEvents(): array
    {
        return array_keys($this->dispatchedEvents);
    }

    public function getDispatchedEventObjects(): array
    {
        return $this->dispatchedEvents;
    }

    public function reset(): void
    {
        $this->dispatchedEvents = [];
    }
}
