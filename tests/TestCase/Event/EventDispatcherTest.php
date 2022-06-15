<?php declare(strict_types=1);

namespace Lightning\Test\Event;

use PHPUnit\Framework\TestCase;
use Lightning\Event\EventDispatcher;
use Lightning\Event\EventWithNameInterface;
use Lightning\Event\EventSubscriberInterface;
use Psr\EventDispatcher\StoppableEventInterface;

// class TestEvent
// {
// }

// class MultiEvent
// {
// }

class TestStoppableEvent implements StoppableEventInterface
{
    public bool $isStopped = false;

    public function stop(): void
    {
        $this->isStopped = true;
    }
    public function isPropagationStopped(): bool
    {
        return $this->isStopped;
    }
}

class Controller implements EventSubscriberInterface
{
    public function subscribedEvents(): array
    {
        return [
            SomethingHappened::class => 'foo',
            SomethingElseHappened::class => ['bar',5]
        ];
    }

    public function foo(object $event): void
    {
        $event->data .= 'foo';
    }

    public function bar(object $event): void
    {
        $event->data .= 'bar';
    }
}

abstract class AbstractEvent
{
    public string $data = '';
}

class SomethingHappened extends AbstractEvent
{
}

class SomethingElseHappened extends AbstractEvent
{
}

class AnotherThingHappened extends AbstractEvent
{
}

class BeforeTransaction implements EventWithNameInterface
{
    public function eventName(): string
    {
        return 'database.beforeTransaction';
    }
}

final class EventDispatcherTest extends TestCase
{
    public function testAddListener(): void
    {
        $eventDispatcher = new EventDispatcher();

        $eventDispatcher->addListener(SomethingHappened::class, function (object $event) {
            $this->assertInstanceOf(SomethingHappened::class, $event);
        })->dispatch(new SomethingHappened());
    }

    public function testGetListeners(): void
    {
        $eventDispatcher = new EventDispatcher();

        $handler = function (object $event) {
            $this->assertInstanceOf(SomethingHappened::class, $event);
        };

        $eventDispatcher->addListener(SomethingHappened::class, $handler)->dispatch(new SomethingHappened());
        $this->assertEquals([$handler], $eventDispatcher->getListenersForEvent(new SomethingHappened()));
    }

    public function testGetListenersNone(): void
    {
        $eventDispatcher = new EventDispatcher();;
        $this->assertEmpty($eventDispatcher->getListenersForEvent(new SomethingElseHappened()));
    }

    public function testGetListenersByName(): void
    {
        $eventDispatcher = new EventDispatcher();

        $handler = function (object $event) {
        };

        $eventDispatcher->addListener('database.beforeTransaction', $handler)->dispatch(new BeforeTransaction());
        $this->assertEquals([$handler], $eventDispatcher->getListenersForEvent(new BeforeTransaction()));
    }

    public function testRemoveListeners(): void
    {
        $eventDispatcher = new EventDispatcher();

        $handler = function (object $event) {
            $this->assertInstanceOf(SomethingHappened::class, $event);
        };

        $eventDispatcher->addListener(SomethingHappened::class, $handler);
        $this->assertNotEmpty($eventDispatcher->getListenersForEvent(new SomethingHappened()));

        $eventDispatcher->removeListener(SomethingHappened::class, $handler);
        $this->assertEmpty($eventDispatcher->getListenersForEvent(new SomethingHappened()));
    }

    public function testAddSubscriber(): void
    {
        $eventDispatcher = new EventDispatcher();

        $controller = new Controller();

        $eventDispatcher->addSubscriber($controller);
        $this->assertEquals([[$controller,'foo']], $eventDispatcher->getListenersForEvent(new SomethingHappened()));
    }

    public function testRemoveSubscriber(): void
    {
        $eventDispatcher = new EventDispatcher();

        $controller = new Controller();
        $eventDispatcher->addSubscriber($controller);
        $this->assertEquals([[$controller,'foo']], $eventDispatcher->getListenersForEvent(new SomethingHappened()));

        $this->assertEmpty($eventDispatcher->removeSubscriber($controller)->getListenersForEvent(new SomethingHappened()));
    }

    public function testDispatch(): void
    {
        $eventDispatcher = new EventDispatcher();

        $event = new SomethingHappened();

        $this->assertEquals($event, $eventDispatcher->dispatch($event));

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(SomethingHappened::class, function (object $event) {
            $this->assertInstanceOf(SomethingHappened::class, $event);
        });
        $this->assertEquals($event, $eventDispatcher->dispatch($event));
    }

    public function testDispatchPriority(): void
    {
        $eventDispatcher = new EventDispatcher();

        $event = new SomethingHappened();
        $handler1 = function (object $event) {
            $event->data .= 'a';
        };
        $handler2 = function (object $event) {
            $event->data .= 'b';
        };
        $handler3 = function (object $event) {
            $event->data .= 'c';
        };

        $eventDispatcher->addListener(SomethingHappened::class, $handler1, 100)
            ->addListener(SomethingHappened::class, $handler2, 25)
            ->addListener(SomethingHappened::class, $handler3, 30);

        $event = $eventDispatcher->dispatch(new SomethingHappened());

        $this->assertEquals('bca', $event->data);
    }
}
