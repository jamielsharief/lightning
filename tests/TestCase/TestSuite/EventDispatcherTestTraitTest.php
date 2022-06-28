<?php declare(strict_types=1);

namespace Lightning\Test\TestSuite;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Lightning\Event\EventDispatcher;
use Lightning\Event\ListenerRegistry;
use Lightning\TestSuite\TestEventDispatcher;
use Lightning\TestSuite\EventDispatcherTestTrait;

class FooEvent
{
}

class BarEvent
{
}

final class EventDispatcherTestTraitTest extends TestCase
{
    use EventDispatcherTestTrait;

    public function createEventDispatcher(): TestEventDispatcher
    {
        return new TestEventDispatcher(new EventDispatcher(new ListenerRegistry()));
    }

    public function testSet(): void
    {
        $testEventDispatcher = $this->createEventDispatcher();

        $this->assertInstanceOf(TestCase::class, $this->setEventDispatcher($testEventDispatcher));
        $this->assertInstanceOf(TestEventDispatcher::class, $this->testEventDispatcher);
    }

    public function testGet(): void
    {
        $this->testEventDispatcher = $this->createEventDispatcher();
        $this->assertInstanceOf(TestEventDispatcher::class, $this->getEventDispatcher());
    }

    public function testGetException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('TestEventDispatcher is not set');

        $this->getEventDispatcher();
    }

    public function testAssertEventDispatched(): void
    {
        $eventDispatcher = $this->createEventDispatcher();
        $eventDispatcher->dispatch(new FooEvent());
        $this->setEventDispatcher($eventDispatcher);

        $this->assertEventDispatched(FooEvent::class);
    }

    public function testAssertEventNotDispatched(): void
    {
        $eventDispatcher = $this->createEventDispatcher();
        $this->setEventDispatcher($eventDispatcher);

        $this->assertEventNotDispatched(FooEvent::class);
    }

    public function testAssertEventsDispatched(): void
    {
        $eventDispatcher = $this->createEventDispatcher();
        $eventDispatcher->dispatch(new FooEvent());
        $eventDispatcher->dispatch(new BarEvent());

        $this->setEventDispatcher($eventDispatcher);

        $this->assertEventsDispatched([BarEvent::class,FooEvent::class]);
    }

    public function testAssertEventsNotDispatched(): void
    {
        $eventDispatcher = $this->createEventDispatcher();
        $this->setEventDispatcher($eventDispatcher);

        $this->assertEventsNotDispatched([BarEvent::class,FooEvent::class]);
    }

    public function testAssertEventsDispatchedEquals(): void
    {
        $eventDispatcher = $this->createEventDispatcher();
        $eventDispatcher->dispatch(new FooEvent());
        $eventDispatcher->dispatch(new BarEvent());

        $this->setEventDispatcher($eventDispatcher);

        $this->assertEventsDispatchedEquals([FooEvent::class,BarEvent::class]);
    }

    public function testAssertEventsDispatchedNotEquals(): void
    {
        $eventDispatcher = $this->createEventDispatcher();
        $eventDispatcher->dispatch(new FooEvent());
        $eventDispatcher->dispatch(new BarEvent());

        $this->setEventDispatcher($eventDispatcher);

        $this->assertEventsDispatchedNotEquals([BarEvent::class,FooEvent::class]);
    }

    public function testAssertEventdsDispatchedCount(): void
    {
        $eventDispatcher = $this->createEventDispatcher();
        $this->setEventDispatcher($eventDispatcher);

        $this->assertEventsDispatchedCount(0);

        $eventDispatcher->dispatch(new FooEvent());
        $this->assertEventsDispatchedCount(1);

        $eventDispatcher->dispatch(new FooEvent());
        $this->assertEventsDispatchedCount(2);
    }
}
