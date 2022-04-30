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

namespace Lightning\TestSuite;

use BadMethodCallException;

/**
 * # PSR-14 Event Test Trait
 */
trait EventDispatcherTestTrait
{
    protected ?TestEventDispatcher $testEventDispatcher = null;

    /**
     * Factory method to create the test version of a Event Dispatcher
     *
     * @return TestEventDispatcher
     */
    public function createEventDispatcher(): TestEventDispatcher
    {
        return new TestEventDispatcher();
    }

    /**
     * Sets the Event Dispatcher for testing
     *
     * @param TestEventDispatcher $testEventDispatcher
     * @return static
     */
    public function setEventDispatcher(TestEventDispatcher $testEventDispatcher): static
    {
        $this->testEventDispatcher = $testEventDispatcher;

        return $this;
    }

    /**
     * Gets the TestEventDispatcher
     *
     * @return TestEventDispatcher
     */
    public function getEventDispatcher(): TestEventDispatcher
    {
        if (! isset($this->testEventDispatcher)) {
            throw new BadMethodCallException('TestEventDispatcher is not set');
        }

        return $this->testEventDispatcher;
    }

    /**
     * Asserts that a particular event was dispached
     *
     * @param string $event
     * @return void
     */
    public function assertEventDispatched(string $event): void
    {
        $this->assertTrue($this->getEventDispatcher()->hasDispatchedEvent($event), sprintf('Event `%s` was not dispatched', $event));
    }

    /**
     * Asserts that an Event was dispatched
     *
     * @param string $event
     * @return void
     */
    public function assertEventNotDispatched(string $event): void
    {
        $this->assertFalse($this->getEventDispatcher()->hasDispatchedEvent($event), sprintf('Event `%s` was dispatched', $event));
    }

    /**
     * Asserts that a group of events were dispatched
     *
     * @param array $events
     * @return void
     */
    public function assertEventsDispatched(array $events): void
    {
        foreach ($events as $event) {
            $this->assertEventDispatched($event);
        }
    }

    /**
     * Asserts that a group of events were not dispatched
     *
     * @param array $events
     * @return void
     */
    public function assertEventsNotDispatched(array $events): void
    {
        foreach ($events as $event) {
            $this->assertEventNotDispatched($event);
        }
    }

    /**
     * Asserts that only these events were called in a particular order
     *
     * @param array $events
     * @return void
     */
    public function assertEventsDispatchedEquals(array $events): void
    {
        $this->assertEquals($events, $this->getEventDispatcher()->getDispatchedEvents());
    }

    /**
     * Asserts that the dispatched events do not match this list of events
     *
     * @param array $events
     * @return void
     */
    public function assertEventsDispatchedNotEquals(array $events): void
    {
        $this->assertNotEquals($events, $this->getEventDispatcher()->getDispatchedEvents());
    }

    /**
     * Asserts how many Events were caught
     *
     * @param integer $count
     * @return void
     */
    public function assertEventsDispatchedCount(int $count): void
    {
        $this->assertCount($count, $this->getEventDispatcher());
    }
}
