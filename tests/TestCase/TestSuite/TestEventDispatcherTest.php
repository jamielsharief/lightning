<?php declare(strict_types=1);

namespace Lightning\Test\TestSuite;

use PHPUnit\Framework\TestCase;
use Lightning\Event\EventWithNameInterface;
use Lightning\TestSuite\TestEventDispatcher;

class GenericEvent implements EventWithNameInterface
{
    public $name = 'Generic.Event';

    public function eventName(): string
    {
        return $this->name;
    }
}

class TestEvent
{
}

final class TestEventDispatcherTest extends TestCase
{
    public function testGetDispatchedEventsCount(): void
    {
        $eventDispatcher = new TestEventDispatcher();

        $this->assertCount(0, $eventDispatcher);
        $eventDispatcher->dispatch(new TestEvent());

        $this->assertCount(1, $eventDispatcher);

        $eventDispatcher->dispatch(new TestEvent());
        $eventDispatcher->dispatch(new TestEvent());
    }

    public function testGetDispatchedEvents(): void
    {
        $eventDispatcher = new TestEventDispatcher();
        $event = new TestEvent();

        $this->assertEquals([], $eventDispatcher->getDispatchedEvents());

        $eventDispatcher->dispatch($event);

        $this->assertEquals(
            [TestEvent::class],
            $eventDispatcher->getDispatchedEvents()
        );
    }

    public function testGetDispatchedEvent(): void
    {
        $eventDispatcher = new TestEventDispatcher();
        $event = new TestEvent();

        $this->assertNull($eventDispatcher->getDispatchedEvent(TestEvent::class));

        $eventDispatcher->dispatch($event);

        $this->assertEquals(
            $event,
            $eventDispatcher->getDispatchedEvent(TestEvent::class)
        );
    }

    public function testGetDispatchedEventGeneric(): void
    {
        $eventDispatcher = new TestEventDispatcher();
        $event = new GenericEvent();

        $this->assertNull($eventDispatcher->getDispatchedEvent('Generic.Event'));

        $eventDispatcher->dispatch($event);

        $this->assertEquals(
            $event,
            $eventDispatcher->getDispatchedEvent('Generic.Event')
        );
    }

    public function testHasDispatchedEvent(): void
    {
        $eventDispatcher = new TestEventDispatcher();
        $event = new TestEvent();

        $this->assertFalse($eventDispatcher->hasDispatchedEvent(TestEvent::class));

        $eventDispatcher->dispatch($event);

        $this->assertTrue($eventDispatcher->hasDispatchedEvent(TestEvent::class));
    }

    public function testReset(): void
    {
        $eventDispatcher = new TestEventDispatcher();
        $eventDispatcher->dispatch(new TestEvent());
        $this->assertCount(1, $eventDispatcher);

        $eventDispatcher->reset();
        $this->assertCount(0, $eventDispatcher);
    }
}
