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

namespace Lightning\TestSuite;

use Lightning\TestSuite\Stubs\EventDispatcherStub;

/**
 * # PSR-14 Event Test Trait
 */
trait EventTestTrait
{
    protected EventDispatcherStub $eventDispatcherStub;

    /**
     * Sets the Event Dispatcher for testings
     *
     * @param EventDispatcherStub $eventDispatcherStub
     * @return self
     */
    public function setEventDispatcher(EventDispatcherStub $eventDispatcherStub): self
    {
        $this->eventDispatcherStub = $eventDispatcherStub;

        return $this;
    }

    /**
     * Gets the EventDispatcherStub
     *
     * @return EventDispatcherStub
     */
    public function getEventDispatcher(): EventDispatcherStub
    {
        return $this->eventDispatcherStub;
    }

    /**
     * Asserts that a particular event was called
     *
     * @param string $event
     * @return void
     */
    protected function assertEventCalled(string $event): void
    {
        $this->assertContains($event, $this->eventDispatcherStub->getDispatchedEvents(), sprintf('Event `%s` was not called ', $event));
    }

    /**
     * Asserts that all events were called in the order they provided
     *
     * @return void
     */
    protected function assertEventsCalled(array $events): void
    {
        $calledEvents = $this->eventDispatcherStub->getDispatchedEvents();
        foreach ($events as  $index => $event) {
            $called = $calledEvents[$index] ?? null;

            if (is_null($called)) {
                $this->fail(sprintf('Event `%s` was not called ', $event));
            }

            $this->assertEquals($event, $called, sprintf('Expecting `%s` but `%s` was called before', $event, $called));
        }

        $expectedCount = count($events);
        $actualCount = count($calledEvents);

        $this->assertEquals($expectedCount, $actualCount, sprintf('Expected `%d` events but `%d` called', $expectedCount, $actualCount));

        $this->eventDispatcherStub->reset();
    }
}
