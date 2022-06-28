<?php declare(strict_types=1);

namespace Lightning\Test\TestSuite;

use PHPUnit\Framework\TestCase;
use Lightning\Event\EventDispatcher;
use Lightning\Event\ListenerRegistry;
use Lightning\TestSuite\TestEventDispatcher;

class TestEvent
{
}

final class TestEventDispatcherTest extends TestCase
{
    public function testGetDispatchedEventsCount(): void
    {
        $eventDispatcher = new TestEventDispatcher(new EventDispatcher(new ListenerRegistry()));

        $this->assertCount(0, $eventDispatcher);
        $eventDispatcher->dispatch(new TestEvent());

        $this->assertCount(1, $eventDispatcher);

        $eventDispatcher->dispatch(new TestEvent());
        $eventDispatcher->dispatch(new TestEvent());
    }

    public function testGetDispatchedEvents(): void
    {
        $eventDispatcher = new TestEventDispatcher(new EventDispatcher(new ListenerRegistry()));
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
        $eventDispatcher = new TestEventDispatcher(new EventDispatcher(new ListenerRegistry()));
        $event = new TestEvent();

        $this->assertNull($eventDispatcher->getDispatchedEvent(TestEvent::class));

        $eventDispatcher->dispatch($event);

        $this->assertEquals(
            $event,
            $eventDispatcher->getDispatchedEvent(TestEvent::class)
        );
    }

    public function testHasDispatchedEvent(): void
    {
        $eventDispatcher = new TestEventDispatcher(new EventDispatcher(new ListenerRegistry()));
        $event = new TestEvent();

        $this->assertFalse($eventDispatcher->hasDispatchedEvent(TestEvent::class));

        $eventDispatcher->dispatch($event);

        $this->assertTrue($eventDispatcher->hasDispatchedEvent(TestEvent::class));
    }
}
