<?php declare(strict_types=1);

namespace Lightning\Test\TestSuite;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
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

    public function testCreateEventDispatcher(): void
    {
        $this->assertInstanceOf(TestEventDispatcher::class, $this->createEventDispatcher());
    }

    public function testSet(): void
    {
        $this->assertInstanceOf(TestCase::class, $this->setEventDispatcher(new TestEventDispatcher()));
        $this->assertInstanceOf(TestEventDispatcher::class, $this->testEventDispatcher);
    }

    public function testGet(): void
    {
        $this->testEventDispatcher = new TestEventDispatcher();
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
        $eventDispatcher = new TestEventDispatcher();
        $eventDispatcher->dispatch(new FooEvent());
        $this->setEventDispatcher($eventDispatcher);

        $this->assertEventDispatched(FooEvent::class);
    }

    public function testAssertEventNotDispatched(): void
    {
        $eventDispatcher = new TestEventDispatcher();
        $this->setEventDispatcher($eventDispatcher);

        $this->assertEventNotDispatched(FooEvent::class);
    }

    public function testAssertEventsDispatched(): void
    {
        $eventDispatcher = new TestEventDispatcher();
        $eventDispatcher->dispatch(new FooEvent());
        $eventDispatcher->dispatch(new BarEvent());

        $this->setEventDispatcher($eventDispatcher);

        $this->assertEventsDispatched([BarEvent::class,FooEvent::class]);
    }

    public function testAssertEventsNotDispatched(): void
    {
        $eventDispatcher = new TestEventDispatcher();
        $this->setEventDispatcher($eventDispatcher);

        $this->assertEventsNotDispatched([BarEvent::class,FooEvent::class]);
    }

    public function testAssertEventsDispatchedEquals(): void
    {
        $eventDispatcher = new TestEventDispatcher();
        $eventDispatcher->dispatch(new FooEvent());
        $eventDispatcher->dispatch(new BarEvent());

        $this->setEventDispatcher($eventDispatcher);

        $this->assertEventsDispatchedEquals([FooEvent::class,BarEvent::class]);
    }

    public function testAssertEventsDispatchedNotEquals(): void
    {
        $eventDispatcher = new TestEventDispatcher();
        $eventDispatcher->dispatch(new FooEvent());
        $eventDispatcher->dispatch(new BarEvent());

        $this->setEventDispatcher($eventDispatcher);

        $this->assertEventsDispatchedNotEquals([BarEvent::class,FooEvent::class]);
    }
}
